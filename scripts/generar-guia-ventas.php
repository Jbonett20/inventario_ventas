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

<h2>1. Las cinco formas de venta</h2>
<p>En <span class="tag">Productos</span> cada producto se registra con una <span class="tag">Forma de Venta</span>. Esa forma decide qué campos se muestran, cómo se calcula el precio y cómo se descuenta el inventario. Todo el inventario se lleva en <span class="tag">unidades mínimas</span>: la unidad más pequeña que se puede vender (una lata, un sobre, una pastilla, un litro...).</p>

<table>
    <thead>
        <tr><th style="width:34mm">Forma de Venta</th><th>Cuándo usarla</th><th style="width:32mm">Inventario</th><th style="width:38mm">Cantidades que admite el POS</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Unidad (entera)</strong></td>
            <td>Se vende de a una unidad (una lata, un jabón, un cepillo).</td>
            <td>Unidades mínimas enteras.</td>
            <td>Enteras (columna <em>Und</em>).</td>
        </tr>
        <tr>
            <td><strong>Caja completa</strong></td>
            <td>Se vende únicamente la presentación cerrada completa. No se abren cajas.</td>
            <td>Unidades mínimas enteras.</td>
            <td>Enteras, solo cajas.</td>
        </tr>
        <tr>
            <td><strong>Caja + Unidad</strong></td>
            <td>Se vende la caja completa o sus unidades sueltas (caja de 24 latas y también latas sueltas).</td>
            <td>Unidades mínimas enteras.</td>
            <td>Enteras (cajas y unidades sueltas).</td>
        </tr>
        <tr>
            <td><strong>Caja + Intermedio + Unidad</strong></td>
            <td>Empaque de tres niveles: caja &rarr; sobre &rarr; pastilla. Cada nivel tiene su precio.</td>
            <td>Unidades mínimas enteras.</td>
            <td>Enteras en tres columnas: cajas, sobres y pastillas.</td>
        </tr>
        <tr>
            <td><strong>Fracción decimal</strong></td>
            <td>Se vende por medida: litros, metros, kilos, gramos, etc. Se puede vender una parte, como 0.5 litros.</td>
            <td>Decimal, en la unidad de medida.</td>
            <td>Decimales (0.25, 0.5, 1.75...).</td>
        </tr>
    </tbody>
</table>

<div class="note">
    <strong>Regla de oro:</strong> el precio de la caja es el precio de la presentación cerrada, el precio del nivel intermedio es el precio de un sobre suelto y el precio de la unidad mínima es el precio de una unidad suelta (una pastilla, una lata). Cada nivel se cobra a su propio precio.
</div>

<h2>2. Cómo registrar un producto</h2>
<p>Vaya a <span class="tag">Productos &rarr; Nuevo Producto</span> y complete los datos básicos: descripción, códigos de barras, presentación, marca, proveedor, categoría, IVA, sección, imagen y stock mínimo. El código se asigna automáticamente. Después configure el bloque de venta.</p>

<h3>2.1 Campos del formulario</h3>
<table>
    <thead>
        <tr><th style="width:50mm">Campo</th><th>Qué significa</th></tr>
    </thead>
    <tbody>
        <tr><td><strong>Descripción</strong></td><td>Nombre del producto. Obligatorio.</td></tr>
        <tr><td><strong>Código</strong></td><td>El sistema asigna un consecutivo numérico. No se digita y no se puede repetir.</td></tr>
        <tr><td><strong>Código Barras #1, #2, #3</strong></td><td>Hasta tres códigos de barras para buscar el producto en el POS.</td></tr>
        <tr><td><strong>Presentación</strong></td><td>Texto libre (ej: X30ML). En fracción decimal se genera automáticamente (ej: X1 LITRO).</td></tr>
        <tr><td><strong>Marca, Proveedor, Categoría, IVA, Sección</strong></td><td>Datos de clasificación; el IVA se aplica en la venta.</td></tr>
        <tr><td><strong>Imagen del Producto / URL de imagen</strong></td><td>Foto del producto (PNG, JPG o WEBP, máx 2MB) o una dirección web.</td></tr>
        <tr><td><strong>Forma de Venta</strong></td><td>Unidad (entera), Caja completa, Caja + Unidad, Caja + Intermedio + Unidad o Fracción decimal.</td></tr>
        <tr><td><strong>Unidades por caja</strong></td><td>Cuántas unidades mínimas trae una caja cerrada. Solo en formas con caja; mínimo 2.</td></tr>
        <tr><td><strong>Nombre del nivel intermedio</strong></td><td>Nombre de la presentación intermedia (solo Caja + Intermedio + Unidad). Ej: Sobre.</td></tr>
        <tr><td><strong>Niveles intermedios por caja</strong></td><td>Cuántos niveles intermedios trae la caja.</td></tr>
        <tr><td><strong>Unidades mínimas por nivel</strong></td><td>Cuántas unidades mínimas trae cada nivel intermedio.</td></tr>
        <tr><td><strong>Unidad de Medida</strong></td><td>Solo fracción decimal: litro, metro, kilo, libra, etc.</td></tr>
        <tr><td><strong>Cantidad x Unidad</strong></td><td>Solo fracción decimal: cuánto equivale una unidad de venta (ejemplo: 1 botella = 1 litro).</td></tr>
        <tr><td><strong>Precio Compra</strong></td><td>Costo de una unidad mínima. El costo de la caja se calcula multiplicando.</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>Precio de la presentación principal: la caja si hay caja, o la unidad/medida si no.</td></tr>
        <tr><td><strong>Precio Venta (nivel intermedio)</strong></td><td>Precio de un sobre suelto (solo Caja + Intermedio + Unidad).</td></tr>
        <tr><td><strong>Precio Venta (unidad suelta / unidad mínima)</strong></td><td>Precio de una unidad mínima suelta. Obligatorio si se venden sueltas.</td></tr>
        <tr><td><strong>Stock Mínimo</strong></td><td>Cantidad a partir de la cual el sistema avisa que hay que reabastecer.</td></tr>
        <tr><td><strong>Rentabilidad</strong></td><td>Se calcula sola: (venta &minus; costo de la caja) &divide; venta.</td></tr>
        <tr><td><strong>Precio Máximo Regulado</strong></td><td>Tope legal de venta para medicamentos regulados. Vacío si no aplica.</td></tr>
    </tbody>
</table>

<div class="warn">
    <strong>Validaciones del sistema:</strong> la fracción decimal no se guarda sin unidad de medida ni cantidad mayor que cero. La caja completa exige al menos 2 unidades mínimas. Si la caja se abre, se exige el precio de la unidad suelta (o el del nivel intermedio). El nivel intermedio exige nombre, cantidad de niveles, unidades por nivel y precio. Con nivel intermedio, el campo <em>Unidades por caja</em> se calcula solo (niveles &times; unidades por nivel) y queda bloqueado.
</div>

<div class="pagebreak"></div>

<h2>3. Ejemplo A: caja de 10 sobres (vender caja o vender sobre)</h2>
<p>Es el caso más común en droguería: la caja trae 10 sobres, pero el cliente puede llevarse un solo sobre.</p>

<h3>3.1 Registro del producto</h3>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Descripción</strong></td><td>Jarabe infantil caja x 10 sobres</td></tr>
        <tr><td><strong>Forma de Venta</strong></td><td>Caja + Unidad</td></tr>
        <tr><td><strong>Unidades por caja</strong></td><td>10</td></tr>
        <tr><td><strong>Precio Compra</strong></td><td>$800 (por sobre)</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>$12.000 (caja completa)</td></tr>
        <tr><td><strong>Precio Venta (unidad suelta)</strong></td><td>$1.500 (un sobre)</td></tr>
    </tbody>
</table>
<p class="small">En la lista de productos, la columna Equivalencia mostrará "10 und internas x caja" y la columna P. Und Interna mostrará $1.500.</p>

<h3>3.2 Cómo queda el inventario</h3>
<p>Al registrar un ingreso de 10 cajas, el inventario queda en <strong>100 unidades mínimas</strong> (10 cajas × 10 sobres). El POS muestra ese stock desglosado, por ejemplo "10 caja(s)".</p>

<h3>3.3 Vender un sobre</h3>
<ol>
    <li>Busque el producto y agréguelo al carrito. El primer clic agrega <strong>1 caja</strong>.</li>
    <li>En la fila del carrito, deje <strong>Cant. caja = 0</strong> y escriba <strong>Und = 1</strong> (o use el botón <strong>+</strong> para cajas y el <strong>+</strong> circular para unidades sueltas).</li>
    <li>El sistema cobra <strong>$1.500</strong> y descuenta <strong>1 unidad mínima</strong>. Si la caja estaba cerrada, se abre y quedan 9 unidades disponibles.</li>
</ol>
<table>
    <thead><tr><th>Cant. caja</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">0</td><td class="center">1</td><td>(1 × $1.500)</td><td class="right">$1.500</td><td class="right">99</td></tr></tbody>
</table>

<h3>3.4 Vender la caja completa</h3>
<ol>
    <li>Agregue el producto al carrito.</li>
    <li>Deje <strong>Cant. caja = 1</strong> y <strong>Und = 0</strong>.</li>
    <li>El sistema cobra <strong>$12.000</strong> y descuenta <strong>10 unidades mínimas</strong>.</li>
</ol>
<table>
    <thead><tr><th>Cant. caja</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">1</td><td class="center">0</td><td>(1 × $12.000)</td><td class="right">$12.000</td><td class="right">90</td></tr></tbody>
</table>

<h3>3.5 Vender 2 cajas y 3 sobres</h3>
<p>En la misma fila escriba <strong>Cant. caja = 2</strong> y <strong>Und = 3</strong>. El sistema cobra por presentación y descuenta el equivalente completo.</p>
<table>
    <thead><tr><th>Cant. caja</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Stock después</th></tr></thead>
    <tbody><tr><td class="center">2</td><td class="center">3</td><td>(2 × $12.000) + (3 × $1.500)</td><td class="right">$28.500</td><td class="right">67</td></tr></tbody>
</table>

<div class="ok">
    <strong>Devoluciones:</strong> si el cliente devuelve un sobre, se devuelve $1.500 y regresa 1 unidad mínima. Si devuelve la caja, se devuelven $12.000 y regresan 10 unidades mínimas. El total de la devolución usa el mismo criterio de precios de la venta.
</div>

<div class="pagebreak"></div>

<h2>4. Ejemplo B: producto que solo se vende por caja</h2>
<p>Cuando la caja no se abre (por ejemplo, un producto sellado que no se vende suelto):</p>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Forma de Venta</strong></td><td>Caja completa</td></tr>
        <tr><td><strong>Unidades por caja</strong></td><td>12</td></tr>
        <tr><td><strong>Precio Venta (caja cerrada)</strong></td><td>$36.000</td></tr>
    </tbody>
</table>
<p>En el POS solo se digita el número de cajas. Al vender 1 caja se descuentan 12 unidades mínimas. La columna <em>Und</em> queda bloqueada: el sistema no permite vender unidades sueltas.</p>

<h2>5. Ejemplo C: venta por fracción decimal (0.5 litros)</h2>
<p>Para productos que se venden por medida, como un thinner que se vende por litros:</p>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Descripción</strong></td><td>Thinner acrílico</td></tr>
        <tr><td><strong>Forma de Venta</strong></td><td>Fracción decimal</td></tr>
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
        <tr><td style="width:60mm"><strong>Forma de Venta</strong></td><td>Unidad (entera)</td></tr>
        <tr><td><strong>Precio Venta</strong></td><td>$3.500</td></tr>
    </tbody>
</table>
<p>En el POS se escribe la cantidad en la columna <strong>Und</strong>. Vender 3 unidades cobra $10.500 y descuenta 3 unidades del inventario.</p>

<h2>7. Ejemplo E: caja de 50 sobres de 10 pastillas (tres niveles)</h2>
<p>Para empaques de tres niveles: caja &rarr; sobre &rarr; pastilla. Cada nivel se vende y se cobra a su propio precio.</p>

<h3>7.1 Registro del producto</h3>
<table>
    <tbody>
        <tr><td style="width:60mm"><strong>Descripción</strong></td><td>Acetaminofén caja x 50 sobres x 10 pastillas</td></tr>
        <tr><td><strong>Forma de Venta</strong></td><td>Caja + Intermedio + Unidad</td></tr>
        <tr><td><strong>Nombre del nivel intermedio</strong></td><td>Sobre</td></tr>
        <tr><td><strong>Niveles intermedios por caja</strong></td><td>50</td></tr>
        <tr><td><strong>Unidades mínimas por nivel</strong></td><td>10</td></tr>
        <tr><td><strong>Unidades por caja</strong></td><td>500 (se calcula solo: 50 × 10) y queda bloqueado</td></tr>
        <tr><td><strong>Precio Compra</strong></td><td>$50 (por pastilla)</td></tr>
        <tr><td><strong>Precio Venta (caja)</strong></td><td>$30.000</td></tr>
        <tr><td><strong>Precio Venta (sobre)</strong></td><td>$700</td></tr>
        <tr><td><strong>Precio Venta (unidad mínima)</strong></td><td>$100 (por pastilla)</td></tr>
    </tbody>
</table>

<h3>7.2 Cómo queda el inventario</h3>
<p>El stock se lleva en <strong>unidades mínimas (pastillas)</strong>. Un ingreso de 2 cajas deja <strong>1.000 pastillas</strong> (2 × 500). El POS muestra ese stock desglosado: "2 caja(s)".</p>

<h3>7.3 Vender en el POS</h3>
<p>Al agregar el producto al carrito se crean tres columnas: <strong>Cant. caja</strong>, <strong>Sobre</strong> y <strong>Und</strong> (pastillas). Puede escribir cantidades en las tres al mismo tiempo.</p>
<table>
    <thead><tr><th>Cant. caja</th><th>Sobre</th><th>Und</th><th>Cálculo</th><th class="right">Total</th><th class="right">Pastillas descontadas</th></tr></thead>
    <tbody>
        <tr><td class="center">0</td><td class="center">0</td><td class="center">3</td><td>3 × $100</td><td class="right">$300</td><td class="right">3</td></tr>
        <tr><td class="center">0</td><td class="center">2</td><td class="center">0</td><td>2 × $700</td><td class="right">$1.400</td><td class="right">20</td></tr>
        <tr><td class="center">1</td><td class="center">3</td><td class="center">5</td><td>$30.000 + (3 × $700) + (5 × $100)</td><td class="right">$32.600</td><td class="right">535</td></tr>
    </tbody>
</table>

<div class="warn">
    <strong>Validaciones del nivel intermedio:</strong> el sistema exige el nombre del nivel (Sobre), cuántos niveles trae la caja, cuántas unidades mínimas trae cada nivel y el precio del nivel. Si alguno falta, el producto no se guarda.
</div>

<div class="pagebreak"></div>

<h2>8. Resumen rápido para el cajero</h2>
<table>
    <thead>
        <tr><th style="width:45mm">Lo que el cliente pide</th><th>Qué hacer en el POS</th><th style="width:45mm">Qué cobra</th></tr>
    </thead>
    <tbody>
        <tr><td>Una caja completa</td><td>Cant. caja = 1, Und = 0</td><td>Precio de la caja</td></tr>
        <tr><td>Un sobre suelto</td><td>Cant. caja = 0, Und = 1</td><td>Precio de la unidad suelta</td></tr>
        <tr><td>Dos cajas y tres sobres</td><td>Cant. caja = 2, Und = 3</td><td>2 cajas + 3 unidades sueltas</td></tr>
        <tr><td>Una caja, tres sobres y cinco pastillas</td><td>Cant. caja = 1, Sobre = 3, Und = 5</td><td>Cada nivel a su precio</td></tr>
        <tr><td>Medio litro</td><td>Cantidad = 0.5</td><td>0.5 × precio por litro</td></tr>
        <tr><td>Tres unidades simples</td><td>Und = 3</td><td>3 × precio unitario</td></tr>
    </tbody>
</table>

<h2>9. Preguntas frecuentes</h2>
<h3>¿Qué pasa si la caja trae 10 sobres pero solo quiero vender la caja completa?</h3>
<p>Use la forma de venta <strong>Caja completa</strong> e indique 10 unidades por caja. Así el inventario se descuenta por cajas completas y la columna <em>Und</em> queda bloqueada.</p>

<h3>¿Puedo cambiar la equivalencia después de crear el producto?</h3>
<p>Sí. Al editar el producto puede cambiar la forma de venta, las unidades por caja y los precios de cada nivel. El cambio aplica a las ventas siguientes; las facturas ya registradas conservan sus precios.</p>

<h3>¿Por qué el precio del sobre no es la décima parte del precio de la caja?</h3>
<p>Porque el precio de cada nivel se configura aparte. Muchas veces vender suelto tiene un precio algo mayor al proporcional de la caja; el sistema respeta el precio que usted defina.</p>

<h3>¿Qué unidad se usa para el stock?</h3>
<p>Siempre la unidad mínima. Una caja de 10 sobres equivale a 10 unidades mínimas, una caja de 50 sobres de 10 pastillas equivale a 500 unidades mínimas, y un litro equivale a 1 unidad de medida que se puede fraccionar.</p>

<h3>¿Qué pasa con las devoluciones de productos fraccionados?</h3>
<p>La devolución devuelve el valor correspondiente a lo devuelto y repone exactamente las unidades mínimas: un sobre devuelve 10 pastillas, una caja devuelve 500.</p>

<h3>¿Puedo escribir un código de producto manualmente?</h3>
<p>No. El código es un consecutivo numérico que asigna el sistema y no se puede repetir. Para identificar el producto use los códigos de barras #1, #2 y #3.</p>

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
