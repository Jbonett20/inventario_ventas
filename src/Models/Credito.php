<?php
namespace SIG\Models;

use SIG\Core\Database;

class Credito
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function listar(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->db->select(
            "SELECT c.*, cl.nombre AS cliente_nombre, cl.documento AS cliente_documento
             FROM vb_creditos c
             JOIN vb_clientes cl ON c.id_cliente = cl.id_cliente
             ORDER BY c.created_at DESC LIMIT :lim OFFSET :off",
            ['lim' => $perPage, 'off' => $offset]
        );
        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_creditos");
        return ['data' => $data, 'total' => (int)($total['total'] ?? 0), 'page' => $page,
                'totalPages' => ceil(($total['total'] ?? 0) / $perPage)];
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
}
