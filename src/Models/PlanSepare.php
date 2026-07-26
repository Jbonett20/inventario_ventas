<?php
namespace SIG\Models;

use SIG\Core\Database;

class PlanSepare
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar planes separe paginados
     */
    public function listar(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->db->select(
            "SELECT ps.*, cl.nombre AS cliente_nombre, cl.documento AS cliente_documento
             FROM vb_plan_separe ps
             JOIN vb_clientes cl ON ps.id_cliente = cl.id_cliente
             ORDER BY ps.created_at DESC LIMIT :lim OFFSET :off",
            ['lim' => $perPage, 'off' => $offset]
        );
        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_plan_separe");
        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Crear un nuevo plan separe
     *
     * @param array $d Datos del plan (id_cliente, valor_total, valor_inicial, fecha_inicio, fecha_fin, detalles[])
     *        $d['detalles'] = [
     *            ['id_producto' => 1, 'cantidad' => 2, 'valor_unitario' => 5000, 'descripcion' => ''],
     *            ...
     *        ]
     */
    public function crear(array $d, int $idUsuario): int
    {
        $codigo = 'SEP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $valorTotal = (float)($d['valor_total'] ?? 0);
        $valorAbonado = (float)($d['valor_inicial'] ?? 0);

        if ($valorAbonado > $valorTotal) {
            throw new \InvalidArgumentException('El valor inicial no puede superar el valor total');
        }

        $saldoPendiente = $valorTotal - $valorAbonado;

        $this->db->beginTransaction();
        try {
            $id = $this->db->insert(
                "INSERT INTO vb_plan_separe (codigo, id_cliente, valor_total, valor_abonado, saldo_pendiente, fecha_inicio, fecha_fin, estado)
                 VALUES (:cod, :cli, :vt, :va, :sp, :fi, :ff, 'ACTIVO')",
                [
                    'cod' => $codigo,
                    'cli' => $d['id_cliente'],
                    'vt'  => $valorTotal,
                    'va'  => $valorAbonado,
                    'sp'  => $saldoPendiente,
                    'fi'  => $d['fecha_inicio'] ?? date('Y-m-d'),
                    'ff'  => $d['fecha_fin'] ?? null,
                ]
            );

            // Guardar detalle de productos
            if (!empty($d['detalles']) && is_array($d['detalles'])) {
                $sumaDetalle = 0;
                foreach ($d['detalles'] as $det) {
                    $cantidad = (int)($det['cantidad'] ?? 1);
                    $valorUnitario = (float)($det['valor_unitario'] ?? 0);
                    $subtotal = $cantidad * $valorUnitario;
                    $sumaDetalle += $subtotal;

                    $this->db->insert(
                        "INSERT INTO vb_plan_separe_detalle (id_plan_separe, id_producto, cantidad, valor_unitario, subtotal)
                         VALUES (:idps, :idp, :cant, :vu, :sub)",
                        [
                            'idps' => $id,
                            'idp'  => !empty($det['id_producto']) ? (int)$det['id_producto'] : null,
                            'cant' => $cantidad,
                            'vu'   => $valorUnitario,
                            'sub'  => $subtotal,
                        ]
                    );
                }
            }

            // Si hay abono inicial, registrarlo
            if ($valorAbonado > 0) {
                $this->db->insert(
                    "INSERT INTO vb_plan_separe_abonos (id_plan_separe, valor, fecha, id_usuario)
                     VALUES (:id, :val, :fecha, :uid)",
                    ['id' => $id, 'val' => $valorAbonado, 'fecha' => $d['fecha_inicio'] ?? date('Y-m-d'), 'uid' => $idUsuario]
                );
            }

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener un plan separe por ID
     */
    public function obtenerPorId(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT ps.*, cl.nombre AS cliente_nombre, cl.documento AS cliente_documento,
                    cl.telefono AS cliente_telefono
             FROM vb_plan_separe ps
             JOIN vb_clientes cl ON ps.id_cliente = cl.id_cliente
             WHERE ps.id_plan_separe = :id",
            ['id' => $id]
        );
    }

    /**
     * Registrar un abono al plan separe
     */
    public function abonar(int $idPlanSepare, float $valor, int $idUsuario): void
    {
        $plan = $this->db->fetchOne(
            "SELECT * FROM vb_plan_separe WHERE id_plan_separe = :id",
            ['id' => $idPlanSepare]
        );

        if (!$plan) {
            throw new \RuntimeException('Plan Separe no encontrado');
        }
        if ($plan['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('El Plan Separe no está activo');
        }
        if ($valor <= 0) {
            throw new \InvalidArgumentException('El valor del abono debe ser mayor a cero');
        }
        if ($valor > $plan['saldo_pendiente']) {
            throw new \InvalidArgumentException('El abono no puede superar el saldo pendiente ($' . number_format($plan['saldo_pendiente'], 0, ',', '.') . ')');
        }

        $this->db->beginTransaction();
        try {
            $this->db->insert(
                "INSERT INTO vb_plan_separe_abonos (id_plan_separe, valor, fecha, id_usuario)
                 VALUES (:id, :val, CURDATE(), :uid)",
                ['id' => $idPlanSepare, 'val' => $valor, 'uid' => $idUsuario]
            );

            $nuevoAbonado = $plan['valor_abonado'] + $valor;
            $nuevoSaldo = $plan['saldo_pendiente'] - $valor;

            $this->db->executeAffected(
                "UPDATE vb_plan_separe SET valor_abonado = :ab, saldo_pendiente = :sp WHERE id_plan_separe = :id",
                ['ab' => $nuevoAbonado, 'sp' => $nuevoSaldo, 'id' => $idPlanSepare]
            );

            // Si saldo llega a 0, marcar como COMPLETADO
            if ($nuevoSaldo <= 0) {
                $this->db->executeAffected(
                    "UPDATE vb_plan_separe SET estado = 'COMPLETADO', fecha_fin = CURDATE() WHERE id_plan_separe = :id",
                    ['id' => $idPlanSepare]
                );
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Cancelar un plan separe
     */
    public function cancelar(int $idPlanSepare): void
    {
        $plan = $this->db->fetchOne(
            "SELECT * FROM vb_plan_separe WHERE id_plan_separe = :id",
            ['id' => $idPlanSepare]
        );

        if (!$plan) {
            throw new \RuntimeException('Plan Separe no encontrado');
        }
        if ($plan['estado'] !== 'ACTIVO') {
            throw new \RuntimeException('El Plan Separe no está activo');
        }

        $this->db->executeAffected(
            "UPDATE vb_plan_separe SET estado = 'CANCELADO' WHERE id_plan_separe = :id",
            ['id' => $idPlanSepare]
        );
    }

    /**
     * Obtener un plan separe con su detalle de productos
     */
    public function obtenerConDetalle(int $id): ?array
    {
        $plan = $this->obtenerPorId($id);
        if (!$plan) return null;

        $plan['detalles'] = $this->db->select(
            "SELECT d.*, p.descripcion, p.codigo, p.codigo_barras_1
             FROM vb_plan_separe_detalle d
             LEFT JOIN vb_productos p ON d.id_producto = p.id_producto
             WHERE d.id_plan_separe = :id
             ORDER BY d.id_detalle ASC",
            ['id' => $id]
        );

        $plan['abonos'] = $this->abonos($id);

        return $plan;
    }

    /**
     * Obtener abonos de un plan separe
     */
    public function abonos(int $idPlanSepare): array
    {
        return $this->db->select(
            "SELECT a.*, u.nombre_usuario
             FROM vb_plan_separe_abonos a
             LEFT JOIN vb_usuarios u ON a.id_usuario = u.id_usuario
             WHERE a.id_plan_separe = :id
             ORDER BY a.fecha DESC, a.created_at DESC",
            ['id' => $idPlanSepare]
        );
    }
}
