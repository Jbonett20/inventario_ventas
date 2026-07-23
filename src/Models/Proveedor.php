<?php
namespace SIG\Models;

use SIG\Core\Database;

class Proveedor
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function listar(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE p.estado = 1';
        $params = [];
        if (!empty($search)) {
            $where .= " AND (p.codigo LIKE :s1 OR p.nombre LIKE :s2 OR p.telefono LIKE :s3)";
            $params['s1'] = "%{$search}%"; $params['s2'] = "%{$search}%"; $params['s3'] = "%{$search}%";
        }
        $data = $this->db->select("SELECT p.* FROM vb_proveedores p {$where} ORDER BY p.nombre ASC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset]));
        $totalParams = $params;
        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_proveedores p {$where}", $totalParams);
        return ['data' => $data, 'total' => (int)($total['total'] ?? 0), 'page' => $page, 'totalPages' => ceil(($total['total'] ?? 0) / $perPage)];
    }

    public function crear(array $d): int
    {
        return $this->db->insert(
            "INSERT INTO vb_proveedores (codigo, nombre, responsable, direccion, telefono, email, departamento, ciudad, dias_pago, rentabilidad)
             VALUES (:c, :n, :r, :d, :t, :e, :dep, :ciu, :dp, :rent)",
            ['c' => $d['codigo'], 'n' => $d['nombre'], 'r' => $d['responsable'] ?? '', 'd' => $d['direccion'] ?? '',
             't' => $d['telefono'] ?? '', 'e' => $d['email'] ?? '', 'dep' => $d['departamento'] ?? '',
             'ciu' => $d['ciudad'] ?? '', 'dp' => $d['dias_pago'] ?? null, 'rent' => $d['rentabilidad'] ?? null]
        );
    }

    public function eliminar(int $id): int
    {
        return $this->db->executeAffected("UPDATE vb_proveedores SET estado = 0 WHERE id_proveedor = :id", ['id' => $id]);
    }
}
