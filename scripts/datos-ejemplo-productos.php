<?php
/**
 * Limpia todo el movimiento y recrea productos de ejemplo con los 4 tipos de venta.
 *
 * Uso: php scripts/datos-ejemplo-productos.php [--si]
 *
 * Borra: facturas, devoluciones, ingresos, egresos, créditos, planes separe,
 * turnos de caja, base diaria, cierres de inventario, lotes, movimientos y
 * productos. Conserva clientes, proveedores, usuarios y catálogos.
 * Haga respaldo antes de ejecutarlo.
 */

require __DIR__ . '/../src/Core/Autoloader.php';

use SIG\Core\Database;
use SIG\Models\Producto;

if (!in_array('--si', $argv, true)) {
    fwrite(STDERR, "Esto borra productos y todo el movimiento. Ejecute con --si para confirmar.\n");
    exit(1);
}

$db = Database::getInstance();

$tablasMovimiento = [
    'vb_devoluciones_pagos',
    'vb_devoluciones_detalle',
    'vb_devoluciones',
    'vb_facturas_pagos',
    'vb_detalle_facturas',
    'vb_facturas',
    'vb_ingresos',
    'vb_ingresos_detalle_factura',
    'vb_ingresos_factura_pagos',
    'vb_ingresos_factura',
    'vb_egresos',
    'vb_creditos_abonos',
    'vb_creditos_detalle',
    'vb_creditos',
    'vb_plan_separe_abonos',
    'vb_plan_separe_detalle',
    'vb_plan_separe',
    'vb_base_diaria',
    'vb_cierres_inventario',
    'vb_turnos_caja',
    'vb_lotes',
    'vb_movimientos_inventario',
    'vb_productos_precios_historial',
    'vb_inventario',
    'vb_productos',
];

$db->execute('SET FOREIGN_KEY_CHECKS = 0');
try {
    foreach ($tablasMovimiento as $tabla) {
        $db->execute("DELETE FROM `{$tabla}`");
        echo "Limpiada: {$tabla}\n";
    }
    $db->execute('ALTER TABLE vb_productos AUTO_INCREMENT = 1');
} finally {
    $db->execute('SET FOREIGN_KEY_CHECKS = 1');
}

$iva = (int)($db->fetchOne("SELECT id_iva FROM vb_ivas WHERE iva = 0 LIMIT 1")['id_iva'] ?? 0);
$proveedor = (int)($db->fetchOne("SELECT id_proveedor FROM vb_proveedores ORDER BY id_proveedor LIMIT 1")['id_proveedor'] ?? 0);
$categoria = (int)($db->fetchOne("SELECT id_categoria FROM vb_categorias ORDER BY id_categoria LIMIT 1")['id_categoria'] ?? 0);
$seccion = (int)($db->fetchOne("SELECT id_seccion FROM vb_secciones ORDER BY id_seccion LIMIT 1")['id_seccion'] ?? 0);
$usuario = (int)($db->fetchOne("SELECT id_usuario FROM vb_usuarios WHERE tipo = 0 ORDER BY id_usuario LIMIT 1")['id_usuario'] ?? 1);

$comunes = [
    'id_iva' => $iva ?: null,
    'id_proveedor' => $proveedor ?: null,
    'id_categoria' => $categoria ?: null,
    'id_seccion' => $seccion ?: null,
    'stock_minimo' => 10,
];
// Set mixto de 10 productos de prueba que cubre los tipos de venta:
// unidad simple, caja con unidades sueltas, solo caja, fracción decimal y
// tres niveles (caja -> intermedio -> unidad mínima).
$productos = [
    [
        'descripcion' => 'PRUEBA 1 - Jabón de tocador 110 g',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 1,
        'fraccion' => 0,
        'valor_compra' => 1800,
        'valor_venta' => 3000,
        'valor_unidad' => 3000,
        'stock_minimo' => 5,
        'ingreso' => ['cantidad_unidad' => 24, 'tipo_cantidad' => 'UNIDAD', 'valor_compra' => 1800],
    ],
    [
        'descripcion' => 'PRUEBA 2 - Arroz blanco 500 g',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 1,
        'fraccion' => 0,
        'valor_compra' => 2200,
        'valor_venta' => 3500,
        'valor_unidad' => 3500,
        'stock_minimo' => 10,
        'ingreso' => ['cantidad_unidad' => 40, 'tipo_cantidad' => 'UNIDAD', 'valor_compra' => 2200],
    ],
    [
        'descripcion' => 'PRUEBA 3 - Aceite girasol 1 L',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 1,
        'fraccion' => 0,
        'valor_compra' => 9500,
        'valor_venta' => 14000,
        'valor_unidad' => 14000,
        'stock_minimo' => 6,
        'ingreso' => ['cantidad_unidad' => 18, 'tipo_cantidad' => 'UNIDAD', 'valor_compra' => 9500],
    ],
    [
        'descripcion' => 'PRUEBA 4 - Caja x 12 sobres de café (vender caja o sobre)',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 12,
        'fraccion' => 1,
        'valor_compra' => 900,
        'valor_venta' => 15000,
        'valor_unidad' => 1500,
        'stock_minimo' => 3,
        'ingreso' => ['cantidad_unidad' => 10, 'tipo_cantidad' => 'CAJA', 'valor_compra' => 10800],
    ],
    [
        'descripcion' => 'PRUEBA 5 - Caja x 24 latas de atún (vender caja o lata)',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 24,
        'fraccion' => 1,
        'valor_compra' => 3200,
        'valor_venta' => 96000,
        'valor_unidad' => 4500,
        'stock_minimo' => 2,
        'ingreso' => ['cantidad_unidad' => 5, 'tipo_cantidad' => 'CAJA', 'valor_compra' => 76800],
    ],
    [
        'descripcion' => 'PRUEBA 6 - Solo caja x 12 unidades (detergente en polvo)',
        'tipo_venta' => 'CAJA',
        'unidad_cerrada' => 12,
        'fraccion' => 0,
        'valor_compra' => 4500,
        'valor_venta' => 72000,
        'valor_unidad' => 0,
        'stock_minimo' => 2,
        'ingreso' => ['cantidad_unidad' => 4, 'tipo_cantidad' => 'CAJA', 'valor_compra' => 54000],
    ],
    [
        'descripcion' => 'PRUEBA 7 - Solo caja x 6 paquetes (papel higiénico)',
        'tipo_venta' => 'CAJA',
        'unidad_cerrada' => 6,
        'fraccion' => 0,
        'valor_compra' => 6000,
        'valor_venta' => 48000,
        'valor_unidad' => 0,
        'stock_minimo' => 3,
        'ingreso' => ['cantidad_unidad' => 6, 'tipo_cantidad' => 'CAJA', 'valor_compra' => 36000],
    ],
    [
        'descripcion' => 'PRUEBA 8 - Alcohol por litro (fracción decimal)',
        'tipo_venta' => 'FRACCION_DECIMAL',
        'unidad_medida' => 'LITRO',
        'cantidad_por_unidad' => 1,
        'valor_compra' => 5000,
        'valor_venta' => 8000,
        'stock_minimo' => 5,
        'ingreso' => ['cantidad_unidad' => 20, 'tipo_cantidad' => 'UNIDAD', 'valor_compra' => 5000],
    ],
    [
        'descripcion' => 'PRUEBA 9 - Clavos por kilo (fracción decimal)',
        'tipo_venta' => 'FRACCION_DECIMAL',
        'unidad_medida' => 'KILO',
        'cantidad_por_unidad' => 1,
        'valor_compra' => 4000,
        'valor_venta' => 6500,
        'stock_minimo' => 3,
        'ingreso' => ['cantidad_unidad' => 15, 'tipo_cantidad' => 'UNIDAD', 'valor_compra' => 4000],
    ],
    [
        'descripcion' => 'PRUEBA 10 - Caja x 50 sobres x 10 pastillas (3 niveles)',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 500,
        'fraccion' => 1,
        'nombre_intermedio' => 'Sobre',
        'unidades_intermedias' => 50,
        'unidades_por_intermedio' => 10,
        'valor_intermedio' => 1200,
        'valor_compra' => 80,
        'valor_venta' => 55000,
        'valor_unidad' => 120,
        'stock_minimo' => 1,
        'ingreso' => ['cantidad_unidad' => 30, 'tipo_cantidad' => 'CAJA', 'valor_compra' => 40000],
    ],
];

$productoModel = new Producto();
$ingresoModel = new SIG\Models\Ingreso();

// El ingreso recalcula el costo promedio y el precio de venta según la
// rentabilidad del producto. Para que los ejemplos conserven el precio
// definido, se restaura después de registrar el stock inicial.
$precios = [];

foreach ($productos as $def) {
    $ingreso = $def['ingreso'];
    unset($def['ingreso']);
    $id = $productoModel->crear(array_merge($comunes, $def));
    $precios[$id] = [
        'valor_venta' => (float)($def['valor_venta'] ?? 0),
        'valor_unidad' => (float)($def['valor_unidad'] ?? 0),
        'valor_intermedio' => (float)($def['valor_intermedio'] ?? 0),
    ];
    $ingresoModel->crear($ingreso + ['id_producto' => $id, 'observacion' => 'Stock inicial de ejemplo'], $usuario);
    $stock = $db->fetchOne("SELECT unidad FROM vb_inventario WHERE id_producto = :id", ['id' => $id]);
    echo "Creado #{$id}: {$def['descripcion']} | stock: " . ($stock['unidad'] ?? 0) . " und mínimas\n";
}

foreach ($precios as $id => $precio) {
    $db->execute(
        "UPDATE vb_productos
            SET valor_venta = :venta, valor_unidad = :unidad, valor_intermedio = :intermedio
          WHERE id_producto = :id",
        [
            'venta' => $precio['valor_venta'],
            'unidad' => $precio['valor_unidad'],
            'intermedio' => $precio['valor_intermedio'],
            'id' => $id,
        ]
    );
}

echo "Listo.\n";
