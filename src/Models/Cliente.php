<?php
namespace SIG\Models;

use SIG\Core\Database;

class Cliente
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE c.activo = 1';
        $params = [];

        if (!empty($search)) {
            $where .= " AND (c.documento LIKE :s1 OR c.nombre LIKE :s2 OR c.telefono LIKE :s3)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
        }

        $sql = "SELECT c.* FROM vb_clientes c {$where} ORDER BY c.nombre ASC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $data = $this->db->select($sql, $params);

        $totalParams = $params;
        unset($totalParams['limit'], $totalParams['offset']);
        $total = $this->db->fetchOne("SELECT COUNT(*) as total FROM vb_clientes c {$where}", $totalParams);

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    public function buscar(string $query): array
    {
        return $this->db->select(
            "SELECT * FROM vb_clientes 
             WHERE activo = 1 AND (documento LIKE :q1 OR nombre LIKE :q2)
             ORDER BY nombre ASC LIMIT 15",
            ['q1' => "%{$query}%", 'q2' => "%{$query}%"]
        );
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM vb_clientes WHERE id_cliente = :id", ['id' => $id]);
    }

    public function crear(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO vb_clientes (tipo_documento, documento, nombre, direccion, telefono, email, regimen, observaciones)
             VALUES (:td, :doc, :nombre, :dir, :tel, :email, :reg, :obs)",
            [
                'td'     => $data['tipo_documento'] ?? 'CC',
                'doc'    => $data['documento'],
                'nombre' => $data['nombre'],
                'dir'    => $data['direccion'] ?? '',
                'tel'    => $data['telefono'] ?? '',
                'email'  => $data['email'] ?? '',
                'reg'    => $data['regimen'] ?? '',
                'obs'    => $data['observaciones'] ?? '',
            ]
        );
    }

    public function actualizar(int $id, array $data): int
    {
        return $this->db->executeAffected(
            "UPDATE vb_clientes SET 
                tipo_documento = :td, documento = :doc, nombre = :nombre,
                direccion = :dir, telefono = :tel, email = :email,
                regimen = :reg, observaciones = :obs
             WHERE id_cliente = :id",
            [
                'td'     => $data['tipo_documento'] ?? 'CC',
                'doc'    => $data['documento'],
                'nombre' => $data['nombre'],
                'dir'    => $data['direccion'] ?? '',
                'tel'    => $data['telefono'] ?? '',
                'email'  => $data['email'] ?? '',
                'reg'    => $data['regimen'] ?? '',
                'obs'    => $data['observaciones'] ?? '',
                'id'     => $id,
            ]
        );
    }

    public function eliminar(int $id): int
    {
        return $this->db->executeAffected("UPDATE vb_clientes SET activo = 0 WHERE id_cliente = :id", ['id' => $id]);
    }
}
