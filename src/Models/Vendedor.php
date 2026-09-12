<?php
namespace SIG\Models;

use SIG\Core\Database;

/**
 * Modelo Vendedor
 *
 * Catálogo de vendedores independiente de los usuarios del sistema.
 * El usuario logueado (cajero) es quien cobra; la venta se puede asignar
 * a un vendedor registrado o al propio usuario.
 */
class Vendedor
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar vendedores con su total vendido (histórico)
     */
    public function listar(bool $soloActivos = false, string $buscar = ''): array
    {
        $where  = [];
        $params = [];

        if ($soloActivos) {
            $where[] = 'v.activo = 1';
        }

        if ($buscar !== '') {
            $where[] = '(v.codigo LIKE :b1 OR v.cedula LIKE :b2 OR v.nombre LIKE :b3)';
            $params['b1'] = "%{$buscar}%";
            $params['b2'] = "%{$buscar}%";
            $params['b3'] = "%{$buscar}%";
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        return $this->db->select(
            "SELECT v.*,
                    (SELECT COUNT(*) FROM vb_facturas f
                      WHERE f.id_vendedor_registrado = v.id_vendedor AND f.estado = 'ACTIVA') AS ventas,
                    (SELECT COALESCE(SUM(f.total), 0) FROM vb_facturas f
                      WHERE f.id_vendedor_registrado = v.id_vendedor AND f.estado = 'ACTIVA') AS total_vendido
             FROM vb_vendedores v
             {$sqlWhere}
             ORDER BY v.activo DESC, v.nombre ASC",
            $params
        );
    }

    /**
     * Buscar vendedores por código, cédula o nombre (para el POS)
     */
    public function buscar(string $q, int $limite = 20): array
    {
        $limite = ($limite > 0 && $limite <= 50) ? $limite : 20;

        return $this->db->select(
            "SELECT id_vendedor, codigo, cedula, nombre, comision
             FROM vb_vendedores
             WHERE activo = 1
               AND (codigo LIKE :q1 OR cedula LIKE :q2 OR nombre LIKE :q3)
             ORDER BY nombre ASC
             LIMIT :lim",
            [
                'q1'  => "%{$q}%",
                'q2'  => "%{$q}%",
                'q3'  => "%{$q}%",
                'lim' => $limite,
            ]
        );
    }

    /**
     * Obtener un vendedor por id
     */
    public function obtener(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM vb_vendedores WHERE id_vendedor = :id",
            ['id' => $id]
        );
    }

    /**
     * Generar un código único con formato V0001, V0002, ...
     */
    public function siguienteCodigo(): string
    {
        $row = $this->db->fetchOne(
            "SELECT MAX(CAST(SUBSTRING(codigo, 2) AS UNSIGNED)) AS max_num
             FROM vb_vendedores
             WHERE codigo REGEXP '^V[0-9]+$'"
        );

        $numero = (int)($row['max_num'] ?? 0) + 1;

        // Garantizar que no se repita (por si hay códigos creados a mano)
        do {
            $codigo = 'V' . str_pad((string)$numero, 4, '0', STR_PAD_LEFT);
            $existe = $this->db->fetchOne(
                "SELECT id_vendedor FROM vb_vendedores WHERE codigo = :c",
                ['c' => $codigo]
            );
            $numero++;
        } while ($existe);

        return $codigo;
    }

    /**
     * Crear o actualizar un vendedor
     */
    public function guardar(array $data): int
    {
        $id     = (int)($data['id_vendedor'] ?? 0);
        $nombre = trim((string)($data['nombre'] ?? ''));
        $codigo = strtoupper(trim((string)($data['codigo'] ?? '')));
        $cedula = trim((string)($data['cedula'] ?? ''));

        if ($nombre === '') {
            throw new \RuntimeException('El nombre del vendedor es obligatorio');
        }
        if ($codigo === '') {
            $codigo = $this->siguienteCodigo();
        }
        if (mb_strlen($codigo) > 20) {
            throw new \RuntimeException('El código no puede superar los 20 caracteres');
        }

        $comision = (float)($data['comision'] ?? 0);
        if ($comision < 0 || $comision > 100) {
            throw new \RuntimeException('La comisión debe estar entre 0 y 100');
        }

        // Código único
        $dupCodigo = $this->db->fetchOne(
            "SELECT id_vendedor FROM vb_vendedores WHERE codigo = :c AND id_vendedor <> :id",
            ['c' => $codigo, 'id' => $id]
        );
        if ($dupCodigo) {
            throw new \RuntimeException("Ya existe un vendedor con el código {$codigo}");
        }

        // Cédula única (solo si se informa)
        if ($cedula !== '') {
            $dupCedula = $this->db->fetchOne(
                "SELECT id_vendedor, nombre FROM vb_vendedores WHERE cedula = :c AND id_vendedor <> :id",
                ['c' => $cedula, 'id' => $id]
            );
            if ($dupCedula) {
                throw new \RuntimeException("La cédula {$cedula} ya está registrada para {$dupCedula['nombre']}");
            }
        }

        $valores = [
            'codigo'    => $codigo,
            'cedula'    => ($cedula === '') ? null : $cedula,
            'nombre'    => $nombre,
            'telefono'  => trim((string)($data['telefono'] ?? '')) ?: null,
            'email'     => trim((string)($data['email'] ?? '')) ?: null,
            'direccion' => trim((string)($data['direccion'] ?? '')) ?: null,
            'comision'  => $comision,
            'activo'    => ((int)($data['activo'] ?? 1) === 0) ? 0 : 1,
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
                "UPDATE vb_vendedores SET " . implode(', ', $sets) . " WHERE id_vendedor = :__id",
                $params
            );
            return $id;
        }

        $cols    = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($valores)));
        $holders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($valores)));

        return $this->db->insert(
            "INSERT INTO vb_vendedores ({$cols}) VALUES ({$holders})",
            $valores
        );
    }

    /**
     * Eliminar un vendedor.
     * Si tiene ventas asignadas, la FK es ON DELETE SET NULL, así que se
     * recomienda desactivarlo en su lugar para no perder el histórico.
     */
    public function eliminar(int $id): array
    {
        $vendedor = $this->obtener($id);
        if (!$vendedor) {
            throw new \RuntimeException('El vendedor no existe');
        }

        $uso = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_facturas WHERE id_vendedor_registrado = :id",
            ['id' => $id]
        );
        $ventas = (int)($uso['total'] ?? 0);

        if ($ventas > 0) {
            throw new \RuntimeException(
                "No se puede eliminar: tiene {$ventas} venta(s) asignada(s). " .
                "Desactívelo para conservar el historial."
            );
        }

        $this->db->executeAffected("DELETE FROM vb_vendedores WHERE id_vendedor = :id", ['id' => $id]);

        return ['nombre' => $vendedor['nombre'], 'codigo' => $vendedor['codigo']];
    }

    /**
     * Reporte de ventas por vendedor en un rango de fechas.
     *
     * Devuelve dos tipos de vendedor en una sola lista:
     *   REGISTRADO -> vb_vendedores (ventas con id_vendedor_registrado)
     *   USUARIO    -> usuarios del sistema que vendieron sin vendedor asignado
     *
     * @param bool $soloConVentas Omitir los que no vendieron nada en el rango
     */
    public function ventasPorVendedor(string $desde, string $hasta, bool $soloConVentas = true): array
    {
        $filas = $this->db->select(
            "SELECT 'REGISTRADO' AS tipo, v.id_vendedor AS id, v.codigo, v.cedula, v.nombre,
                    v.comision,
                    COUNT(f.id_factura) AS ventas,
                    COALESCE(SUM(f.total), 0) AS total,
                    COALESCE(SUM(f.ganancia), 0) AS ganancia
             FROM vb_vendedores v
             LEFT JOIN vb_facturas f
                    ON f.id_vendedor_registrado = v.id_vendedor
                   AND f.estado = 'ACTIVA'
                   AND f.fecha BETWEEN :desde1 AND :hasta1
             GROUP BY v.id_vendedor, v.codigo, v.cedula, v.nombre, v.comision

             UNION ALL

             SELECT 'USUARIO' AS tipo, u.id_usuario AS id, u.nombre_usuario AS codigo, NULL AS cedula,
                    COALESCE(NULLIF(u.nombre_completo, ''), u.nombre_usuario) AS nombre,
                    0 AS comision,
                    COUNT(f.id_factura) AS ventas,
                    COALESCE(SUM(f.total), 0) AS total,
                    COALESCE(SUM(f.ganancia), 0) AS ganancia
             FROM vb_usuarios u
             LEFT JOIN vb_facturas f
                    ON f.id_vendedor = u.id_usuario
                   AND f.id_vendedor_registrado IS NULL
                   AND f.estado = 'ACTIVA'
                   AND f.fecha BETWEEN :desde2 AND :hasta2
             GROUP BY u.id_usuario, u.nombre_usuario, u.nombre_completo

             ORDER BY total DESC, nombre ASC",
            [
                'desde1' => $desde,
                'hasta1' => $hasta,
                'desde2' => $desde,
                'hasta2' => $hasta,
            ]
        );

        $resultado = [];
        foreach ($filas as $f) {
            $f['ventas'] = (int)$f['ventas'];
            $f['total'] = (float)$f['total'];
            $f['ganancia'] = (float)$f['ganancia'];
            $f['comision'] = (float)$f['comision'];
            $f['promedio'] = $f['ventas'] > 0 ? round($f['total'] / $f['ventas'], 2) : 0.0;

            if ($soloConVentas && $f['ventas'] === 0) {
                continue;
            }
            $resultado[] = $f;
        }

        return $resultado;
    }

    /**
     * Detalle de las facturas de un vendedor en un rango de fechas
     *
     * @param string $tipo REGISTRADO | USUARIO
     */
    public function detalleVentas(string $desde, string $hasta, string $tipo, int $id): array
    {
        $condicion = ($tipo === 'USUARIO')
            ? 'f.id_vendedor = :id AND f.id_vendedor_registrado IS NULL'
            : 'f.id_vendedor_registrado = :id';

        return $this->db->select(
            "SELECT f.id_factura, f.codigo, f.fecha, f.hora, f.tipo, f.tipo_pago,
                    f.subtotal, f.total_iva, f.total, f.ganancia, f.estado,
                    c.nombre AS cliente_nombre, c.documento AS cliente_documento
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             WHERE {$condicion}
               AND f.fecha BETWEEN :desde AND :hasta
             ORDER BY f.fecha DESC, f.id_factura DESC
             LIMIT 500",
            ['id' => $id, 'desde' => $desde, 'hasta' => $hasta]
        );
    }
}
