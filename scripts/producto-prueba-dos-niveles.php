<?php
/**
 * Agrega un producto de prueba de dos niveles: caja o sobre (sin unidad mínima suelta).
 *
 * Uso: php scripts/producto-prueba-dos-niveles.php
 */

require __DIR__ . '/../src/Core/Autoloader.php';

use SIG\Core\Database;
use SIG\Models\Producto;
use SIG\Models\Ingreso;

$db = Database::getInstance();

$iva0 = (int)($db->fetchOne("SELECT id_iva FROM vb_ivas WHERE iva = 0 LIMIT 1")['id_iva'] ?? 0);
$usuario = (int)($db->fetchOne("SELECT id_usuario FROM vb_usuarios WHERE tipo = 0 ORDER BY id_usuario LIMIT 1")['id_usuario'] ?? 1);

$def = [
    'descripcion' => 'PRUEBA 2 niveles - Caja x 20 sobres (vender caja o sobre)',
    'presentacion' => 'Caja x 20 sobres',
    'tipo_venta' => 'UNIDAD',
    'unidad_cerrada' => 20,
    'fraccion' => 1,
    'unidades_intermedias' => 20,
    'unidades_por_intermedio' => 1,
    'nombre_intermedio' => 'Sobre',
    'valor_intermedio' => 1800,
    'valor_compra' => 900,
    'valor_venta' => 30000,
    'valor_unidad' => 1800,
    'stock_minimo' => 2,
    'id_iva' => $iva0 ?: null,
    'id_proveedor' => 6,
    'id_categoria' => 2,
    'id_seccion' => 1,
];

$id = (new Producto())->crear($def);

(new Ingreso())->crear([
    'id_producto' => $id,
    'cantidad_unidad' => 200,
    'cantidad_fraccion' => 0,
    'tipo_cantidad' => 'UNIDAD',
    'valor_compra' => 900,
    'observacion' => 'Stock inicial',
], $usuario);

$db->execute(
    "UPDATE vb_productos SET valor_venta = :venta, valor_intermedio = :inter WHERE id_producto = :id",
    ['venta' => 30000, 'inter' => 1800, 'id' => $id]
);

echo "Creado #{$id}: {$def['descripcion']}\n";
