<?php
namespace SIG\Models;

use SIG\Core\Database;

class Credito
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Listar créditos.
     *
     * @param string $estado Filtro: '' o 'TODOS' = todos, 'INHABILITADOS' = solo los
     *                       inhabilitados, o el nombre exacto del estado.
     */
    public function listar(int $page = 1, int $perPage = 25, string $estado = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $params = [];

        $estado = strtoupper(trim($estado));
        if ($estado === 'INHABILITADOS') {
            $where .= " AND c.estado = 'INHABILITADO'";
        } elseif ($estado !== '' && $estado !== 'TODOS') {
            $where .= " AND c.estado = :estado";
            $params['estado'] = $estado;
        }

        $data = $this->db->select(
            "SELECT c.*, cl.nombre AS cliente_nombre, cl.documento AS cliente_documento,
                    u.nombre_usuario AS usuario_inhabilito
             FROM vb_creditos c
             JOIN vb_clientes cl ON c.id_cliente = cl.id_cliente
             LEFT JOIN vb_usuarios u ON c.id_usuario_inhabilitado = u.id_usuario
             {$where}
             ORDER BY c.created_at DESC LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_creditos c {$where}", $params
        );

        // Cartera: los créditos inhabilitados NO cuentan como dinero por cobrar
        $resumen = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN estado IN ('ACTIVO','VENCIDO') THEN saldo_pendiente ELSE 0 END), 0) AS cartera,
                COALESCE(SUM(CASE WHEN estado = 'ACTIVO' THEN 1 ELSE 0 END), 0) AS activos,
                COALESCE(SUM(CASE WHEN estado = 'VENCIDO' THEN 1 ELSE 0 END), 0) AS vencidos,
                COALESCE(SUM(CASE WHEN estado = 'PAGADO' THEN 1 ELSE 0 END), 0) AS pagados,
                COALESCE(SUM(CASE WHEN estado = 'INHABILITADO' THEN 1 ELSE 0 END), 0) AS inhabilitados
             FROM vb_creditos"
        );

        return [
            'data'        => $data,
            'total'       => (int)($total['total'] ?? 0),
            'page'        => $page,
            'totalPages'  => ceil(($total['total'] ?? 0) / $perPage),
            'resumen'     => [
                'cartera'       => (float)($resumen['cartera'] ?? 0),
                'activos'       => (int)($resumen['activos'] ?? 0),
                'vencidos'      => (int)($resumen['vencidos'] ?? 0),
                'pagados'       => (int)($resumen['pagados'] ?? 0),
                'inhabilitados' => (int)($resumen['inhabilitados'] ?? 0),
            ],
        ];
    }

    /**
     * Obtener un crédito con los datos del cliente
     */
    public function obtener(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*, cl.nombre AS cliente_nombre, cl.documento AS cliente_documento
             FROM vb_creditos c
             JOIN vb_clientes cl ON c.id_cliente = cl.id_cliente
             WHERE c.id_credito = :id",
            ['id' => $id]
        );
    }

    public function crear(array $d, int $idUsuario): int
    {
        $codigo = 'CRE-' . date('Ymd') . '-' . rand(100, 999);
        return $this->db->insert(
            "INSERT INTO vb_creditos (codigo, id_cliente, valor_total, valor_aumento, saldo_pendiente, fecha_inicio, fecha_fin, estado)
             VALUES (:cod, :cli, :vt, :va, :sp, :fi, :ff, 'ACTIVO')",
            [
                'cod' => $codigo, 'cli' => $d['id_cliente'], 'vt' => $d['valor_total'],
                'va' => $d['valor_aumento'] ?? 0, 'sp' => $d['valor_total'],
                'fi' => $d['fecha_inicio'] ?? date('Y-m-d'), 'ff' => $d['fecha_fin'] ?? null,
            ]
        );
    }

    public function abonar(int $idCredito, float $valor, int $idUsuario): void
    {
        $credito = $this->obtener($idCredito);
        if (!$credito) {
            throw new \RuntimeException('El crédito no existe');
        }
        if ($credito['estado'] === 'INHABILITADO') {
            throw new \RuntimeException('Este crédito está inhabilitado: reactívelo antes de registrar abonos.');
        }
        if ($valor <= 0) {
            throw new \RuntimeException('El valor del abono debe ser mayor a cero');
        }

        $this->db->beginTransaction();
        try {
            $this->db->insert(
                "INSERT INTO vb_creditos_abonos (id_credito, valor, fecha, id_usuario) VALUES (:id, :val, CURDATE(), :uid)",
                ['id' => $idCredito, 'val' => $valor, 'uid' => $idUsuario]
            );
            $this->db->executeAffected(
                "UPDATE vb_creditos SET saldo_pendiente = saldo_pendiente - :val WHERE id_credito = :id",
                ['val' => $valor, 'id' => $idCredito]
            );
            // Si saldo llega a 0, marcar como pagado
            $cred = $this->db->fetchOne("SELECT saldo_pendiente FROM vb_creditos WHERE id_credito = :id", ['id' => $idCredito]);
            if ($cred && $cred['saldo_pendiente'] <= 0) {
                $this->db->executeAffected("UPDATE vb_creditos SET estado = 'PAGADO' WHERE id_credito = :id", ['id' => $idCredito]);
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function abonos(int $idCredito): array
    {
        return $this->db->select(
            "SELECT a.*, u.nombre_usuario FROM vb_creditos_abonos a
             LEFT JOIN vb_usuarios u ON a.id_usuario = u.id_usuario
             WHERE a.id_credito = :id ORDER BY a.fecha DESC",
            ['id' => $idCredito]
        );
    }

    /**
     * Inhabilitar un crédito: deja de contar en cartera y no permite abonos.
     * No se borra nada: el motivo, la fecha y el usuario quedan guardados.
     */
    public function inhabilitar(int $idCredito, string $motivo, ?int $idUsuario = null): void
    {
        $credito = $this->obtener($idCredito);
        if (!$credito) {
            throw new \RuntimeException('El crédito no existe');
        }
        if ($credito['estado'] === 'INHABILITADO') {
            throw new \RuntimeException('Este crédito ya está inhabilitado');
        }
        if ((float)$credito['saldo_pendiente'] <= 0 || $credito['estado'] === 'PAGADO') {
            throw new \RuntimeException('No se puede inhabilitar un crédito que ya está pagado');
        }

        $motivo = trim($motivo);
        if ($motivo === '') {
            throw new \RuntimeException('Debe indicar el motivo por el que se inhabilita el crédito');
        }

        $this->db->executeAffected(
            "UPDATE vb_creditos
                SET estado = 'INHABILITADO', motivo_inhabilitado = :motivo,
                    fecha_inhabilitado = NOW(), id_usuario_inhabilitado = :uid
              WHERE id_credito = :id",
            ['motivo' => $motivo, 'uid' => $idUsuario, 'id' => $idCredito]
        );
    }

    /**
     * Reactivar un crédito inhabilitado. Vuelve a ACTIVO, o a PAGADO si ya no
     * tiene saldo.
     */
    public function reactivar(int $idCredito, ?int $idUsuario = null): void
    {
        $credito = $this->obtener($idCredito);
        if (!$credito) {
            throw new \RuntimeException('El crédito no existe');
        }
        if ($credito['estado'] !== 'INHABILITADO') {
            throw new \RuntimeException('Este crédito no está inhabilitado');
        }

        $nuevoEstado = ((float)$credito['saldo_pendiente'] > 0) ? 'ACTIVO' : 'PAGADO';

        $this->db->executeAffected(
            "UPDATE vb_creditos
                SET estado = :estado, motivo_inhabilitado = NULL,
                    fecha_inhabilitado = NULL, id_usuario_inhabilitado = NULL
              WHERE id_credito = :id",
            ['estado' => $nuevoEstado, 'id' => $idCredito]
        );
    }
}
