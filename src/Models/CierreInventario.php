<?php
namespace SIG\Models;

use SIG\Core\Database;

/**
 * Cierre de inventario por periodos.
 *
 * Un cierre guarda la foto del negocio al final de un periodo:
 *   - cuanto capital hay en inventario (a costo y a precio de venta)
 *   - cuanto se vendio, se devolvio, entro y se gasto en ese rango
 *
 * Se puede ejecutar de una vez (CERRADO) o dejar agendado (PROGRAMADO).
 * Nada se borra: un cierre mal hecho se ANULA y queda el registro.
 */
class CierreInventario
{
    private Database $db;

    public const PERIODOS = ['DIARIO', 'SEMANAL', 'QUINCENAL', 'MENSUAL', 'PERSONALIZADO'];

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Calcula todos los totales del periodo SIN guardar nada.
     * Sirve para previsualizar antes de cerrar.
     */
    public function calcular(string $desde, string $hasta): array
    {
        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            throw new \RuntimeException('Las fechas del período no son válidas');
        }
        if ($desde > $hasta) {
            throw new \RuntimeException('La fecha inicial no puede ser mayor que la final');
        }

        $rango = ['desde' => $desde, 'hasta' => $hasta];

        // --- Ventas del periodo (solo facturas activas) ---
        $ventas = $this->db->fetchOne(
            "SELECT COUNT(*) AS facturas,
                    COALESCE(SUM(total), 0) AS total,
                    COALESCE(SUM(ganancia), 0) AS ganancia,
                    COALESCE(SUM(descuento), 0) AS descuento
             FROM vb_facturas
             WHERE fecha BETWEEN :desde AND :hasta AND estado = 'ACTIVA'",
            $rango
        );

        // --- Facturas anuladas ---
        $anuladas = $this->db->fetchOne(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS total
             FROM vb_facturas
             WHERE fecha BETWEEN :desde AND :hasta AND estado = 'ANULADA'",
            $rango
        );

        // --- Devoluciones ---
        $devoluciones = $this->db->fetchOne(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS total
             FROM vb_devoluciones
             WHERE fecha BETWEEN :desde AND :hasta",
            $rango
        );

        // --- Mercancía que entro (ingresos y compras) ---
        $ingresos = $this->db->fetchOne(
            "SELECT COUNT(*) AS cantidad,
                    COALESCE(SUM(cantidad_unidad * COALESCE(valor_compra, 0)), 0) AS total,
                    COALESCE(SUM(cantidad_unidad), 0) AS unidad,
                    COALESCE(SUM(cantidad_fraccion), 0) AS fraccion
             FROM vb_ingresos
             WHERE fecha BETWEEN :desde AND :hasta",
            $rango
        );

        // --- Egresos (gastos) ---
        $egresos = $this->db->fetchOne(
            "SELECT COUNT(*) AS cantidad, COALESCE(SUM(valor), 0) AS total
             FROM vb_egresos
             WHERE fecha BETWEEN :desde AND :hasta",
            $rango
        );

        // --- Foto del inventario en este momento ---
        $inventario = $this->db->fetchOne(
            "SELECT COALESCE(SUM(inv.unidad), 0) AS total_unidad,
                    COALESCE(SUM(inv.fraccion), 0) AS total_fraccion,
                    COALESCE(SUM(p.valor_compra * inv.unidad), 0) AS capital_invertido,
                    COALESCE(SUM(p.valor_venta * inv.unidad), 0) AS capital_total
             FROM vb_inventario inv
             JOIN vb_productos p ON inv.id_producto = p.id_producto
             WHERE p.activo = 1"
        );

        $totalVentas       = (float)($ventas['total'] ?? 0);
        $totalGanancia     = (float)($ventas['ganancia'] ?? 0);
        $totalDevoluciones = (float)($devoluciones['total'] ?? 0);
        $totalEgresos      = (float)($egresos['total'] ?? 0);
        $capitalInvertido  = (float)($inventario['capital_invertido'] ?? 0);
        $capitalTotal      = (float)($inventario['capital_total'] ?? 0);

        return [
            'desde'             => $desde,
            'hasta'             => $hasta,
            'dias'              => (int)((strtotime($hasta) - strtotime($desde)) / 86400) + 1,

            'ventas'            => [
                'facturas'  => (int)($ventas['facturas'] ?? 0),
                'total'     => round($totalVentas, 2),
                'ganancia'  => round($totalGanancia, 2),
                'descuento' => round((float)($ventas['descuento'] ?? 0), 2),
            ],
            'anuladas'          => [
                'cantidad'  => (int)($anuladas['cantidad'] ?? 0),
                'total'     => round((float)($anuladas['total'] ?? 0), 2),
            ],
            'devoluciones'      => [
                'cantidad'  => (int)($devoluciones['cantidad'] ?? 0),
                'total'     => round($totalDevoluciones, 2),
            ],
            'ingresos'          => [
                'cantidad'  => (int)($ingresos['cantidad'] ?? 0),
                'total'     => round((float)($ingresos['total'] ?? 0), 2),
                'unidad'    => (int)($ingresos['unidad'] ?? 0),
                'fraccion'  => (int)($ingresos['fraccion'] ?? 0),
            ],
            'egresos'           => [
                'cantidad'  => (int)($egresos['cantidad'] ?? 0),
                'total'     => round($totalEgresos, 2),
            ],
            'inventario'        => [
                'unidad'            => (int)($inventario['total_unidad'] ?? 0),
                'fraccion'          => (int)($inventario['total_fraccion'] ?? 0),
                'capital_invertido' => round($capitalInvertido, 2),
                'capital_total'     => round($capitalTotal, 2),
                'utilidad_potencial'=> round($capitalTotal - $capitalInvertido, 2),
            ],
            // Lo que realmente gano el negocio en el periodo
            'utilidad_neta'     => round($totalGanancia - $totalEgresos - $totalDevoluciones, 2),
        ];
    }

    /**
     * Comprueba si otro cierre ya cubre ese rango de fechas.
     * Devuelve el cierre que se cruza, o null si el rango esta libre.
     */
    public function buscarSolape(string $desde, string $hasta, int $excluir = 0): ?array
    {
        $sql = "SELECT id_cierre, periodo, fecha_desde, fecha_hasta, estado
                FROM vb_cierres_inventario
                WHERE estado <> 'ANULADO'
                  AND COALESCE(fecha_desde, fecha_cierre) <= :hasta
                  AND COALESCE(fecha_hasta, fecha_cierre) >= :desde";
        $params = ['desde' => $desde, 'hasta' => $hasta];

        if ($excluir > 0) {
            $sql .= " AND id_cierre <> :excluir";
            $params['excluir'] = $excluir;
        }

        $sql .= " ORDER BY fecha_desde ASC LIMIT 1";

        return $this->db->fetchOne($sql, $params) ?: null;
    }

    /**
     * Ejecuta el cierre de un periodo (calcula y guarda).
     */
    public function cerrar(string $desde, string $hasta, string $periodo, ?int $idUsuario = null, string $observacion = '', bool $permitirSolape = false): array
    {
        $periodo = $this->normalizarPeriodo($periodo);
        $calc = $this->calcular($desde, $hasta);

        if (!$permitirSolape) {
            $solape = $this->buscarSolape($desde, $hasta);
            if ($solape) {
                throw new \RuntimeException(
                    'Ya existe un cierre (' . $solape['periodo'] . ') que cubre del '
                    . ($solape['fecha_desde'] ?? $solape['fecha_cierre']) . ' al '
                    . ($solape['fecha_hasta'] ?? $solape['fecha_cierre']) . '. Anúlelo primero si quiere rehacerlo.'
                );
            }
        }

        $id = $this->db->insert(
            "INSERT INTO vb_cierres_inventario
                (periodo, fecha_desde, fecha_hasta, estado,
                 total_invertido, total_capital, total_utilidad,
                 total_ventas, total_devoluciones, total_ingresos, total_egresos,
                 total_ganancia, total_facturas, total_anuladas, utilidad_neta,
                 total_unidad, total_fraccion, fecha_cierre, id_usuario, observacion)
             VALUES
                (:periodo, :desde, :hasta, 'CERRADO',
                 :invertido, :capital, :utilidad,
                 :ventas, :devoluciones, :ingresos, :egresos,
                 :ganancia, :facturas, :anuladas, :neta,
                 :unidad, :fraccion, :fecha_cierre, :uid, :obs)",
            [
                'periodo'      => $periodo,
                'desde'        => $desde,
                'hasta'        => $hasta,
                'invertido'    => $calc['inventario']['capital_invertido'],
                'capital'      => $calc['inventario']['capital_total'],
                'utilidad'     => $calc['inventario']['utilidad_potencial'],
                'ventas'       => $calc['ventas']['total'],
                'devoluciones' => $calc['devoluciones']['total'],
                'ingresos'     => $calc['ingresos']['total'],
                'egresos'      => $calc['egresos']['total'],
                'ganancia'     => $calc['ventas']['ganancia'],
                'facturas'     => $calc['ventas']['facturas'],
                'anuladas'     => $calc['anuladas']['total'],
                'neta'         => $calc['utilidad_neta'],
                'unidad'       => $calc['inventario']['unidad'],
                'fraccion'     => $calc['inventario']['fraccion'],
                'fecha_cierre' => $hasta,
                'uid'          => $idUsuario,
                'obs'          => $observacion !== '' ? $observacion : null,
            ]
        );

        return ['id_cierre' => $id, 'calculo' => $calc];
    }

    /**
     * Deja un cierre agendado para que se ejecute despues.
     */
    public function programar(string $desde, string $hasta, string $periodo, ?int $idUsuario = null, string $observacion = ''): int
    {
        $periodo = $this->normalizarPeriodo($periodo);
        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            throw new \RuntimeException('Las fechas del período no son válidas');
        }
        if ($desde > $hasta) {
            throw new \RuntimeException('La fecha inicial no puede ser mayor que la final');
        }

        $solape = $this->buscarSolape($desde, $hasta);
        if ($solape) {
            throw new \RuntimeException(
                'Ya hay un cierre que cubre del ' . ($solape['fecha_desde'] ?? $solape['fecha_cierre'])
                . ' al ' . ($solape['fecha_hasta'] ?? $solape['fecha_cierre']) . '.'
            );
        }

        return $this->db->insert(
            "INSERT INTO vb_cierres_inventario
                (periodo, fecha_desde, fecha_hasta, estado, fecha_cierre, id_usuario, observacion)
             VALUES (:periodo, :desde, :hasta, 'PROGRAMADO', :cierre, :uid, :obs)",
            [
                'periodo' => $periodo,
                'desde'   => $desde,
                'hasta'   => $hasta,
                'cierre'  => $hasta,
                'uid'     => $idUsuario,
                'obs'     => $observacion !== '' ? $observacion : null,
            ]
        );
    }

    /**
     * Ejecuta un cierre programado que ya llego a su fecha final.
     */
    public function ejecutarProgramado(int $idCierre, ?int $idUsuario = null): array
    {
        $c = $this->obtener($idCierre);
        if (!$c) {
            throw new \RuntimeException('El cierre no existe');
        }
        if ($c['estado'] !== 'PROGRAMADO') {
            throw new \RuntimeException('Este cierre no está programado (estado actual: ' . $c['estado'] . ')');
        }
        if ($c['fecha_hasta'] > date('Y-m-d')) {
            throw new \RuntimeException('Todavía no termina el período (va hasta el ' . $c['fecha_hasta'] . ')');
        }

        $calc = $this->calcular($c['fecha_desde'], $c['fecha_hasta']);

        $this->db->executeAffected(
            "UPDATE vb_cierres_inventario
                SET estado = 'CERRADO',
                    total_invertido = :invertido, total_capital = :capital, total_utilidad = :utilidad,
                    total_ventas = :ventas, total_devoluciones = :devoluciones,
                    total_ingresos = :ingresos, total_egresos = :egresos,
                    total_ganancia = :ganancia, total_facturas = :facturas,
                    total_anuladas = :anuladas, utilidad_neta = :neta,
                    total_unidad = :unidad, total_fraccion = :fraccion,
                    fecha_cierre = :hoy, id_usuario = :uid
              WHERE id_cierre = :id",
            [
                'invertido'    => $calc['inventario']['capital_invertido'],
                'capital'      => $calc['inventario']['capital_total'],
                'utilidad'     => $calc['inventario']['utilidad_potencial'],
                'ventas'       => $calc['ventas']['total'],
                'devoluciones' => $calc['devoluciones']['total'],
                'ingresos'     => $calc['ingresos']['total'],
                'egresos'      => $calc['egresos']['total'],
                'ganancia'     => $calc['ventas']['ganancia'],
                'facturas'     => $calc['ventas']['facturas'],
                'anuladas'     => $calc['anuladas']['total'],
                'neta'         => $calc['utilidad_neta'],
                'unidad'       => $calc['inventario']['unidad'],
                'fraccion'     => $calc['inventario']['fraccion'],
                'hoy'          => date('Y-m-d'),
                'uid'          => $idUsuario,
                'id'           => $idCierre,
            ]
        );

        return ['id_cierre' => $idCierre, 'calculo' => $calc];
    }

    /**
     * Anular un cierre (no se borra: queda con estado ANULADO).
     */
    public function anular(int $idCierre, ?int $idUsuario = null, string $motivo = ''): void
    {
        $c = $this->obtener($idCierre);
        if (!$c) {
            throw new \RuntimeException('El cierre no existe');
        }
        if ($c['estado'] === 'ANULADO') {
            throw new \RuntimeException('Este cierre ya está anulado');
        }

        $obs = trim($c['observacion'] . ' | ANULADO: ' . ($motivo !== '' ? $motivo : 'sin motivo'));

        $this->db->executeAffected(
            "UPDATE vb_cierres_inventario SET estado = 'ANULADO', observacion = :obs WHERE id_cierre = :id",
            ['obs' => $obs, 'id' => $idCierre]
        );
    }

    public function listar(int $page = 1, int $perPage = 25, string $estado = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE 1 = 1';
        $params = [];

        $estado = strtoupper(trim($estado));
        if (in_array($estado, ['PROGRAMADO', 'CERRADO', 'ANULADO'], true)) {
            $where .= " AND c.estado = :estado";
            $params['estado'] = $estado;
        }

        $data = $this->db->select(
            "SELECT c.*, u.nombre_usuario
             FROM vb_cierres_inventario c
             LEFT JOIN vb_usuarios u ON c.id_usuario = u.id_usuario
             {$where}
             ORDER BY c.fecha_desde DESC, c.id_cierre DESC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_cierres_inventario c {$where}", $params
        );

        $pendientes = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_cierres_inventario
              WHERE estado = 'PROGRAMADO' AND fecha_hasta <= CURDATE()"
        );

        return [
            'data'              => $data,
            'total'             => (int)($total['total'] ?? 0),
            'page'              => $page,
            'totalPages'        => max(1, (int)ceil(($total['total'] ?? 0) / $perPage)),
            'programados_listos'=> (int)($pendientes['total'] ?? 0),
        ];
    }

    public function obtener(int $idCierre): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*, u.nombre_usuario
             FROM vb_cierres_inventario c
             LEFT JOIN vb_usuarios u ON c.id_usuario = u.id_usuario
             WHERE c.id_cierre = :id",
            ['id' => $idCierre]
        );
    }

    /**
     * Detalle del período de un cierre: en qué se vendió, devoluciones,
     * egresos por tipo y qué mercancía entró.
     */
    public function detallePeriodo(int $idCierre): array
    {
        $c = $this->obtener($idCierre);
        if (!$c) {
            throw new \RuntimeException('El cierre no existe');
        }

        $desde = $c['fecha_desde'] ?: $c['fecha_cierre'];
        $hasta = $c['fecha_hasta'] ?: $c['fecha_cierre'];
        $rango = ['desde' => $desde, 'hasta' => $hasta];

        $porMetodo = $this->db->select(
            "SELECT tipo_pago, COUNT(*) AS facturas, COALESCE(SUM(total), 0) AS total
             FROM vb_facturas
             WHERE fecha BETWEEN :desde AND :hasta AND estado = 'ACTIVA'
             GROUP BY tipo_pago ORDER BY total DESC",
            $rango
        );

        $devoluciones = $this->db->select(
            "SELECT d.id_devolucion, d.codigo, d.fecha, d.total, d.motivo,
                    f.codigo AS factura_codigo, cl.nombre AS cliente_nombre
             FROM vb_devoluciones d
             LEFT JOIN vb_facturas f ON d.id_factura = f.id_factura
             LEFT JOIN vb_clientes cl ON d.id_cliente = cl.id_cliente
             WHERE d.fecha BETWEEN :desde AND :hasta
             ORDER BY d.fecha ASC",
            $rango
        );

        $egresos = $this->db->select(
            "SELECT te.nombre AS tipo, COUNT(*) AS cantidad, COALESCE(SUM(e.valor), 0) AS total
             FROM vb_egresos e
             LEFT JOIN vb_tipos_egreso te ON e.id_tipo_egreso = te.id_tipo_egreso
             WHERE e.fecha BETWEEN :desde AND :hasta
             GROUP BY te.nombre ORDER BY total DESC",
            $rango
        );

        // Con qué se le devolvió la plata al cliente (importa para el cuadre de caja:
        // solo lo devuelto en EFECTIVO baja el dinero fisico)
        $devPorMetodo = $this->db->select(
            "SELECT pg.metodo, COUNT(DISTINCT pg.id_devolucion) AS cantidad, COALESCE(SUM(pg.monto), 0) AS total
             FROM vb_devoluciones_pagos pg
             JOIN vb_devoluciones d ON d.id_devolucion = pg.id_devolucion
             WHERE d.fecha BETWEEN :desde AND :hasta
             GROUP BY pg.metodo ORDER BY total DESC",
            $rango
        );

        $ingresos = $this->db->select(
            "SELECT p.codigo, p.descripcion, SUM(i.cantidad_unidad) AS unidad,
                    SUM(i.cantidad_fraccion) AS fraccion,
                    COALESCE(SUM(i.cantidad_unidad * COALESCE(i.valor_compra, 0)), 0) AS total
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             WHERE i.fecha BETWEEN :desde AND :hasta
             GROUP BY p.id_producto, p.codigo, p.descripcion
             ORDER BY total DESC",
            $rango
        );

        return [
            'cierre'       => $c,
            'por_metodo'   => $porMetodo,
            'devoluciones' => $devoluciones,
            'dev_por_metodo' => $devPorMetodo,
            'egresos'      => $egresos,
            'ingresos'     => $ingresos,
        ];
    }

    /**
     * Rango sugerido para el próximo cierre, según el período elegido:
     * arranca el día siguiente al último cierre y termina hoy.
     */
    public function rangoSugerido(string $periodo): array
    {
        $periodo = $this->normalizarPeriodo($periodo);

        $ultimo = $this->db->fetchOne(
            "SELECT COALESCE(fecha_hasta, fecha_cierre) AS ultima
             FROM vb_cierres_inventario
             WHERE estado <> 'ANULADO'
             ORDER BY COALESCE(fecha_hasta, fecha_cierre) DESC, id_cierre DESC
             LIMIT 1"
        );

        $desde = $ultimo && !empty($ultimo['ultima'])
            ? date('Y-m-d', strtotime($ultimo['ultima'] . ' +1 day'))
            : date('Y-m-01');

        if ($desde > date('Y-m-d')) {
            $desde = date('Y-m-d');
        }

        $dias = $this->diasDelPeriodo($periodo);
        $hastaSugerido = date('Y-m-d', strtotime($desde . ' +' . ($dias - 1) . ' days'));
        $hasta = ($hastaSugerido > date('Y-m-d')) ? date('Y-m-d') : $hastaSugerido;

        return ['desde' => $desde, 'hasta' => $hasta, 'dias' => $dias];
    }

    private function diasDelPeriodo(string $periodo): int
    {
        switch ($periodo) {
            case 'SEMANAL':    return 7;
            case 'QUINCENAL':  return 15;
            case 'MENSUAL':    return 30;
            default:           return 1;
        }
    }

    private function normalizarPeriodo(string $periodo): string
    {
        $periodo = strtoupper(trim($periodo));
        return in_array($periodo, self::PERIODOS, true) ? $periodo : 'DIARIO';
    }

    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        [$y, $m, $d] = explode('-', $fecha);
        return checkdate((int)$m, (int)$d, (int)$y);
    }
}
