<?php
namespace SIG\Models;

use SIG\Core\Database;

class Ingreso
{
    private Database $db;

    /** Último cálculo de precios aplicado (para informar al usuario) */
    public ?array $ultimoCalculo = null;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Listar ingresos
     */
    public function listar(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $data = $this->db->select(
            "SELECT i.*, p.codigo, p.descripcion, p.presentacion, u.nombre_usuario
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON i.id_usuario = u.id_usuario
             ORDER BY i.fecha DESC, i.created_at DESC
             LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );

        $total = $this->db->fetchOne("SELECT COUNT(*) AS total FROM vb_ingresos");

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Calcula, SIN escribir nada en la base de datos, cómo quedarían los
     * precios de un producto al registrar un ingreso.
     *
     * Contabilidad / costeo (Colombia - costo promedio ponderado):
     *   costo nuevo = (stock antes × costo viejo) + (cantidad nueva × precio compra)
     *                 ------------------------------------------------------------
     *                              stock antes + cantidad nueva
     *
     * El precio de venta se recalcula con el margen que ya trae el producto:
     *   precio venta = costo / (1 - rentabilidad / 100)      (margen sobre el precio de venta)
     *
     * El precio NUNCA queda en cero: si el producto no tiene un % de
     * rentabilidad válido, se conserva el precio de venta actual.
     *
     * @param int      $idProducto
     * @param float    $precioCompra  Precio pagado en ESTA compra
     * @param int      $cantidadNueva Cantidad que entra (misma unidad del precio)
     * @param int|null $stockAntes    Stock ANTES del ingreso. Si se omite se deduce restando lo que entró.
     *
     * @return array Detalle completo del cálculo (para previsualizar y para el historial)
     */
    public function calcularPreciosIngreso(int $idProducto, float $precioCompra, int $cantidadNueva, ?int $stockAntes = null): array
    {
        $p = $this->db->fetchOne(
            "SELECT p.valor_compra, p.valor_venta, p.valor_unidad, p.rentabilidad,
                    p.precio_maximo_regulado, COALESCE(inv.unidad, 0) AS stock
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.id_producto = :id",
            ['id' => $idProducto]
        );

        if (!$p) {
            throw new \RuntimeException('Producto no encontrado');
        }

        $costoActual  = round((float)$p['valor_compra'], 2);
        $ventaActual  = round((float)$p['valor_venta'], 2);
        $unidadActual = ($p['valor_unidad'] !== null) ? round((float)$p['valor_unidad'], 2) : null;
        $rentabilidad = round((float)$p['rentabilidad'], 2);
        $tope         = ($p['precio_maximo_regulado'] !== null && (float)$p['precio_maximo_regulado'] > 0)
                            ? round((float)$p['precio_maximo_regulado'], 2)
                            : null;

        // Si no nos dan el stock anterior se deduce: el trigger de vb_ingresos
        // ya sumó lo que entró, así que se resta para no contarlo dos veces.
        $stock = ($stockAntes !== null) ? max(0, $stockAntes) : max(0, (int)$p['stock'] - $cantidadNueva);

        $totalStock = $stock + $cantidadNueva;
        $costoNuevo = ($totalStock > 0)
            ? round((($stock * $costoActual) + ($cantidadNueva * $precioCompra)) / $totalStock, 2)
            : round($precioCompra, 2);

        $r = [
            'id_producto'           => $idProducto,
            'costo_actual'          => $costoActual,
            'costo_nuevo'           => $costoNuevo,
            'stock_antes'           => $stock,
            'cantidad'              => $cantidadNueva,
            'precio_compra'         => round($precioCompra, 2),
            'rentabilidad'          => $rentabilidad,
            'rentabilidad_final'    => $rentabilidad,
            'venta_actual'          => $ventaActual,
            'venta_nueva'           => $ventaActual,
            'unidad_actual'         => $unidadActual,
            'unidad_nueva'          => $unidadActual,
            'tope_regulado'         => $tope,
            'cambia_costo'          => abs($costoNuevo - $costoActual) >= 0.01,
            'cambia_venta'          => false,
            'aviso'                 => '',
        ];

        if (!$r['cambia_costo']) {
            $r['aviso'] = 'El costo promedio no cambia: se compró al mismo precio que ya estaba registrado.';
            return $r;
        }

        // Sin un % de rentabilidad válido NO se puede recalcular el precio:
        // se deja el que ya tenía (jamás dejarlo en $0).
        if ($rentabilidad <= 0 || $rentabilidad >= 100) {
            $r['aviso'] = 'El producto no tiene un % de rentabilidad válido (' . $rentabilidad . '%). Se conserva el precio de venta actual de $' . number_format($ventaActual, 0, ',', '.') . '.';
            return $r;
        }

        $ventaNueva = round($costoNuevo / (1 - $rentabilidad / 100), 2);

        if ($ventaNueva <= 0) {
            $r['aviso'] = 'El precio calculado dio $0. Se conserva el precio de venta actual.';
            return $r;
        }

        // Tope legal: medicamentos con precio máximo regulado
        if ($tope !== null && $ventaNueva > $tope) {
            $r['aviso'] = 'El precio calculado ($' . number_format($ventaNueva, 0, ',', '.') . ') supera el precio máximo regulado ($' . number_format($tope, 0, ',', '.') . '). Se aplica el tope legal.';
            $ventaNueva = $tope;
        }

        $r['venta_nueva']  = $ventaNueva;
        $r['cambia_venta'] = abs($ventaNueva - $ventaActual) >= 0.01;

        // El precio de la unidad suelta conserva su proporción
        // (ej: caja $12.000 y la tableta suelta $1.500 => si sube la caja, la suelta sube igual)
        if ($unidadActual !== null && $unidadActual > 0 && $ventaActual > 0) {
            $r['unidad_nueva'] = round($unidadActual * ($ventaNueva / $ventaActual), 2);
        }

        // Rentabilidad real después del cambio (baja si el tope legal actuó)
        if ($ventaNueva > 0 && $costoNuevo > 0) {
            $r['rentabilidad_final'] = round((($ventaNueva - $costoNuevo) / $ventaNueva) * 100, 2);
        }

        return $r;
    }

    /**
     * Aplica el cálculo del ingreso: actualiza costo, precio de venta y precio
     * de la unidad suelta, y deja la trazabilidad en el historial.
     *
     * @return array Resultado del cálculo (mismo formato de calcularPreciosIngreso)
     */
    public function aplicarPreciosIngreso(int $idProducto, float $precioCompra, int $cantidadNueva, int $stockAntes, ?int $idUsuario = null, ?int $idIngreso = null, string $motivo = '', string $origen = 'INGRESO'): array
    {
        $calc = $this->calcularPreciosIngreso($idProducto, $precioCompra, $cantidadNueva, $stockAntes);
        $this->ultimoCalculo = $calc;

        $cambiaUnidad = ($calc['unidad_actual'] !== null && $calc['unidad_nueva'] !== null
            && abs($calc['unidad_nueva'] - $calc['unidad_actual']) >= 0.01);

        if (!$calc['cambia_costo'] && !$calc['cambia_venta'] && !$cambiaUnidad) {
            return $calc; // nada cambió: no se ensucia el historial
        }

        $this->db->executeAffected(
            "UPDATE vb_productos
                SET valor_compra = :compra, valor_venta = :venta,
                    valor_unidad = :unidad, rentabilidad = :rent
              WHERE id_producto = :id",
            [
                'compra' => $calc['costo_nuevo'],
                'venta'  => $calc['venta_nueva'],
                'unidad' => $calc['unidad_nueva'],
                'rent'   => $calc['rentabilidad_final'],
                'id'     => $idProducto,
            ]
        );

        $this->registrarHistorial([
            'id_producto'            => $idProducto,
            'origen'                 => $origen,
            'id_referencia'          => $idIngreso,
            'cantidad'               => $cantidadNueva,
            'precio_compra'          => $precioCompra,
            'costo_anterior'         => $calc['costo_actual'],
            'costo_nuevo'            => $calc['costo_nuevo'],
            'precio_venta_anterior'  => $calc['venta_actual'],
            'precio_venta_nuevo'     => $calc['venta_nueva'],
            'precio_unidad_anterior' => $calc['unidad_actual'],
            'precio_unidad_nuevo'    => $calc['unidad_nueva'],
            'rentabilidad'           => $calc['rentabilidad_final'],
            'motivo'                 => trim($motivo . ($calc['aviso'] !== '' ? ' | ' . $calc['aviso'] : '')),
            'id_usuario'             => $idUsuario,
        ]);

        return $calc;
    }

    /**
     * Guarda una fila en la bitácora de precios. NUNCA se borra ni se edita:
     * es la trazabilidad para contabilidad.
     */
    public function registrarHistorial(array $h): int
    {
        return $this->db->insert(
            "INSERT INTO vb_productos_precios_historial
                (id_producto, origen, id_referencia, cantidad, precio_compra,
                 costo_anterior, costo_nuevo, precio_venta_anterior, precio_venta_nuevo,
                 precio_unidad_anterior, precio_unidad_nuevo, rentabilidad, motivo, id_usuario, created_at)
             VALUES
                (:prod, :origen, :ref, :cant, :pcompra,
                 :canterior, :cnuevo, :pvanterior, :pvnuevo,
                 :puanterior, :punuevo, :rent, :motivo, :uid, NOW())",
            [
                'prod'       => $h['id_producto'],
                'origen'     => $h['origen'] ?? 'INGRESO',
                'ref'        => $h['id_referencia'] ?? null,
                'cant'       => $h['cantidad'] ?? 0,
                'pcompra'    => $h['precio_compra'] ?? null,
                'canterior'  => $h['costo_anterior'] ?? 0,
                'cnuevo'     => $h['costo_nuevo'] ?? 0,
                'pvanterior' => $h['precio_venta_anterior'] ?? 0,
                'pvnuevo'    => $h['precio_venta_nuevo'] ?? 0,
                'puanterior' => $h['precio_unidad_anterior'] ?? null,
                'punuevo'    => $h['precio_unidad_nuevo'] ?? null,
                'rent'       => $h['rentabilidad'] ?? null,
                'motivo'     => $h['motivo'] ?? null,
                'uid'        => $h['id_usuario'] ?? null,
            ]
        );
    }

    /**
     * Costo promedio de TODAS las compras registradas de un producto.
     * Sirve como respaldo cuando no se puede restaurar un valor exacto.
     *
     * @param int      $idProducto
     * @param int|null $excluirIngreso Ingreso que NO debe contarse (el que se está anulando)
     */
    public function costoPromedioDeCompras(int $idProducto, ?int $excluirIngreso = null): ?array
    {
        $sql = "SELECT COALESCE(SUM(cantidad_unidad * valor_compra), 0) AS total,
                       COALESCE(SUM(cantidad_unidad), 0) AS cantidad,
                       COUNT(*) AS compras
                FROM vb_ingresos
                WHERE id_producto = :id AND valor_compra > 0 AND cantidad_unidad > 0";
        $params = ['id' => $idProducto];

        if ($excluirIngreso !== null) {
            $sql .= " AND id_ingreso <> :excluir";
            $params['excluir'] = $excluirIngreso;
        }

        $r = $this->db->fetchOne($sql, $params);
        $cantidad = (int)($r['cantidad'] ?? 0);

        if ($cantidad <= 0) {
            return null;
        }

        return [
            'costo'   => round((float)$r['total'] / $cantidad, 2),
            'compras' => (int)($r['compras'] ?? 0),
            'cantidad'=> $cantidad,
        ];
    }

    /**
     * Devuelve los precios de un producto cuando se anula un ingreso.
     *
     * - Si el costo actual es exactamente el que dejó ese ingreso, se restaura
     *   el valor anterior tal cual (reversión exacta).
     * - Si después hubo otros movimientos, se recalcula con las compras que
     *   siguen vigentes (nunca se deja el precio en cero).
     * - Todo queda registrado en el historial como REVERSION_INGRESO.
     *
     * @return array|null Detalle del cambio, o null si no había nada que revertir
     */
    public function revertirPreciosPorIngreso(int $idProducto, int $idIngreso, ?int $idUsuario = null, string $motivoExtra = ''): ?array
    {
        $actual = $this->db->fetchOne(
            "SELECT valor_compra, valor_venta, valor_unidad, rentabilidad
             FROM vb_productos WHERE id_producto = :id",
            ['id' => $idProducto]
        );
        if (!$actual) {
            return null;
        }

        $costoActual  = round((float)$actual['valor_compra'], 2);
        $ventaActual  = round((float)$actual['valor_venta'], 2);
        $unidadActual = ($actual['valor_unidad'] !== null) ? round((float)$actual['valor_unidad'], 2) : null;
        $rentabilidad = round((float)$actual['rentabilidad'], 2);

        // Fila del historial que dejó ese ingreso
        $h = $this->db->fetchOne(
            "SELECT * FROM vb_productos_precios_historial
              WHERE id_producto = :p AND id_referencia = :r AND origen = 'INGRESO'
              ORDER BY id_historial DESC LIMIT 1",
            ['p' => $idProducto, 'r' => $idIngreso]
        );

        if ($h && abs($costoActual - (float)$h['costo_nuevo']) < 0.01) {
            // Nadie movió el costo después de ese ingreso: reversión exacta
            $costoNuevo  = round((float)$h['costo_anterior'], 2);
            $ventaNueva  = round((float)$h['precio_venta_anterior'], 2);
            $unidadNueva = ($h['precio_unidad_anterior'] !== null) ? round((float)$h['precio_unidad_anterior'], 2) : $unidadActual;
            $motivo      = 'Reversión del ingreso #' . $idIngreso . ': se restauran los precios anteriores';
        } else {
            // Hubo otros movimientos: se recalcula con las compras vigentes
            $calc = $this->costoPromedioDeCompras($idProducto, $idIngreso);
            if ($calc === null) {
                return null; // ya no quedan compras con precio: mejor no inventar un costo
            }

            $costoNuevo  = $calc['costo'];
            $ventaNueva  = ($rentabilidad > 0 && $rentabilidad < 100)
                ? round($costoNuevo / (1 - $rentabilidad / 100), 2)
                : $ventaActual;
            if ($ventaNueva <= 0) {
                $ventaNueva = $ventaActual;
            }
            $unidadNueva = ($unidadActual !== null && $unidadActual > 0 && $ventaActual > 0)
                ? round($unidadActual * ($ventaNueva / $ventaActual), 2)
                : $unidadActual;
            $motivo = 'Reversión del ingreso #' . $idIngreso . ': costo recalculado con las compras vigentes (' . $calc['compras'] . ')';
        }

        if (abs($costoNuevo - $costoActual) < 0.01
            && abs($ventaNueva - $ventaActual) < 0.01
            && ($unidadNueva === null || $unidadActual === null || abs($unidadNueva - $unidadActual) < 0.01)) {
            return null; // nada que revertir
        }

        $rentabilidadFinal = ($ventaNueva > 0 && $costoNuevo > 0)
            ? round((($ventaNueva - $costoNuevo) / $ventaNueva) * 100, 2)
            : $rentabilidad;

        $this->db->executeAffected(
            "UPDATE vb_productos
                SET valor_compra = :compra, valor_venta = :venta,
                    valor_unidad = :unidad, rentabilidad = :rent
              WHERE id_producto = :id",
            [
                'compra' => $costoNuevo,
                'venta'  => $ventaNueva,
                'unidad' => $unidadNueva,
                'rent'   => $rentabilidadFinal,
                'id'     => $idProducto,
            ]
        );

        $this->registrarHistorial([
            'id_producto'            => $idProducto,
            'origen'                 => 'REVERSION_INGRESO',
            'id_referencia'          => $idIngreso,
            'cantidad'               => 0,
            'precio_compra'          => null,
            'costo_anterior'         => $costoActual,
            'costo_nuevo'            => $costoNuevo,
            'precio_venta_anterior'  => $ventaActual,
            'precio_venta_nuevo'     => $ventaNueva,
            'precio_unidad_anterior' => $unidadActual,
            'precio_unidad_nuevo'    => $unidadNueva,
            'rentabilidad'           => $rentabilidadFinal,
            'motivo'                 => trim($motivo . ($motivoExtra !== '' ? ' | ' . $motivoExtra : '')),
            'id_usuario'             => $idUsuario,
        ]);

        return [
            'costo_anterior'        => $costoActual,
            'costo_nuevo'           => $costoNuevo,
            'precio_venta_anterior' => $ventaActual,
            'precio_venta_nuevo'    => $ventaNueva,
        ];
    }

    /**
     * Historial de cambios de precio de un producto (lo más nuevo primero)
     */
    public function historial(int $idProducto, int $limit = 100): array
    {
        return $this->db->select(
            "SELECT h.*, u.nombre_usuario
             FROM vb_productos_precios_historial h
             LEFT JOIN vb_usuarios u ON h.id_usuario = u.id_usuario
             WHERE h.id_producto = :id
             ORDER BY h.created_at DESC, h.id_historial DESC
             LIMIT :limit",
            ['id' => $idProducto, 'limit' => $limit]
        );
    }

    /**
     * Historial de compras de un producto: cuánto costaba antes y cuánto ahora.
     * Se apoya solo en ingresos que tengan precio de compra registrado.
     */
    public function historialCompras(int $idProducto): array
    {
        return $this->db->select(
            "SELECT i.id_ingreso, i.fecha, i.cantidad_unidad, i.cantidad_fraccion,
                    i.valor_compra, i.observacion, u.nombre_usuario, p.descripcion, p.codigo
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON i.id_usuario = u.id_usuario
             WHERE i.id_producto = :id AND i.valor_compra IS NOT NULL
             ORDER BY i.fecha DESC, i.id_ingreso DESC
             LIMIT 200",
            ['id' => $idProducto]
        );
    }

    /**
     * Resumen de costos de compra de un producto:
     * primera compra, última compra y costo promedio vigente.
     */
    public function resumenCostos(int $idProducto): array
    {
        $primera = $this->db->fetchOne(
            "SELECT valor_compra, fecha FROM vb_ingresos
              WHERE id_producto = :id AND valor_compra IS NOT NULL AND valor_compra > 0
              ORDER BY fecha ASC, id_ingreso ASC LIMIT 1",
            ['id' => $idProducto]
        );

        $ultima = $this->db->fetchOne(
            "SELECT valor_compra, fecha FROM vb_ingresos
              WHERE id_producto = :id AND valor_compra IS NOT NULL AND valor_compra > 0
              ORDER BY fecha DESC, id_ingreso DESC LIMIT 1",
            ['id' => $idProducto]
        );

        $prod = $this->db->fetchOne(
            "SELECT valor_compra, valor_venta, valor_unidad, rentabilidad, precio_maximo_regulado
             FROM vb_productos WHERE id_producto = :id",
            ['id' => $idProducto]
        );

        return [
            'primera_compra' => $primera['valor_compra'] ?? null,
            'fecha_primera'  => $primera['fecha'] ?? null,
            'ultima_compra'  => $ultima['valor_compra'] ?? null,
            'fecha_ultima'   => $ultima['fecha'] ?? null,
            'costo_promedio' => $prod['valor_compra'] ?? null,
            'precio_venta'   => $prod['valor_venta'] ?? null,
            'precio_unidad'  => $prod['valor_unidad'] ?? null,
            'rentabilidad'   => $prod['rentabilidad'] ?? null,
            'tope_regulado'  => $prod['precio_maximo_regulado'] ?? null,
        ];
    }

    /**
     * Crear un ingreso y actualizar inventario automáticamente (usando triggers)
     * Soporta conversión cajas ↔ unidades según tipo_cantidad
     * Aplica precio promedio ponderado si el precio de compra cambia
     */
    public function crear(array $data, int $idUsuario): int
    {
        $idProducto = (int)($data['id_producto'] ?? 0);
        $cantUnd    = (int)($data['cantidad_unidad'] ?? 0);
        $cantFrac   = (int)($data['cantidad_fraccion'] ?? 0);
        $tipoCant   = $data['tipo_cantidad'] ?? 'UNIDAD';
        $obs        = $data['observacion'] ?? '';

        $prod = $this->db->fetchOne(
            "SELECT unidad_cerrada, valor_compra FROM vb_productos WHERE id_producto = :id",
            ['id' => $idProducto]
        );

        // Si no escriben precio de compra, se asume el costo que ya tiene el producto
        $nuevoValorCompra = (isset($data['valor_compra']) && $data['valor_compra'] !== '' && $data['valor_compra'] !== null)
            ? (float)$data['valor_compra']
            : (float)($prod['valor_compra'] ?? 0);

        // Cantidad con la que se calcula el PROMEDIO del costo.
        // El precio de compra es por unidad cerrada (caja), así que cuando el
        // ingreso es por cajas el promedio también se hace en cajas.
        $cantParaPromedio = $cantUnd;

        if ($tipoCant === 'CAJA') {
            $undPorCaja = (int)($prod['unidad_cerrada'] ?? 1);
            if ($undPorCaja < 1) $undPorCaja = 1;
            $totalUnd = $cantUnd * $undPorCaja;
            $obs = ($obs ? $obs . ' | ' : '') . "Ingreso por cajas: {$cantUnd} cajas × {$undPorCaja} und = {$totalUnd} und"
                 . ' | Compra: $' . number_format($nuevoValorCompra, 0, ',', '.') . ' por caja';
            $cantParaPromedio = $cantUnd; // el precio es por caja
            $cantUnd = $totalUnd;
        } else {
            $obs = ($obs ? $obs . ' | ' : '') . "Ingreso por unidades: {$cantUnd} und"
                 . ($cantFrac > 0 ? " + {$cantFrac} frac" : '')
                 . ' | Compra: $' . number_format($nuevoValorCompra, 0, ',', '.');
        }

        // La fila de inventario debe existir ANTES del INSERT:
        // los triggers suman el stock directamente en vb_inventario
        $this->asegurarInventario($idProducto);

        // El stock anterior se lee ANTES de insertar: una vez el trigger suma,
        // ya no se puede saber cuál era (ese era el error del costo promedio).
        $invAntes = $this->db->fetchOne(
            "SELECT unidad FROM vb_inventario WHERE id_producto = :id",
            ['id' => $idProducto]
        );
        $stockAntes = (int)($invAntes['unidad'] ?? 0);

        $id = $this->db->insert(
            "INSERT INTO vb_ingresos (id_producto, cantidad_unidad, cantidad_fraccion, valor_compra, tipo, observacion, id_usuario, fecha)
             VALUES (:prod, :und, :frac, :vcompra, :tipo, :obs, :uid, :fecha)",
            [
                'prod'    => $idProducto,
                'und'     => $cantUnd,
                'frac'    => $cantFrac,
                'vcompra' => $nuevoValorCompra,
                'tipo'    => $data['tipo'] ?? 'MANUAL',
                'obs'     => $obs,
                'uid'     => $idUsuario,
                'fecha'   => $data['fecha'] ?? date('Y-m-d'),
            ]
        );

        // Costo promedio ponderado + nuevo precio de venta + historial
        if ($cantParaPromedio > 0 && $nuevoValorCompra > 0) {
            $this->aplicarPreciosIngreso(
                $idProducto,
                $nuevoValorCompra,
                $cantParaPromedio,
                $stockAntes,
                $idUsuario,
                $id,
                $data['motivo_precio'] ?? ('Ingreso #' . $id)
            );
        }

        return $id;
    }

    /**
     * Crear la fila de inventario del producto si no existe.
     * Es obligatorio hacerlo antes de insertar en vb_ingresos porque los
     * triggers actualizan vb_inventario pero no crean la fila.
     */
    private function asegurarInventario(int $idProducto): void
    {
        $inv = $this->db->fetchOne(
            "SELECT id_inventario FROM vb_inventario WHERE id_producto = :id",
            ['id' => $idProducto]
        );

        if (!$inv) {
            $this->db->insert(
                "INSERT INTO vb_inventario (id_producto, unidad, fraccion) VALUES (:id, 0, 0)",
                ['id' => $idProducto]
            );
        }
    }

    /**
     * Buscar productos para ingreso
     */
    public function buscarProductos(string $q): array
    {
        return $this->db->select(
            "SELECT p.*, inv.unidad, inv.fraccion AS stock_fraccion
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND (p.codigo LIKE :q1 OR p.descripcion LIKE :q2
                  OR p.codigo_barras_1 LIKE :q3 OR p.codigo_barras_2 LIKE :q4
                  OR p.codigo_barras_3 LIKE :q5)
             ORDER BY p.descripcion ASC LIMIT 20",
            ['q1' => "%{$q}%", 'q2' => "%{$q}%", 'q3' => "%{$q}%", 'q4' => "%{$q}%", 'q5' => "%{$q}%"]
        );
    }

    /**
     * Listar facturas de compra (ingreso por factura)
     */
    public function listarFacturasCompra(): array
    {
        return $this->listarFacturas('TODAS');
    }

    // ============================================================
    // INGRESOS POR FACTURA
    // ============================================================

    /**
     * Listar facturas de compra con totales de productos
     *
     * @param string $filtro TODAS | PENDIENTES | APLICADAS
     */
    public function listarFacturas(string $filtro = 'TODAS', string $buscar = ''): array
    {
        $where  = [];
        $params = [];

        if ($filtro === 'PENDIENTES') {
            $where[] = 'f.estado = 0';
        } elseif ($filtro === 'APLICADAS') {
            $where[] = 'f.estado = 1';
        }

        if ($buscar !== '') {
            $where[] = '(f.nombre_factura LIKE :buscar OR prov.nombre LIKE :buscar)';
            $params['buscar'] = "%{$buscar}%";
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        return $this->db->select(
            "SELECT f.*, prov.nombre AS proveedor_nombre,
                    (SELECT COUNT(*) FROM vb_ingresos_detalle_factura d
                      WHERE d.id_ingreso_factura = f.id_ingreso_factura) AS total_productos,
                    (SELECT COALESCE(SUM(d.cantidad_unidad), 0) FROM vb_ingresos_detalle_factura d
                      WHERE d.id_ingreso_factura = f.id_ingreso_factura) AS total_unidad,
                    (SELECT COALESCE(SUM(d.cantidad_fraccion), 0) FROM vb_ingresos_detalle_factura d
                      WHERE d.id_ingreso_factura = f.id_ingreso_factura) AS total_fraccion
             FROM vb_ingresos_factura f
             LEFT JOIN vb_proveedores prov ON f.id_proveedor = prov.id_proveedor
             {$sqlWhere}
             ORDER BY f.estado ASC, f.fecha DESC, f.id_ingreso_factura DESC
             LIMIT 200",
            $params
        );
    }

    /**
     * Obtener una factura de compra
     */
    public function obtenerFactura(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT f.*, prov.nombre AS proveedor_nombre
             FROM vb_ingresos_factura f
             LEFT JOIN vb_proveedores prov ON f.id_proveedor = prov.id_proveedor
             WHERE f.id_ingreso_factura = :id",
            ['id' => $id]
        );
    }

    /**
     * Crear una factura de compra (cabecera)
     */
    public function crearFactura(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO vb_ingresos_factura (nombre_factura, id_proveedor, fecha, estado)
             VALUES (:nombre, :prov, :fecha, 0)",
            [
                'nombre' => $data['nombre_factura'],
                'prov'   => !empty($data['id_proveedor']) ? (int)$data['id_proveedor'] : null,
                'fecha'  => $data['fecha'] ?? date('Y-m-d'),
            ]
        );
    }

    /**
     * Actualizar cabecera de una factura de compra
     */
    public function actualizarFactura(int $id, array $data): int
    {
        return $this->db->executeAffected(
            "UPDATE vb_ingresos_factura
             SET nombre_factura = :nombre, id_proveedor = :prov, fecha = :fecha
             WHERE id_ingreso_factura = :id",
            [
                'nombre' => $data['nombre_factura'],
                'prov'   => !empty($data['id_proveedor']) ? (int)$data['id_proveedor'] : null,
                'fecha'  => $data['fecha'] ?? date('Y-m-d'),
                'id'     => $id,
            ]
        );
    }

    /**
     * Eliminar una factura de compra y su detalle.
     * Si ya fue aplicada al inventario, primero se revierte.
     */
    public function eliminarFactura(int $id, int $idUsuario): void
    {
        $factura = $this->obtenerFactura($id);
        if (!$factura) {
            throw new \RuntimeException('La factura no existe');
        }

        if ((int)$factura['estado'] === 1) {
            $this->revertirFactura($id, $idUsuario);
        }

        $this->db->beginTransaction();
        try {
            $this->db->executeAffected(
                "DELETE FROM vb_ingresos_detalle_factura WHERE id_ingreso_factura = :id",
                ['id' => $id]
            );
            $this->db->executeAffected(
                "DELETE FROM vb_ingresos_factura WHERE id_ingreso_factura = :id",
                ['id' => $id]
            );
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Detalle (productos) de una factura de compra
     *
     * precio_compra  = lo que se paga en ESTA factura (lo que digita el usuario)
     * costo_promedio = el costo que el producto tiene hoy en el sistema
     */
    public function listarDetalleFactura(int $idFactura): array
    {
        return $this->db->select(
            "SELECT d.id_detalle, d.id_ingreso_factura, d.id_producto,
                    d.cantidad_unidad, d.cantidad_fraccion,
                    d.valor_compra AS precio_compra,
                    p.codigo, p.descripcion, p.presentacion, p.codigo_barras_1,
                    p.valor_compra AS costo_promedio, p.valor_venta, p.valor_unidad, p.unidad_cerrada,
                    inv.unidad AS stock_unidad, inv.fraccion AS stock_fraccion
             FROM vb_ingresos_detalle_factura d
             JOIN vb_productos p ON d.id_producto = p.id_producto
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE d.id_ingreso_factura = :id
             ORDER BY d.id_detalle ASC",
            ['id' => $idFactura]
        );
    }

    /**
     * Agregar un producto a la factura. Si ya existe, suma cantidades.
     *
     * @param float|null $valorCompra Precio pagado por unidad cerrada. Si no se
     *                                envía, se toma el costo que hoy tiene el producto.
     */
    public function agregarProductoFactura(int $idFactura, int $idProducto, int $cantU, int $cantF, ?float $valorCompra = null): int
    {
        $factura = $this->obtenerFactura($idFactura);
        if (!$factura) {
            throw new \RuntimeException('La factura no existe');
        }
        if ((int)$factura['estado'] === 1) {
            throw new \RuntimeException('La factura ya fue pasada a inventario. Reviértala para editarla.');
        }

        $prod = $this->db->fetchOne(
            "SELECT id_producto, valor_compra FROM vb_productos WHERE id_producto = :id AND activo = 1",
            ['id' => $idProducto]
        );
        if (!$prod) {
            throw new \RuntimeException('El producto no existe o está inactivo');
        }

        // Si no digitaron precio, se usa el costo que ya tiene el producto
        $precio = ($valorCompra !== null && $valorCompra > 0)
            ? round($valorCompra, 2)
            : (float)($prod['valor_compra'] ?? 0);

        $existente = $this->db->fetchOne(
            "SELECT id_detalle, cantidad_unidad, cantidad_fraccion, valor_compra
             FROM vb_ingresos_detalle_factura
             WHERE id_ingreso_factura = :f AND id_producto = :p",
            ['f' => $idFactura, 'p' => $idProducto]
        );

        if ($existente) {
            // Si ya estaba cargado y ahora digitan un precio, se respeta el nuevo
            $precioFinal = ($valorCompra !== null && $valorCompra > 0)
                ? round($valorCompra, 2)
                : ($existente['valor_compra'] !== null ? (float)$existente['valor_compra'] : $precio);

            $this->db->executeAffected(
                "UPDATE vb_ingresos_detalle_factura
                 SET cantidad_unidad = :u, cantidad_fraccion = :fr, valor_compra = :v
                 WHERE id_detalle = :id",
                [
                    'u'  => (int)$existente['cantidad_unidad'] + $cantU,
                    'fr' => (int)$existente['cantidad_fraccion'] + $cantF,
                    'v'  => $precioFinal,
                    'id' => $existente['id_detalle'],
                ]
            );
            return (int)$existente['id_detalle'];
        }

        return $this->db->insert(
            "INSERT INTO vb_ingresos_detalle_factura
                (id_ingreso_factura, id_producto, cantidad_unidad, cantidad_fraccion, valor_compra)
             VALUES (:f, :p, :u, :fr, :v)",
            ['f' => $idFactura, 'p' => $idProducto, 'u' => $cantU, 'fr' => $cantF, 'v' => $precio]
        );
    }

    /**
     * Quitar un producto del detalle de la factura
     */
    public function quitarProductoFactura(int $idDetalle): int
    {
        return $this->db->executeAffected(
            "DELETE FROM vb_ingresos_detalle_factura WHERE id_detalle = :id",
            ['id' => $idDetalle]
        );
    }

    /**
     * Pasar la factura completa al inventario:
     * genera un ingreso por cada producto (tipo FACTURA_COMPRA) y marca la factura como aplicada.
     * Los triggers de vb_ingresos actualizan vb_inventario y vb_movimientos_inventario.
     */
    public function pasarFacturaAInventario(int $idFactura, int $idUsuario): array
    {
        $factura = $this->obtenerFactura($idFactura);
        if (!$factura) {
            throw new \RuntimeException('La factura no existe');
        }
        if ((int)$factura['estado'] === 1) {
            throw new \RuntimeException('Esta factura ya fue pasada a inventario');
        }

        $detalle = $this->listarDetalleFactura($idFactura);
        if (!$detalle) {
            throw new \RuntimeException('La factura no tiene productos cargados');
        }

        $this->db->beginTransaction();
        try {
            $productos = 0;
            $totalUnidad = 0;
            $totalFraccion = 0;

            foreach ($detalle as $d) {
                $idProducto = (int)$d['id_producto'];
                $cantU = (int)$d['cantidad_unidad'];
                $cantF = (int)$d['cantidad_fraccion'];

                if ($cantU <= 0 && $cantF <= 0) {
                    continue;
                }

                // La fila de inventario debe existir antes del INSERT (el trigger no crea filas)
                $this->asegurarInventario($idProducto);

                // Stock ANTES del insert (el trigger suma después)
                $invAntes = $this->db->fetchOne(
                    "SELECT unidad FROM vb_inventario WHERE id_producto = :id",
                    ['id' => $idProducto]
                );
                $stockAntes = (int)($invAntes['unidad'] ?? 0);

                // Precio REAL pagado en esta factura. Si la línea es vieja y no tiene
                // precio propio, se usa el costo que ya tenía el producto.
                $precioCompra = (float)($d['precio_compra'] ?? 0);
                if ($precioCompra <= 0) {
                    $precioCompra = (float)($d['costo_promedio'] ?? 0);
                }

                $idIngreso = $this->db->insert(
                    "INSERT INTO vb_ingresos
                        (id_producto, cantidad_unidad, cantidad_fraccion, valor_compra, tipo,
                         id_factura_compra, observacion, id_usuario, fecha)
                     VALUES (:prod, :und, :frac, :vcompra, 'FACTURA_COMPRA', :fac, :obs, :uid, :fecha)",
                    [
                        'prod'    => $idProducto,
                        'und'     => $cantU,
                        'frac'    => $cantF,
                        'vcompra' => $precioCompra > 0 ? $precioCompra : null,
                        'fac'     => $idFactura,
                        'obs'     => 'Factura de compra: ' . $factura['nombre_factura']
                                   . ' | Compra: $' . number_format($precioCompra, 0, ',', '.'),
                        'uid'     => $idUsuario,
                        'fecha'   => $factura['fecha'] ?: date('Y-m-d'),
                    ]
                );

                if ($precioCompra > 0 && $cantU > 0) {
                    $this->aplicarPreciosIngreso(
                        $idProducto,
                        $precioCompra,
                        $cantU,
                        $stockAntes,
                        $idUsuario,
                        (int)$idIngreso,
                        'Factura de compra: ' . $factura['nombre_factura']
                    );
                }

                $productos++;
                $totalUnidad += $cantU;
                $totalFraccion += $cantF;
            }

            if ($productos === 0) {
                throw new \RuntimeException('La factura no tiene cantidades válidas');
            }

            $this->db->executeAffected(
                "UPDATE vb_ingresos_factura SET estado = 1 WHERE id_ingreso_factura = :id",
                ['id' => $idFactura]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return [
            'id_ingreso_factura' => $idFactura,
            'productos'          => $productos,
            'unidad'             => $totalUnidad,
            'fraccion'           => $totalFraccion,
        ];
    }

    /**
     * Revertir una factura aplicada: elimina los ingresos generados y devuelve el stock
     */
    public function revertirFactura(int $idFactura, int $idUsuario): array
    {
        $factura = $this->obtenerFactura($idFactura);
        if (!$factura) {
            throw new \RuntimeException('La factura no existe');
        }
        if ((int)$factura['estado'] === 0) {
            throw new \RuntimeException('Esta factura no ha sido pasada a inventario');
        }

        $ingresos = $this->db->select(
            "SELECT id_ingreso, id_producto, cantidad_unidad, cantidad_fraccion
             FROM vb_ingresos WHERE id_factura_compra = :id
             ORDER BY id_ingreso DESC",
            ['id' => $idFactura]
        );

        $this->db->beginTransaction();
        try {
            foreach ($ingresos as $ing) {
                $idProducto = (int)$ing['id_producto'];
                $cantU = (int)$ing['cantidad_unidad'];
                $cantF = (int)$ing['cantidad_fraccion'];

                $this->db->executeAffected(
                    "UPDATE vb_inventario
                     SET unidad = GREATEST(unidad - :u, 0),
                         fraccion = GREATEST(fraccion - :f, 0)
                     WHERE id_producto = :p",
                    ['u' => $cantU, 'f' => $cantF, 'p' => $idProducto]
                );

                // Se devuelve el costo que dejó ese ingreso (del más nuevo al más viejo,
                // así la reversión sigue la misma cadena en que se aplicaron los precios)
                $this->revertirPreciosPorIngreso(
                    $idProducto,
                    (int)$ing['id_ingreso'],
                    $idUsuario,
                    'Se revirtió la factura de compra: ' . $factura['nombre_factura']
                );

                $this->db->executeAffected(
                    "DELETE FROM vb_ingresos WHERE id_ingreso = :id",
                    ['id' => $ing['id_ingreso']]
                );

                $inv = $this->db->fetchOne(
                    "SELECT unidad, fraccion FROM vb_inventario WHERE id_producto = :p",
                    ['p' => $idProducto]
                );

                $this->db->insert(
                    "INSERT INTO vb_movimientos_inventario
                        (id_producto, tipo, unidad, fraccion, unidad_resultante, fraccion_resultante,
                         observacion, id_usuario, created_at)
                     VALUES (:p, 'AJUSTE', :u, :f, :ur, :fr, :obs, :uid, NOW())",
                    [
                        'p'   => $idProducto,
                        'u'   => -1 * $cantU,
                        'f'   => -1 * $cantF,
                        'ur'  => (int)($inv['unidad'] ?? 0),
                        'fr'  => (int)($inv['fraccion'] ?? 0),
                        'obs' => 'Reversa de factura de compra: ' . $factura['nombre_factura'],
                        'uid' => $idUsuario,
                    ]
                );
            }

            $this->db->executeAffected(
                "UPDATE vb_ingresos_factura SET estado = 0 WHERE id_ingreso_factura = :id",
                ['id' => $idFactura]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return ['id_ingreso_factura' => $idFactura, 'revertidos' => count($ingresos)];
    }

    // ============================================================
    // CONSULTAS Y MANTENIMIENTO DE INGRESOS
    // ============================================================

    /**
     * Obtener un ingreso con datos del producto
     */
    public function obtener(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT i.*, p.codigo, p.descripcion, p.presentacion, p.unidad_cerrada,
                    p.valor_compra, p.valor_venta, p.valor_unidad,
                    u.nombre_usuario,
                    f.nombre_factura
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON i.id_usuario = u.id_usuario
             LEFT JOIN vb_ingresos_factura f ON i.id_factura_compra = f.id_ingreso_factura
             WHERE i.id_ingreso = :id",
            ['id' => $id]
        );
    }

    /**
     * Ingresos en un rango de fechas con totales de inversión, venta y ganancia
     */
    public function porFecha(string $desde, string $hasta, string $tipo = 'TODOS'): array
    {
        $params = ['desde' => $desde, 'hasta' => $hasta];
        $sqlTipo = '';

        if ($tipo === 'MANUAL' || $tipo === 'FACTURA_COMPRA') {
            $sqlTipo = ' AND i.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        $rows = $this->db->select(
            "SELECT i.id_ingreso, i.fecha, i.tipo, i.cantidad_unidad, i.cantidad_fraccion,
                    i.observacion, i.id_factura_compra,
                    p.id_producto, p.codigo, p.descripcion, p.presentacion, p.unidad_cerrada,
                    p.valor_compra, p.valor_venta, p.valor_unidad,
                    u.nombre_usuario
             FROM vb_ingresos i
             JOIN vb_productos p ON i.id_producto = p.id_producto
             LEFT JOIN vb_usuarios u ON i.id_usuario = u.id_usuario
             WHERE i.fecha BETWEEN :desde AND :hasta {$sqlTipo}
             ORDER BY i.fecha DESC, i.id_ingreso DESC",
            $params
        );

        $totalUnidad = 0;
        $totalFraccion = 0;
        $inversion = 0.0;
        $venta = 0.0;

        foreach ($rows as &$r) {
            $cantU = (int)$r['cantidad_unidad'];
            $cantF = (int)$r['cantidad_fraccion'];
            $undCerrada = max(1, (int)($r['unidad_cerrada'] ?? 1));
            $valorCompra = (float)($r['valor_compra'] ?? 0);
            $valorVenta = (float)($r['valor_venta'] ?? 0);
            $valorUnidad = (float)($r['valor_unidad'] ?? 0);

            $r['inversion'] = round($cantU * $valorCompra + $cantF * ($valorCompra / $undCerrada), 2);
            $r['venta']     = round($cantU * $valorVenta + $cantF * $valorUnidad, 2);
            $r['ganancia']  = round($r['venta'] - $r['inversion'], 2);

            $totalUnidad += $cantU;
            $totalFraccion += $cantF;
            $inversion += $r['inversion'];
            $venta += $r['venta'];
        }
        unset($r);

        return [
            'data'    => $rows,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'totales' => [
                'registros' => count($rows),
                'unidad'    => $totalUnidad,
                'fraccion'  => $totalFraccion,
                'inversion' => round($inversion, 2),
                'venta'     => round($venta, 2),
                'ganancia'  => round($venta - $inversion, 2),
            ],
        ];
    }

    /**
     * Eliminar un ingreso revirtiendo el stock (solo ingresos manuales)
     */
    public function eliminar(int $id, int $idUsuario): ?array
    {
        $ing = $this->obtener($id);
        if (!$ing) {
            throw new \RuntimeException('El ingreso no existe');
        }
        if ($ing['tipo'] === 'FACTURA_COMPRA') {
            throw new \RuntimeException('Este ingreso viene de una factura de compra: elimine la factura completa.');
        }

        $idProducto = (int)$ing['id_producto'];
        $cantU = (int)$ing['cantidad_unidad'];
        $cantF = (int)$ing['cantidad_fraccion'];
        $reversion = null;

        $this->db->beginTransaction();
        try {
            $this->db->executeAffected(
                "UPDATE vb_inventario
                 SET unidad = GREATEST(unidad - :u, 0),
                     fraccion = GREATEST(fraccion - :f, 0)
                 WHERE id_producto = :p",
                ['u' => $cantU, 'f' => $cantF, 'p' => $idProducto]
            );

            // Si ese ingreso había movido el costo, se devuelve a su valor anterior.
            // Sin esto el costo promedio quedaba inflado y el producto "valía" más de lo real.
            $reversion = $this->revertirPreciosPorIngreso(
                $idProducto,
                $id,
                $idUsuario,
                'Se eliminó el ingreso #' . $id
            );

            $this->db->executeAffected(
                "DELETE FROM vb_ingresos WHERE id_ingreso = :id",
                ['id' => $id]
            );

            $inv = $this->db->fetchOne(
                "SELECT unidad, fraccion FROM vb_inventario WHERE id_producto = :p",
                ['p' => $idProducto]
            );

            $this->db->insert(
                "INSERT INTO vb_movimientos_inventario
                    (id_producto, tipo, unidad, fraccion, unidad_resultante, fraccion_resultante,
                     observacion, id_usuario, created_at)
                 VALUES (:p, 'AJUSTE', :u, :f, :ur, :fr, :obs, :uid, NOW())",
                [
                    'p'   => $idProducto,
                    'u'   => -1 * $cantU,
                    'f'   => -1 * $cantF,
                    'ur'  => (int)($inv['unidad'] ?? 0),
                    'fr'  => (int)($inv['fraccion'] ?? 0),
                    'obs' => 'Reversa por eliminación del ingreso #' . $id,
                    'uid' => $idUsuario,
                ]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return $reversion;
    }
}
