<?php
namespace SIG\Controllers;

use SIG\Core\Request;
use SIG\Core\Response;
use SIG\Core\View;
use SIG\Core\Session;
use SIG\Models\Ingreso;

class IngresoController
{
    private Ingreso $model;
    private Session $session;

    public function __construct()
    {
        $this->model   = new Ingreso();
        $this->session = Session::getInstance();
    }

    public function index(Request $r): string
    {
        $view = new View();
        $proveedorModel = new \SIG\Models\Proveedor();
        $proveedores = $proveedorModel->listar(1, 500)['data'] ?? [];

        return $view->render('ingresos/index', [
            'title'       => 'Ingresos',
            'facturas'    => $this->model->listarFacturasCompra(),
            'proveedores' => $proveedores,
            'username'    => $this->session->get('username'),
            'userTipo'    => $this->session->getUserType(),
            'userImagen'  => $this->session->get('user_imagen'),
        ]);
    }

    public function listar(Request $r): void
    {
        Response::success($this->model->listar((int)$r->get('page', 1)));
    }

    public function store(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['id_producto'])) {
            Response::error('Debe seleccionar un producto', 422);
        }

        $cantU = (int)($data['cantidad_unidad'] ?? 0);
        $cantF = (int)($data['cantidad_fraccion'] ?? 0);

        if ($cantU <= 0 && $cantF <= 0) {
            Response::error('Debe ingresar al menos una cantidad', 422);
        }

        // Validar que si es por cajas, al menos haya cantidad
        $tipoCant = $data['tipo_cantidad'] ?? 'UNIDAD';
        if ($tipoCant === 'CAJA' && $cantU <= 0) {
            Response::error('Debe ingresar al menos una caja', 422);
        }

        try {
            $id = $this->model->crear($data, $this->session->getUserId());

            $mensaje = 'Ingreso registrado correctamente';
            $calc = $this->model->ultimoCalculo;
            if ($calc) {
                $mensaje .= ' | Costo: $' . number_format($calc['costo_actual'], 0, ',', '.') . ' → $' . number_format($calc['costo_nuevo'], 0, ',', '.');
                if ($calc['cambia_venta']) {
                    $mensaje .= ' | Precio venta: $' . number_format($calc['venta_actual'], 0, ',', '.') . ' → $' . number_format($calc['venta_nueva'], 0, ',', '.');
                }
                if ($calc['aviso'] !== '') {
                    $mensaje .= ' | ' . $calc['aviso'];
                }
            }

            Response::success(['id' => $id, 'calculo' => $calc], $mensaje);
        } catch (\Exception $e) {
            Response::error('Error al registrar ingreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ingreso rápido por código (solo código + cantidad)
     */
    public function rapido(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        if (empty($data['codigo'])) {
            Response::error('Debe ingresar un código de producto', 422);
        }

        // Buscar producto por código o código de barras
        $db = \SIG\Core\Database::getInstance();
        $producto = $db->fetchOne(
            "SELECT p.*, inv.unidad AS stock_actual, inv.fraccion AS stock_frac
             FROM vb_productos p
             LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
             WHERE p.activo = 1 AND (p.codigo = :cod OR p.codigo_barras_1 = :cod2 OR p.codigo_barras_2 = :cod3 OR p.codigo_barras_3 = :cod4)",
            ['cod' => $data['codigo'], 'cod2' => $data['codigo'], 'cod3' => $data['codigo'], 'cod4' => $data['codigo']]
        );

        if (!$producto) {
            Response::error('Producto no encontrado con ese código', 404);
        }

        $data['id_producto'] = $producto['id_producto'];
        $data['tipo_cantidad'] = $data['tipo_cantidad'] ?? 'UNIDAD';

        try {
            $id = $this->model->crear($data, $this->session->getUserId());
            $nuevoStock = $db->fetchOne(
                "SELECT unidad, fraccion FROM vb_inventario WHERE id_producto = :id",
                ['id' => $producto['id_producto']]
            );
            Response::success([
                'id_ingreso' => $id,
                'producto'   => $producto['descripcion'],
                'codigo'     => $producto['codigo'],
                'stock_anterior' => $producto['stock_actual'] ?? 0,
                'stock_nuevo'    => $nuevoStock['unidad'] ?? 0,
            ], 'Ingreso rápido registrado correctamente');
        } catch (\Exception $e) {
            Response::error('Error en ingreso rápido: ' . $e->getMessage(), 500);
        }
    }

    public function buscarProductos(Request $r): void
    {
        $q = $r->get('q', '');
        Response::success($this->model->buscarProductos($q));
    }

    /**
     * Previsualizar el efecto de un ingreso sobre el costo y el precio de venta.
     * NO escribe nada: solo muestra qué va a pasar antes de guardar.
     *
     * GET /ingresos/previsualizar?id_producto=1&cantidad_unidad=5&tipo_cantidad=CAJA&valor_compra=25000
     */
    public function previsualizar(Request $r): void
    {
        $idProducto = (int)$r->get('id_producto', 0);
        if ($idProducto <= 0) {
            Response::error('Debe seleccionar un producto', 422);
            return;
        }

        $cantidad = (int)$r->get('cantidad_unidad', 0);
        $tipoCant = (string)$r->get('tipo_cantidad', 'UNIDAD');
        $precio   = $r->get('valor_compra', null);
        $precio   = ($precio === null || $precio === '') ? null : (float)$precio;

        try {
            $db = \SIG\Core\Database::getInstance();
            $prod = $db->fetchOne(
                "SELECT valor_compra, valor_venta, valor_unidad, rentabilidad, unidad_cerrada,
                        precio_maximo_regulado, descripcion, codigo
                 FROM vb_productos WHERE id_producto = :id",
                ['id' => $idProducto]
            );

            if (!$prod) {
                Response::error('Producto no encontrado', 404);
                return;
            }

            // Si no escribieron precio, se usa el costo vigente del producto
            $precioCompra = $precio !== null ? $precio : (float)$prod['valor_compra'];

            // Misma equivalencia que en crear(): el precio de compra es por caja
            $cantParaPromedio = $cantidad;
            $descripcionCantidad = $cantidad . ' unidad(es)';
            if ($tipoCant === 'CAJA') {
                $undPorCaja = max(1, (int)($prod['unidad_cerrada'] ?? 1));
                $descripcionCantidad = $cantidad . ' caja(s) × ' . $undPorCaja . ' und = ' . ($cantidad * $undPorCaja) . ' und';
            }

            $calc = $this->model->calcularPreciosIngreso($idProducto, $precioCompra, $cantParaPromedio);

            Response::success([
                'producto'             => $prod['descripcion'],
                'codigo'               => $prod['codigo'],
                'descripcion_cantidad' => $descripcionCantidad,
                'calculo'              => $calc,
            ]);
        } catch (\Exception $e) {
            Response::error('Error al previsualizar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Historial de precios y de compras de un producto
     * GET /ingresos/historial-precios?id_producto=1
     */
    public function historialPrecios(Request $r): void
    {
        $idProducto = (int)$r->get('id_producto', 0);
        if ($idProducto <= 0) {
            Response::error('Debe seleccionar un producto', 422);
            return;
        }

        $db = \SIG\Core\Database::getInstance();
        $prod = $db->fetchOne(
            "SELECT codigo, descripcion FROM vb_productos WHERE id_producto = :id",
            ['id' => $idProducto]
        );

        Response::success([
            'producto' => $prod ?: null,
            'resumen'  => $this->model->resumenCostos($idProducto),
            'historial'=> $this->model->historial($idProducto),
            'compras'  => $this->model->historialCompras($idProducto),
        ]);
    }

    // ============================================================
    // INGRESOS POR FACTURA (facturas de compra)
    // ============================================================

    /**
     * Listar facturas de compra
     * GET /ingresos/facturas?filtro=TODAS|PENDIENTES|APLICADAS&buscar=
     */
    public function facturas(Request $r): void
    {
        $filtro = (string)$r->get('filtro', 'TODAS');
        $buscar = trim((string)$r->get('buscar', ''));
        Response::success($this->model->listarFacturas($filtro, $buscar));
    }

    /**
     * Crear una factura de compra
     * POST /ingresos/factura/crear
     */
    public function crearFactura(Request $r): void
    {
        $data   = $r->json() ?: $r->all();
        $nombre = trim((string)($data['nombre_factura'] ?? ''));

        if ($nombre === '') {
            Response::error('Debe indicar el nombre o número de la factura', 422);
        }

        $data['nombre_factura'] = $nombre;

        try {
            $id = $this->model->crearFactura($data);
            Response::success(['id_ingreso_factura' => $id], 'Factura creada correctamente');
        } catch (\Exception $e) {
            Response::error('Error al crear la factura: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar la cabecera de una factura de compra
     * POST /ingresos/factura/actualizar
     */
    public function actualizarFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_ingreso_factura'] ?? 0);
        $nombre = trim((string)($data['nombre_factura'] ?? ''));

        if ($id <= 0) {
            Response::error('Factura no válida', 422);
        }
        if ($nombre === '') {
            Response::error('Debe indicar el nombre o número de la factura', 422);
        }

        $data['nombre_factura'] = $nombre;

        try {
            $this->model->actualizarFactura($id, $data);
            Response::success(['id_ingreso_factura' => $id], 'Factura actualizada correctamente');
        } catch (\Exception $e) {
            Response::error('Error al actualizar la factura: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener factura + detalle de productos
     * GET /ingresos/factura/detalle?id=1
     */
    public function detalleFactura(Request $r): void
    {
        $id = (int)$r->get('id', 0);
        if ($id <= 0) {
            Response::error('Factura no válida', 422);
        }

        $factura = $this->model->obtenerFactura($id);
        if (!$factura) {
            Response::error('La factura no existe', 404);
        }

        $detalle = $this->model->listarDetalleFactura($id);

        $totalUnidad = 0;
        $totalFraccion = 0;
        $valorCompra = 0.0;
        foreach ($detalle as $d) {
            $totalUnidad   += (int)$d['cantidad_unidad'];
            $totalFraccion += (int)$d['cantidad_fraccion'];
            // Lo que se paga de verdad en esta factura (si la línea no tiene precio, se usa el costo del producto)
            $precioLinea = (float)($d['precio_compra'] ?? 0);
            if ($precioLinea <= 0) {
                $precioLinea = (float)($d['costo_promedio'] ?? 0);
            }
            $valorCompra += (int)$d['cantidad_unidad'] * $precioLinea;
        }

        Response::success([
            'factura' => $factura,
            'detalle' => $detalle,
            'totales' => [
                'productos' => count($detalle),
                'unidad'    => $totalUnidad,
                'fraccion'  => $totalFraccion,
                'inversion' => round($valorCompra, 2),
            ],
        ]);
    }

    /**
     * Agregar producto a una factura de compra
     * POST /ingresos/factura/agregar
     */
    public function agregarProductoFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();

        $idFactura = (int)($data['id_ingreso_factura'] ?? 0);
        $idProducto = (int)($data['id_producto'] ?? 0);
        $cantU = (int)($data['cantidad_unidad'] ?? 0);
        $cantF = (int)($data['cantidad_fraccion'] ?? 0);
        $valorCompra = (isset($data['valor_compra']) && $data['valor_compra'] !== '' && $data['valor_compra'] !== null)
            ? (float)$data['valor_compra'] : null;

        if ($idFactura <= 0) {
            Response::error('Factura no válida', 422);
        }
        if ($idProducto <= 0) {
            Response::error('Debe seleccionar un producto', 422);
        }
        if ($cantU <= 0 && $cantF <= 0) {
            Response::error('Debe ingresar al menos una cantidad', 422);
        }

        try {
            $idDetalle = $this->model->agregarProductoFactura($idFactura, $idProducto, $cantU, $cantF, $valorCompra);
            Response::success(['id_detalle' => $idDetalle], 'Producto agregado a la factura');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Quitar producto del detalle de la factura
     * POST /ingresos/factura/quitar
     */
    public function quitarProductoFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $idDetalle = (int)($data['id_detalle'] ?? 0);

        if ($idDetalle <= 0) {
            Response::error('Registro no válido', 422);
        }

        try {
            $this->model->quitarProductoFactura($idDetalle);
            Response::success(null, 'Producto quitado de la factura');
        } catch (\Exception $e) {
            Response::error('Error al quitar el producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Pasar la factura completa al inventario
     * POST /ingresos/factura/pasar
     */
    public function pasarFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $idFactura = (int)($data['id_ingreso_factura'] ?? 0);

        if ($idFactura <= 0) {
            Response::error('Factura no válida', 422);
        }

        try {
            $res = $this->model->pasarFacturaAInventario($idFactura, $this->session->getUserId());
            Response::success($res, "Factura pasada a inventario: {$res['productos']} producto(s)");
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Revertir una factura ya aplicada al inventario
     * POST /ingresos/factura/revertir
     */
    public function revertirFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $idFactura = (int)($data['id_ingreso_factura'] ?? 0);

        if ($idFactura <= 0) {
            Response::error('Factura no válida', 422);
        }

        try {
            $res = $this->model->revertirFactura($idFactura, $this->session->getUserId());
            Response::success($res, "Factura revertida: {$res['revertidos']} movimiento(s) deshecho(s)");
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Eliminar una factura de compra (con su detalle)
     * POST /ingresos/factura/eliminar
     */
    public function eliminarFactura(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $idFactura = (int)($data['id_ingreso_factura'] ?? 0);

        if ($idFactura <= 0) {
            Response::error('Factura no válida', 422);
        }

        try {
            $this->model->eliminarFactura($idFactura, $this->session->getUserId());
            Response::success(null, 'Factura eliminada correctamente');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    // ============================================================
    // CONSULTAS
    // ============================================================

    /**
     * Ingresos por rango de fechas con totales
     * GET /ingresos/por-fecha?desde=&hasta=&tipo=
     */
    public function porFecha(Request $r): void
    {
        $desde = (string)$r->get('desde', date('Y-m-01'));
        $hasta = (string)$r->get('hasta', date('Y-m-d'));
        $tipo  = (string)$r->get('tipo', 'TODOS');

        if (!$this->fechaValida($desde) || !$this->fechaValida($hasta)) {
            Response::error('Las fechas no son válidas', 422);
        }
        if ($desde > $hasta) {
            Response::error('La fecha inicial no puede ser mayor que la final', 422);
        }

        Response::success($this->model->porFecha($desde, $hasta, $tipo));
    }

    /**
     * Detalle de un ingreso
     * GET /ingresos/obtener/{id}
     */
    public function obtener(Request $r): void
    {
        $id = (int)$r->param('id', 0);
        $ingreso = $id > 0 ? $this->model->obtener($id) : null;

        if (!$ingreso) {
            Response::error('El ingreso no existe', 404);
        }

        Response::success($ingreso);
    }

    /**
     * Eliminar un ingreso manual (revierte el stock y el costo)
     * POST /ingresos/eliminar
     */
    public function eliminar(Request $r): void
    {
        $data = $r->json() ?: $r->all();
        $id   = (int)($data['id_ingreso'] ?? 0);

        if ($id <= 0) {
            Response::error('Ingreso no válido', 422);
        }

        try {
            $reversion = $this->model->eliminar($id, $this->session->getUserId());

            $mensaje = 'Ingreso eliminado y stock revertido';
            if (!empty($reversion)) {
                $mensaje .= ' | Costo: $' . number_format($reversion['costo_anterior'], 0, ',', '.')
                          . ' → $' . number_format($reversion['costo_nuevo'], 0, ',', '.');
                if (abs($reversion['precio_venta_nuevo'] - $reversion['precio_venta_anterior']) >= 0.01) {
                    $mensaje .= ' | Precio de venta: $' . number_format($reversion['precio_venta_anterior'], 0, ',', '.')
                              . ' → $' . number_format($reversion['precio_venta_nuevo'], 0, ',', '.');
                }
            }

            Response::success(['reversion' => $reversion], $mensaje);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    /**
     * Validar formato de fecha AAAA-MM-DD
     */
    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $fecha));
        return checkdate($m, $d, $y);
    }
}
