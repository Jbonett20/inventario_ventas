<?php

require __DIR__ . '/../vendor/autoload.php';

/**
 * Genera la guía de tipos de venta y registro de productos.
 *
 * Uso: php scripts/generar-guia-ventas.php
 * Salida: docs/guia-tipos-venta-y-registro-de-productos.pdf
 */

$salida = __DIR__ . '/../docs/guia-tipos-venta-y-registro-de-productos.pdf';
$directorio = dirname($salida);
if (!is_dir($directorio)) {
    mkdir($directorio, 0755, true);
}

$html = <<<'HTML'
<html>
<head>
<style>
    body { font-family: sans-serif; font-size: 10.5pt; color: #212529; }
    h1 { font-size: 20pt; color: #4b2e83; margin-bottom: 2mm; }
    h2 { font-size: 14pt; color: #4b2e83; margin-top: 8mm; margin-bottom: 2mm; border-bottom: 0.4mm solid #d6cdea; padding-bottom: 1mm; }
    h3 { font-size: 11.5pt; color: #2c3e50; margin-top: 5mm; margin-bottom: 1.5mm; }
    p { margin: 0 0 2.5mm 0; line-height: 1.45; }
    ul, ol { margin: 0 0 3mm 5mm; padding-left: 4mm; }
    li { margin-bottom: 1.2mm; line-height: 1.4; }
    table { width: 100%; border-collapse: collapse; margin: 2mm 0 4mm 0; font-size: 9.5pt; }
    th { background: #efe9f8; color: #3b2368; text-align: left; padding: 2mm; border: 0.25mm solid #cfc3e6; }
    td { padding: 2mm; border: 0.25mm solid #ddd6ea; vertical-align: top; }
    .right { text-align: right; }
    .center { text-align: center; }
    .note { background: #f6f3fb; border-left: 1.2mm solid #7e57c2; padding: 2.5mm 3mm; margin: 3mm 0; }
    .warn { background: #fff6e5; border-left: 1.2mm solid #e0a106; padding: 2.5mm 3mm; margin: 3mm 0; }
    .ok { background: #eef8f0; border-left: 1.2mm solid #2e7d32; padding: 2.5mm 3mm; margin: 3mm 0; }
    .small { font-size: 9pt; color: #6c757d; }
    .tag { font-weight: bold; color: #4b2e83; }
    .cover { text-align: center; margin-top: 45mm; }
    .cover h1 { font-size: 26pt; }
    .cover p { font-size: 12pt; color: #555; }
    .pagebreak { page-break-before: always; }
</style>
</head>
<body>

<div class="cover">
    <h1>Guía de tipos de venta y registro de productos</h1>
    <p>Cómo registrar un producto y cómo venderlo en el punto de venta (POS)</p>
    <p class="small">Sistema Integral de Gestión &middot; Inventario y Ventas</p>
    <p class="small">Fecha de generación: %FECHA%</p>
</div>

<div class="pagebreak"></div>

<h2>1. Los tres tipos de venta</h2>
<p>En <span class="tag">Productos</span> cada producto se registra con un <span class="tag">Tipo de Venta</span>. Ese tipo decide qué campos se muestran, cómo se calcula el precio y cómo se descuenta el inventario. Todo el inventario se lleva en <span class="tag">unidades internas</span>: la unidad más pequeña que se puede vender.</p>

<table>
    <thead>
        <tr><th style="width:28mm">Tipo</th><th>Cuándo usarlo</th><th style="width:45mm">Inventario</th><th style="width:38mm">Cantidades que admite el POS</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>UNIDAD</strong></td>
            <td>Se vende por unidades completas. Si el producto viene en caja con varias unidades internas (por ejemplo, una caja de 10 sobres), aquí se configura la equivalencia.</td>
            <td>Unidades internas enteras.</td>
            <td>Enteras (cajas y/o unidades internas).</td>
        </tr>
        <tr>
            <td><strong>CAJA</strong></td>
            <td>Se vende únicamente la presentación cerrada completa. No se abren cajas.</td>
            <td>Unidades internas enteras.</td>
            <td>Enteras (cajas).</td>
        </tr>
        <tr>
            <td><strong>FRACCION_DECIMAL</strong></td>
            <td>Se vende por medida: litros, metros, kilos, gramos, etc. Se puede vender una parte, como 0.5 litros.</td>
            <td>Decimal, en la unidad de medida.</td>
            <td>Decimales (0.25, 0.5, 1.75...).</td>
        </tr>
    </tbody>
</table>

<div class="note">
    <strong>Regla de oro:</strong> el precio de la caja es el precio de la presentación cerrada y el precio de la unidad interna es el precio de una unidad suelta. Si la caja trae 10 sobres, el POS cobra el precio del sobre cuando se vende suelto y el precio de la caja cuando se vende completa.
</div>

<h2>2. Cómo registrar un producto</h2>
<p>Vaya a <span class="tag">Productos &rarr; Nuevo Producto</span> y complete los datos básicos: descripción, marca, proveedor, categoría, IVA, sección, imagen y stock mínimo. El código se asigna automáticamente. Después configure el bloque de venta:</p>

<h3>2.1 Campos del bloque de venta</h3>
<table>
    <thead>
        <tr><th style="width:52mm">Campo</th><th>Qué significa</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Tipo de Venta</strong></td><td>UNIDAD, CAJA o FRACCION_DECIMAL.</td></tr>
        <tr><td><strong>Unidades internas por caja</strong></td><td>Cuántas unidades vendibles trae una caja cerrada. Use 1 si el producto no se vende por caja.</td></tr>
        <tr><td><strong>Unidades internas sueltas</strong></td><td>Indique si la caja se puede abrir para vender sus unidades internas una por una.</td></tr>
        <tr><td><strong>Precio Compra</strong></td><td>Costo de una unidad interna.</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>Precio de la presentación principal: la caja si se configura como caja, o la unidad si no hay caja. En fracción decimal, es el precio por la cantidad equivalente (por ejemplo, por litro).</td></tr>
        <tr><td><strong>Precio Unidad Interna</strong></td><td>Precio de una unidad interna suelta. Obligatorio si se venden unidades internas sueltas.</td></tr>
        <tr><td><strong>Unidad de Medida</strong></td><td>Solo para FRACCION_DECIMAL: litro, metro, kilo, libra, etc.</td></tr>
        <tr><td><strong>Cantidad x Unidad</strong></td><td>Solo para FRACCION_DECIMAL: cuánto equivale una unidad de venta (ejemplo: 1 botella = 1 litro).</td></tr>
    </tbody>
</table>

<div class="warn">
    <strong>Validaciones del sistema:</strong> un producto decimal no se guarda sin unidad de medida ni cantidad mayor que cero. Una caja con varias unidades internas que se venden sueltas no se guarda sin el precio de la unidad interna. Al cambiar de tipo, el sistema ajusta la equivalencia para que no queden datos contradictorios.
</div>

<div class="pagebreak"></div>

<h2>3. Ejemplo A: caja de 10 sobres (vender caja o vender sobre)</h2>
<p>Es el caso más común en droguería: la caja trae 10 sobres, pero el cliente puede llevarse un solo sobre.</p>

<h3>3.1 Registro del producto</h3>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Descripción</strong></td><td>Jarabe infantil caja x 10 sobres</td></tr>
        <tr><td><strong>Tipo de Venta</strong></td><td>UNIDAD</td></tr>
        <tr><td><strong>Unidades internas por caja</strong></td><td>10</td></tr>
        <tr><td><strong>Unidades internas sueltas</strong></td><td>Sí, se venden unidades internas sueltas</td></tr>
        <tr><td><strong>Precio Compra</strong></td><td>$800 (por sobre)</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>$12.000 (caja completa)</td></tr>
        <tr><td><strong>Precio Unidad Interna</strong></td><td>$1.500 (un sobre)</td></tr>
    </tbody>
</table>
<p class="small">En la lista de productos, la columna Equivalencia mostrará "10 und internas x caja" y la columna P. Und Interna mostrará $1.500.</p>

<h3>3.2 Cómo queda el inventario</h3>
<p>Al registrar un ingreso de 10 cajas, el inventario queda en <strong>100 unidades internas</strong> (10 cajas × 10 sobres). El POS muestra ese stock como "100 und internas".</p>

<h3>3.3 Vender un sobre</h3>
<ol>
    <li>Busque el producto y agréguelo al carrito. El primer clic agrega <strong>1 caja</strong>.</li>
    <li>En la fila del carrito, deje <strong>Cajas = 0</strong> y escriba <strong>Und = 1</strong> (o use el botón <strong>+</strong> para cajas y el <strong>−</strong> para quitar una unidad).</li>
    <li>El sistema cobra <strong>$1.500</strong> y descuenta <strong>1 unidad interna</strong>. Si la caja estaba cerrada, se abre y quedan 9 unidades disponibles.</li>
</ol>
<table>
    <thead><tr><th>Cajas</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">0</td><td class="center">1</td><td>(1 × $1.500)</td><td class="right">$1.500</td><td class="right">99</td></tr></tbody>
</table>

<h3>3.4 Vender la caja completa</h3>
<ol>
    <li>Agregue el producto al carrito.</li>
    <li>Deje <strong>Cajas = 1</strong> y <strong>Und = 0</strong>.</li>
    <li>El sistema cobra <strong>$12.000</strong> y descuenta <strong>10 unidades internas</strong>.</li>
</ol>
<table>
    <thead><tr><th>Cajas</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">1</td><td class="center">0</td><td>(1 × $12.000)</td><td class="right">$12.000</td><td class="right">90</td></tr></tbody>
</table>

<h3>3.5 Vender 2 cajas y 3 sobres</h3>
<p>En la misma fila escriba <strong>Cajas = 2</strong> y <strong>Und = 3</strong>. El sistema cobra por presentación y descuenta el equivalente completo.</p>
<table>
    <thead><tr><th>Cajas</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">2</td><td class="center">3</td><td>(2 × $12.000) + (3 × $1.500)</td><td class="right">$28.500</td><td class="right">67</td></tr></tbody>
</table>

<div class="ok">
    <strong>Devoluciones:</strong> si el cliente devuelve un sobre, se devuelve $1.500 y regresa 1 unidad interna. Si devuelve la caja, se devuelven $12.000 y regresan 10 unidades internas. El total de la devolución usa el mismo criterio de precios de la venta.
</div>

<div class="pagebreak"></div>

<h2>4. Ejemplo B: producto que solo se vende por caja</h2>
<p>Cuando la caja no se abre (por ejemplo, un producto sellado que no se vende suelto):</p>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Tipo de Venta</strong></td><td>CAJA</td></tr>
        <tr><td><strong>Unidades internas por caja</strong></td><td>12</td></tr>
        <tr><td><strong>Unidades internas sueltas</strong></td><td>No se venden sueltas</td></tr>
        <tr><td><strong>Precio Venta (caja cerrada)</strong></td><td>$36.000</td></tr>
    </tbody>
</table>
<p>En el POS solo se digita el número de cajas. Al vender 1 caja se descuentan 12 unidades internas. Si se intenta vender una unidad interna suelta, el sistema no lo permite.</p>

<h2>5. Ejemplo C: venta por fracción decimal (0.5 litros)</h2>
<p>Para productos que se venden por medida, como un thinner que se vende por litros:</p>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Descripción</strong></td><td>Thinner acrílico</td></tr>
        <tr><td><strong>Tipo de Venta</strong></td><td>FRACCION_DECIMAL</td></tr>
        <tr><td><strong>Unidad de Medida</strong></td><td>LITRO</td></tr>
        <tr><td><strong>Cantidad x Unidad</strong></td><td>1</td></tr>
        <tr><td><strong>Precio Venta (litro)</strong></td><td>$9.000</td></tr>
    </tbody>
</table>
<p class="small">La presentación se genera automáticamente como "X1 LITRO".</p>

<h3>5.1 Vender 0.5 litros</h3>
<ol>
    <li>Agregue el producto al carrito.</li>
    <li>En la columna de cantidad escriba <strong>0.5</strong>.</li>
    <li>El sistema cobra <strong>$4.500</strong> y descuenta <strong>0.5 litros</strong> del inventario.</li>
</ol>
<table>
    <thead><tr><th>Cantidad</th><th>Unidad</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">0.50</td><td>LITRO</td><td>0.5 × $9.000</td><td class="right">$4.500</td><td class="right">4.50</td></tr></tbody>
</table>

<div class="note">
    El POS no permite vender una cantidad mayor al stock disponible. Si el stock es 4.50 litros y se intenta vender 5, el sistema rechaza la operación y no registra factura ni movimiento de inventario.
</div>

<h2>6. Ejemplo D: producto simple por unidad</h2>
<p>Para un producto que no tiene caja ni se vende por medida (por ejemplo, un cepillo de dientes):</p>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Tipo de Venta</strong></td><td>UNIDAD</td></tr>
        <tr><td><strong>Unidades internas por caja</strong></td><td>1</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>$3.500</td></tr>
    </tbody>
</table>
<p>En el POS se escribe la cantidad en la columna <strong>Und</strong>. Vender 3 unidades cobra $10.500 y descuenta 3 unidades del inventario.</p>

<div class="pagebreak"></div>

<h2>7. Resumen rápido para el cajero</h2>
<table>
    <thead>
        <tr><th style="width:45mm">Lo que el cliente pide</th><th>Qué hacer en el POS</th><th style="width:45mm">Qué cobra</th></tr>
    </thead>
    <tbody>
        <tr><td>Una caja completa</td><td>Cajas = 1, Und = 0</td><td>Precio de la caja</td></tr>
        <tr><td>Un sobre suelto</td><td>Cajas = 0, Und = 1</td><td>Precio de la unidad interna</td></tr>
        <tr><td>Dos cajas y tres sobres</td><td>Cajas = 2, Und = 3</td><td>2 cajas + 3 unidades internas</td></tr>
        <tr><td>Medio litro</td><td>Cantidad = 0.5</td><td>0.5 × precio por litro</td></tr>
        <tr><td>Tres unidades simples</td><td>Und = 3</td><td>3 × precio unitario</td></tr>
    </tbody>
</table>

<h2>8. Preguntas frecuentes</h2>
<h3>¿Qué pasa si la caja trae 10 sobres pero solo quiero vender la caja completa?</h3>
<p>Use tipo CAJA, configure 10 unidades internas por caja y deje "Unidades internas sueltas" en "No se venden sueltas". Así el inventario se descuenta por cajas completas.</p>

<h3>¿Puedo cambiar la equivalencia después de crear el producto?</h3>
<p>Sí. Al editar el producto puede ajustar las unidades internas por caja y el precio de la unidad interna. El cambio aplica a las ventas siguientes; las facturas ya registradas conservan sus precios.</p>

<h3>¿Por qué el precio del sobre no es la décima parte del precio de la caja?</h3>
<p>Porque el precio de la unidad interna se configura aparte. Muchas veces vender suelto tiene un precio algo mayor al proporcional de la caja; el sistema respeta el precio que usted defina.</p>

<h3>¿Qué unidad se usa para el stock?</h3>
<p>Siempre la unidad interna. Una caja de 10 sobres equivale a 10 unidades internas, y un litro equivale a 1 unidad de medida que se puede fraccionar.</p>

<h3>¿Qué pasa con las devoluciones de productos fraccionados?</h3>
<p>La devolución devuelve el valor correspondiente a lo devuelto y repone exactamente las unidades internas: un sobre devuelve 1 unidad interna, una caja devuelve 10.</p>

<p class="small" style="margin-top:10mm">Guía generada automáticamente por el sistema. Los precios de los ejemplos son ilustrativos; use los precios de su negocio al registrar cada producto.</p>

</body>
</html>
HTML;

$html = str_replace('%FECHA%', date('d/m/Y'), $html);

$mpdf = new \Mpdf\Mpdf([
    'mode'          => 'utf-8',
    'format'        => 'A4',
    'margin_left'   => 15,
    'margin_right'  => 15,
    'margin_top'    => 15,
    'margin_bottom' => 15,
    'tempDir'       => sys_get_temp_dir() . '/mpdf',
]);
$mpdf->SetTitle('Guía de tipos de venta y registro de productos');
$mpdf->SetAuthor('Sistema Integral de Gestión');
$mpdf->WriteHTML($html);
$mpdf->Output($salida, \Mpdf\Output\Destination::FILE);

echo 'PDF generado: ' . $salida . PHP_EOL;
