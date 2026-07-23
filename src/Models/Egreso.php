<?php
namespace SIG\Models;

use SIG\Core\Database;

class Egreso
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function listar(int $page = 1, int $perPage = 25, string $mes = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1=1'; $params = [];
        if (!empty($mes)) { $where .= " AND e.mes = :mes"; $params['mes'] = $mes; }

        $data = $this->db->select(
            "SELECT e.*, t.nombre AS tipo_nombre, t.codigo AS tipo_codigo, u.nombre_usuario
             FROM vb_egresos e
             JOIN vb_tipos_egreso t ON e.id_tipo_egreso = t.id_tipo_egreso
             LEFT JOIN vb_usuarios u ON e.id_usuario = u.id_usuario
             {$where} ORDER BY e.fecha DESC LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_egresos e {$where}", $params);
        $suma = $this->db->fetchOne("SELECT COALESCE(SUM(valor),0) AS total FROM vb_egresos e {$where}", $params);

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

    public function tipos(): array
    {
        return $this->db->select("SELECT * FROM vb_tipos_egreso WHERE activo = 1 ORDER BY nombre");
    }

    public function meses(): array
    {
        return $this->db->select(
            "SELECT DISTINCT mes FROM vb_egresos ORDER BY mes DESC LIMIT 12"
        );
    }
}
