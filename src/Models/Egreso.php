<?php
namespace SIG\Models;

use SIG\Core\Database;

class Egreso
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Listar egresos con filtro por mes o por rango de fechas
     */
    public function listar(int $page = 1, int $perPage = 25, string $mes = '', string $desde = '', string $hasta = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1=1'; $params = [];

        if ($mes !== '') {
            $where .= " AND e.mes = :mes";
            $params['mes'] = $mes;
        }
        if ($desde !== '') {
            $where .= " AND e.fecha >= :desde";
            $params['desde'] = $desde;
        }
        if ($hasta !== '') {
            $where .= " AND e.fecha <= :hasta";
            $params['hasta'] = $hasta;
        }

        $data = $this->db->select(
            "SELECT e.*, t.nombre AS tipo_nombre, t.codigo AS tipo_codigo, u.nombre_usuario
             FROM vb_egresos e
             JOIN vb_tipos_egreso t ON e.id_tipo_egreso = t.id_tipo_egreso
             LEFT JOIN vb_usuarios u ON e.id_usuario = u.id_usuario
             {$where} ORDER BY e.fecha DESC, e.id_egreso DESC LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_egresos e {$where}", $params);
        $suma = $this->db->fetchOne("SELECT COALESCE(SUM(e.valor),0) AS total FROM vb_egresos e {$where}", $params);

        return ['data' => $data, 'total' => (int)($total['total'] ?? 0), 'page' => $page,
                'totalPages' => ceil(($total['total'] ?? 0) / $perPage), 'suma' => (float)($suma['total'] ?? 0)];
    }

    public function crear(array $d, int $idUsuario): int
    {
        return $this->db->insert(
            "INSERT INTO vb_egresos (id_tipo_egreso, pagado_a, valor, fecha, mes, observacion, id_usuario)
             VALUES (:tipo, :pag, :val, :fec, :mes, :obs, :uid)",
            [
                'tipo' => $d['id_tipo_egreso'], 'pag' => $d['pagado_a'] ?? '',
                'val' => $d['valor'], 'fec' => $d['fecha'] ?? date('Y-m-d'),
                'mes' => date('Y-m', strtotime($d['fecha'] ?? date('Y-m-d'))),
                'obs' => $d['observacion'] ?? '', 'uid' => $idUsuario,
            ]
        );
    }

    /**
     * Eliminar un egreso
     */
    public function eliminar(int $id): int
    {
        $egreso = $this->db->fetchOne(
            "SELECT id_egreso FROM vb_egresos WHERE id_egreso = :id",
            ['id' => $id]
        );
        if (!$egreso) {
            throw new \RuntimeException('El egreso no existe');
        }

        return $this->db->executeAffected("DELETE FROM vb_egresos WHERE id_egreso = :id", ['id' => $id]);
    }

    public function tipos(): array
    {
        return $this->db->select("SELECT * FROM vb_tipos_egreso WHERE activo = 1 ORDER BY nombre");
    }

    /**
     * Todos los tipos de egreso (incluye inactivos) con su uso
     */
    public function tiposTodos(): array
    {
        return $this->db->select(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM vb_egresos e WHERE e.id_tipo_egreso = t.id_tipo_egreso) AS egresos,
                    (SELECT COALESCE(SUM(e.valor), 0) FROM vb_egresos e WHERE e.id_tipo_egreso = t.id_tipo_egreso) AS total
             FROM vb_tipos_egreso t
             ORDER BY t.nombre ASC"
        );
    }

    /**
     * Crear o actualizar un tipo de egreso
     */
    public function guardarTipo(array $d): int
    {
        $id     = (int)($d['id_tipo_egreso'] ?? 0);
        $codigo = trim((string)($d['codigo'] ?? ''));
        $nombre = trim((string)($d['nombre'] ?? ''));

        if ($codigo === '') {
            throw new \RuntimeException('El código es obligatorio');
        }
        if ($nombre === '') {
            throw new \RuntimeException('El nombre es obligatorio');
        }

        $duplicado = $this->db->fetchOne(
            "SELECT id_tipo_egreso FROM vb_tipos_egreso
             WHERE (codigo = :codigo OR nombre = :nombre) AND id_tipo_egreso <> :id",
            ['codigo' => $codigo, 'nombre' => $nombre, 'id' => $id]
        );
        if ($duplicado) {
            throw new \RuntimeException('Ya existe un tipo de egreso con ese código o nombre');
        }

        $valores = [
            'codigo'    => $codigo,
            'nombre'    => $nombre,
            'concepto'  => trim((string)($d['concepto'] ?? '')),
            'activo'    => ((int)($d['activo'] ?? 1) === 0) ? 0 : 1,
        ];

        if ($id > 0) {
            $sets = [];
            $params = [];
            foreach ($valores as $col => $val) {
                $sets[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $params['__id'] = $id;

            $this->db->executeAffected(
                "UPDATE vb_tipos_egreso SET " . implode(', ', $sets) . " WHERE id_tipo_egreso = :__id",
                $params
            );
            return $id;
        }

        return $this->db->insert(
            "INSERT INTO vb_tipos_egreso (codigo, nombre, concepto, activo)
             VALUES (:codigo, :nombre, :concepto, :activo)",
            $valores
        );
    }

    /**
     * Eliminar un tipo de egreso.
     * La FK vb_egresos.id_tipo_egreso es ON DELETE CASCADE, por lo que se
     * bloquea la eliminación si el tipo tiene egresos registrados.
     */
    public function eliminarTipo(int $id): array
    {
        $tipo = $this->db->fetchOne(
            "SELECT id_tipo_egreso, nombre FROM vb_tipos_egreso WHERE id_tipo_egreso = :id",
            ['id' => $id]
        );
        if (!$tipo) {
            throw new \RuntimeException('El tipo de egreso no existe');
        }

        $uso = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_egresos WHERE id_tipo_egreso = :id",
            ['id' => $id]
        );
        $egresos = (int)($uso['total'] ?? 0);

        if ($egresos > 0) {
            throw new \RuntimeException(
                "No se puede eliminar: hay {$egresos} egreso(s) con este tipo. " .
                "Desactívelo en su lugar para conservar el historial."
            );
        }

        $this->db->executeAffected("DELETE FROM vb_tipos_egreso WHERE id_tipo_egreso = :id", ['id' => $id]);

        return ['nombre' => $tipo['nombre']];
    }

    public function meses(): array
    {
        return $this->db->select(
            "SELECT DISTINCT mes FROM vb_egresos ORDER BY mes DESC LIMIT 12"
        );
    }
}
