<?php
/**
 * Crea el catálogo de productos de prueba con sus equivalencias correctas.
 *
 * Uso: php scripts/productos-prueba.php
 *
 * Requiere que vb_productos esté vacío (o que no existan los códigos de barras
 * usados aquí). Crea el producto, el registro de inventario y el ingreso
 * inicial de stock.
 */

require __DIR__ . '/../src/Core/Autoloader.php';

use SIG\Core\Database;
use SIG\Models\Producto;
use SIG\Models\Ingreso;

$db = Database::getInstance();

$iva0 = (int)($db->fetchOne("SELECT id_iva FROM vb_ivas WHERE iva = 0 LIMIT 1")['id_iva'] ?? 0);
$usuario = (int)($db->fetchOne("SELECT id_usuario FROM vb_usuarios WHERE tipo = 0 ORDER BY id_usuario LIMIT 1")['id_usuario'] ?? 1);

$comunes = [
    'id_iva' => $iva0 ?: null,
    'id_proveedor' => 6,
    'id_categoria' => 2,
    'id_seccion' => 1,
    'stock_minimo' => 3,
];

/**
 * Presentación principal de cada producto:
 *   - simple:       1 unidad, sin caja
 *   - caja_sueltas: caja con N unidades internas que se venden sueltas
 *   - solo_caja:    caja que solo se vende cerrada
 *   - decimal:      venta por fracción decimal
 *   - tres_niveles: caja -> nivel intermedio -> unidad mínima
 */
$productos = [
    [
        'descripcion' => 'Agua mineral 600 ml',
        'presentacion' => 'Botella 600 ml',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 1,
        'fraccion' => 0,
        'valor_compra' => 1200,
        'valor_venta' => 2500,
        'valor_unidad' => 2500,
        'stock' => 48,
    ],
    [
        'descripcion' => 'Arroz blanco 500 g',
        'presentacion' => 'Bolsa 500 g',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 1,
        'fraccion' => 0,
        'valor_compra' => 2200,
        'valor_venta' => 3500,
        'valor_unidad' => 3500,
        'stock' => 40,
    ],
    [
        'descripcion' => 'Café molido caja x 12 sobres',
        'presentacion' => 'Caja x 12 sobres',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 12,
        'fraccion' => 1,
        'valor_compra' => 900,
        'valor_venta' => 15000,
        'valor_unidad' => 1500,
        'stock' => 120,
    ],
    [
        'descripcion' => 'Atún en lata caja x 24 latas',
        'presentacion' => 'Caja x 24 latas',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 24,
        'fraccion' => 1,
        'valor_compra' => 3200,
        'valor_venta' => 96000,
        'valor_unidad' => 4500,
        'stock' => 120,
    ],
    [
        'descripcion' => 'Detergente en polvo caja x 12 bolsas',
        'presentacion' => 'Caja x 12 bolsas',
        'tipo_venta' => 'CAJA',
        'unidad_cerrada' => 12,
        'fraccion' => 0,
        'valor_compra' => 4500,
        'valor_venta' => 72000,
        'valor_unidad' => 0,
        'stock' => 48,
    ],
    [
        'descripcion' => 'Alcohol 70% por litro',
        'presentacion' => 'X1 LITRO',
        'tipo_venta' => 'FRACCION_DECIMAL',
        'unidad_medida' => 'LITRO',
        'cantidad_por_unidad' => 1,
        'valor_compra' => 5000,
        'valor_venta' => 8000,
        'valor_unidad' => 0,
        'stock' => 20,
    ],
    [
        'descripcion' => 'Clavos por kilo',
        'presentacion' => 'X1 KILO',
        'tipo_venta' => 'FRACCION_DECIMAL',
        'unidad_medida' => 'KILO',
        'cantidad_por_unidad' => 1,
        'valor_compra' => 4000,
        'valor_venta' => 6500,
        'valor_unidad' => 0,
        'stock' => 15,
    ],
    [
        'descripcion' => 'Acetaminofén caja x 50 sobres x 10 pastillas',
        'presentacion' => 'Caja x 50 sobres x 10 pastillas',
        'tipo_venta' => 'UNIDAD',
        'unidad_cerrada' => 500,
        'fraccion' => 1,
        'unidades_intermedias' => 50,
        'unidades_por_intermedio' => 10,
        'nombre_intermedio' => 'Sobre',
        'valor_intermedio' => 1200,
        'valor_compra' => 80,
        'valor_venta' => 55000,
        'valor_unidad' => 120,
        'stock' => 15000,
    ],
];

$productoModel = new Producto();
$ingresoModel = new Ingreso();

// El ingreso recalcula el costo promedio y el precio de venta según la
// rentabilidad. Para que los productos conserven el precio definido, se
// restauran los precios después de registrar el stock inicial.
$precios = [];

foreach ($productos as $def) {
    $stock = $def['stock'];
    unset($def['stock']);

    $id = $productoModel->crear(array_merge($comunes, $def));

    $ingresoModel->crear([
        'id_producto' => $id,
        'cantidad_unidad' => $stock,
        'cantidad_fraccion' => 0,
        'tipo_cantidad' => 'UNIDAD',
        'valor_compra' => $def['valor_compra'],
        'observacion' => 'Stock inicial',
    ], $usuario);

    $precios[$id] = [
        'valor_venta' => (float)($def['valor_venta'] ?? 0),
        'valor_unidad' => (float)($def['valor_unidad'] ?? 0),
        'valor_intermedio' => (float)($def['valor_intermedio'] ?? 0),
    ];

    echo "Creado #{$id}: {$def['descripcion']} | stock: {$stock} und mínimas\n";
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
