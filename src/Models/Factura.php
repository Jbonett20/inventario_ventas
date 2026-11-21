<?php
namespace SIG\Models;

use SIG\Core\Database;
use SIG\Core\Session;
use DateTimeImmutable;

class Factura
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Obtener el siguiente código de factura según el rango
     */
    public function siguienteCodigo(string $tipo = 'NORMAL'): int
    {
        $rango = $this->db->fetchOne(
            "SELECT inicio, fin FROM vb_rangos_facturacion WHERE tipo = :tipo AND activo = 1 LIMIT 1",
            ['tipo' => $tipo]
        );

        if (!$rango) throw new \RuntimeException("No hay rango activo para facturas {$tipo}");

        $ultimo = $this->db->fetchOne(
            "SELECT MAX(codigo) AS ultimo FROM vb_facturas WHERE tipo = :tipo AND codigo BETWEEN :ini AND :fin",
            ['tipo' => $tipo, 'ini' => $rango['inicio'], 'fin' => $rango['fin']]
        );

        // OJO: $ultimo es un array, por lo que siempre es "verdadero".
        // Hay que comprobar el valor, no el array, para estrenar el rango.
        $ultimoCodigo = $ultimo['ultimo'] ?? null;
        $siguiente = ($ultimoCodigo !== null)
            ? (int)$ultimoCodigo + 1
            : (int)$rango['inicio'];

        if ($siguiente > (int)$rango['fin']) {
            throw new \RuntimeException("Rango de facturación {$tipo} agotado");
        }

        return $siguiente;
    }

    /**
     * Normaliza y valida los pagos de una factura.
     *
     * Acepta pagos fraccionados: varias formas de pago en la misma venta.
     *   $data['pagos'] = [ ['metodo' => 'NEQUI', 'monto' => 10000], ... ]
     *
     * Si no llega el arreglo, se arma un solo pago con la forma de pago de siempre.
     *
     * Reglas:
     *   - La suma debe cubrir el total, salvo que se financie el saldo.
     *   - El cambio sale del efectivo recibido, para que los pagos guardados
     *     sumen exactamente el total y los reportes por método cuadren.
     *
     * @param array<string, mixed> $data
     * @return array{pagos: list<array{metodo: string, monto: float, referencia: string|null}>, recibido: float, cambio: float, resumen: string, saldo_credito: float}
     */
    private function procesarPagos(array $data, float $totalPagar, bool $permitirCredito = false): array
    {
        $pagos = [];
        $brutos = $data['pagos'] ?? null;

        if (is_array($brutos) && count($brutos)) {
            foreach ($brutos as $p) {
                if (!is_array($p)) continue;

                $metodo = strtoupper(trim((string)($p['metodo'] ?? '')));
                $monto  = round((float)($p['monto'] ?? 0), 2);

                if ($metodo === '' || $monto <= 0) continue;

                $ref = isset($p['referencia']) ? trim((string)$p['referencia']) : '';

                $pagos[] = [
                    'metodo'     => mb_substr($metodo, 0, 50),
                    'monto'      => $monto,
                    'referencia' => $ref !== '' ? mb_substr($ref, 0, 100) : null,
                ];
            }
        }

        // Compatibilidad: sin arreglo, se usa la forma de pago única
        if (!$pagos && !$permitirCredito) {
            $metodo = strtoupper(trim((string)($data['tipo_pago'] ?? ''))) ?: 'EFECTIVO';
            $monto  = round((float)($data['pago_recibido'] ?? 0), 2);
            if ($monto <= 0) {
                $monto = round($totalPagar, 2);
            }

            $pagos[] = [
                'metodo'     => mb_substr($metodo, 0, 50),
                'monto'      => $monto,
                'referencia' => null,
            ];
        }

        $recibido = round(array_sum(array_column($pagos, 'monto')), 2);
        $saldoCredito = round(max(0, $totalPagar - min($recibido, $totalPagar)), 2);

        // El cliente tiene que cubrir el total
        if (!$permitirCredito && $totalPagar > 0 && $recibido + 0.01 < $totalPagar) {
            throw new \RuntimeException(
                'Los pagos suman $' . number_format($recibido, 0, ',', '.') .
                ' y la factura es de $' . number_format($totalPagar, 0, ',', '.') .
                '. Faltan $' . number_format($totalPagar - $recibido, 0, ',', '.') . '.'
            );
        }

        $cambio = round(max(0, $recibido - $totalPagar), 2);

        if ($cambio > 0) {
            // El cambio sale del efectivo; si no hay efectivo, del primer pago
            $idx = 0;
            foreach ($pagos as $i => $p) {
                if ($p['metodo'] === 'EFECTIVO') { $idx = $i; break; }
            }

            $nuevoMonto = round($pagos[$idx]['monto'] - $cambio, 2);

            if ($nuevoMonto <= 0) {
                throw new \RuntimeException(
                    'El cambio ($' . number_format($cambio, 0, ',', '.') . ') es mayor que lo recibido en ' .
                    $pagos[$idx]['metodo'] . '. Revise los montos.'
                );
            }

            $pagos[$idx]['monto'] = $nuevoMonto;
        }

        $metodos = array_values(array_unique(array_column($pagos, 'metodo')));
        $resumen = count($metodos) > 1
            ? 'MIXTO'
            : ($metodos[0] ?? ($saldoCredito > 0 ? 'CREDITO' : 'EFECTIVO'));

        return [
            'pagos'          => $pagos,
            'recibido'       => $recibido,
            'cambio'         => $cambio,
            'resumen'        => $resumen,
            'saldo_credito'  => $saldoCredito,
        ];
    }

    /**
     * Crear factura (cabecera + detalle) en transacción
     */

    /**
     * @param array<string, mixed> $data
     * @param list<array<string, mixed>> $detalles
     * @return array<string, mixed>
     */
    public function crear(array $data, array $detalles, int $idVendedor): array
    {
        $this->db->beginTransaction();

        try {
            $idTurno = (new TurnoCaja())->bloquearTurnoAbierto($idVendedor);
            if ($idTurno === null) {
                throw new \RuntimeException('Debes abrir un turno de caja antes de registrar ventas.');
            }

            $ventaCredito = filter_var($data['venta_credito'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || strtoupper(trim((string)($data['tipo_pago'] ?? ''))) === 'CREDITO';
            if ($ventaCredito) {
                $this->validarClienteCredito((int)($data['id_cliente'] ?? 0));
            }
            $fechaVencimiento = $ventaCredito
                ? $this->validarFechaVencimiento($data['fecha_vencimiento'] ?? null)
                : null;

            $tipo = $data['tipo'] ?? 'NORMAL';
            $codigo = $this->siguienteCodigo($tipo);

            // Calcular totales
            $subtotal = 0;
            $totalIva = 0;
            $totalGanancia = 0;

            foreach ($detalles as &$det) {
                $producto = $this->db->fetchOne(
                    "SELECT valor_compra, rentabilidad, tipo_venta, cantidad_por_unidad, unidad_cerrada, fraccion, valor_unidad,
                            unidades_intermedias, unidades_por_intermedio, valor_intermedio
                     FROM vb_productos WHERE id_producto = :id",
                    ['id' => $det['id_producto']]
                );
                if (!$producto) {
                    throw new \RuntimeException('El producto de la venta ya no existe.');
                }

                $tipoVenta = $producto['tipo_venta'] ?? 'UNIDAD';
                $det['tipo_venta'] = $tipoVenta;
                $precioInterno = (float)($producto['valor_unidad'] ?? 0) > 0
                    ? (float)$producto['valor_unidad']
                    : (float)$det['precio_unitario'];
                $det['valor_unidad'] = $precioInterno;

                if ($tipoVenta === 'FRACCION_DECIMAL') {
                    // Venta por fracción decimal: precio * cantidad decimal
                    $cantDec = (float)($det['cantidad_decimal'] ?? 0);
                    if ($cantDec <= 0) {
                        throw new \RuntimeException('La cantidad vendida debe ser mayor que cero.');
                    }
                    $det['cantidad_unidad'] = 0;
                    $det['cantidad_fraccion'] = 0;
                    $det['cantidad_intermedia'] = 0;
                    $det['subtotal'] = $det['precio_unitario'] * $cantDec;
                    $det['ganancia'] = ($det['precio_unitario'] - (float)$producto['valor_compra']) * $cantDec;
                } else {
                    // Cajas a precio de caja, nivel intermedio a su precio y unidades mínimas a su precio
                    $cantUnd = max(0, (int)($det['cantidad_unidad'] ?? 0));
                    $cantFrac = max(0, (int)($det['cantidad_fraccion'] ?? 0));
                    $cantInter = max(0, (int)($det['cantidad_intermedia'] ?? 0));
                    $porCaja = max(1, (int)($producto['unidad_cerrada'] ?? 1));
                    $intermedios = max(0, (int)($producto['unidades_intermedias'] ?? 0));
                    $porIntermedio = max(1, (int)($producto['unidades_por_intermedio'] ?? 1));
                    $precioIntermedio = (float)($producto['valor_intermedio'] ?? 0);
                    $det['cantidad_unidad'] = $cantUnd;
                    $det['cantidad_fraccion'] = $cantFrac;
                    $det['cantidad_intermedia'] = $cantInter;
                    $det['cantidad_decimal'] = 0;

                    if ($cantUnd + $cantFrac + $cantInter <= 0) {
                        throw new \RuntimeException('La cantidad vendida debe ser mayor que cero.');
                    }
                    if ($cantFrac > 0 && (int)($producto['fraccion'] ?? 0) <= 0) {
                        throw new \RuntimeException('El producto no se vende por unidades internas sueltas.');
                    }
                    if ($cantInter > 0 && ($intermedios <= 0 || $precioIntermedio <= 0)) {
                        throw new \RuntimeException('El producto no tiene configurado el nivel intermedio de venta.');
                    }

                    // Costo de cada presentación: la caja y el nivel intermedio agrupan unidades mínimas
                    $costoCaja = (float)$producto['valor_compra'] * $porCaja;
                    $costoIntermedio = (float)$producto['valor_compra'] * $porIntermedio;

                    $det['subtotal'] = ($cantUnd * (float)$det['precio_unitario'])
                        + ($cantInter * $precioIntermedio)
                        + ($cantFrac * $precioInterno);
                    $det['ganancia'] = ($cantUnd * ((float)$det['precio_unitario'] - $costoCaja))
                        + ($cantInter * ($precioIntermedio - $costoIntermedio))
                        + ($cantFrac * ($precioInterno - (float)$producto['valor_compra']));
                }

                // Unidades mínimas que realmente salen del inventario. Vender un
                // sobre de una caja cerrada abre la caja y consume de sus unidades.
                if ($tipoVenta === 'FRACCION_DECIMAL') {
                    $det['unidades_inventario'] = (float)($det['cantidad_decimal'] ?? 0);
                } else {
                    // Todo el inventario se lleva en la unidad mínima:
                    // cajas × unidades por caja + nivel intermedio × unidades por nivel + sueltas.
                    $porCajaInv = max(1, (int)($producto['unidad_cerrada'] ?? 1));
                    $porIntermedioInv = max(1, (int)($producto['unidades_por_intermedio'] ?? 1));
                    $det['unidades_inventario'] = (max(0, (int)($det['cantidad_unidad'] ?? 0)) * $porCajaInv)
                        + (max(0, (int)($det['cantidad_intermedia'] ?? 0)) * $porIntermedioInv)
                        + max(0, (int)($det['cantidad_fraccion'] ?? 0));
                }
                $det['iva_valor'] = $det['subtotal'] * ($det['iva'] / 100);
                $det['total'] = $det['subtotal'] + $det['iva_valor'];

                $subtotal += $det['subtotal'];
                $totalIva += $det['iva_valor'];
                $totalGanancia += $det['ganancia'];
            }
            unset($det);

            $total = $subtotal + $totalIva;
            $descuento = (float)($data['descuento'] ?? 0);
            $totalPagar = $total - $descuento;

            // Pagos fraccionados: el cliente puede pagar con varias formas
            $infoPagos    = $this->procesarPagos($data, $totalPagar, $ventaCredito);
            $pagos        = $infoPagos['pagos'];
            $pagoRecibido = $infoPagos['recibido'];
            $cambio       = $infoPagos['cambio'];
            $saldoCredito = $infoPagos['saldo_credito'];
            $tipoPago     = $saldoCredito > 0 ? 'CREDITO' : $infoPagos['resumen'];

            // Insertar factura
            $idFactura = $this->db->insert(
                "INSERT INTO vb_facturas 
                    (uuid, codigo, id_empresa, id_cliente, id_vendedor, id_vendedor_registrado,
                     id_turno, fecha, hora, tipo, tipo_pago,
                     subtotal, total_iva, total, pago_recibido, cambio, descuento, ganancia, estado)
                 VALUES 
                    (:uuid, :cod, 1, :cli, :vend, :vendreg, :turno,
                     :fecha, :hora, :tipo, :tpago,
                     :sub, :iva, :total, :pago, :cambio, :desc, :gan, 'ACTIVA')",
                [
                    'uuid'  => \SIG\Helpers\Security::generateUUID(),
                    'cod'   => $codigo,
                    'cli'   => $data['id_cliente'] ?? 1,
                    'vend'  => $idVendedor,
                    'vendreg' => !empty($data['id_vendedor_registrado']) ? (int)$data['id_vendedor_registrado'] : null,
                    'turno' => $idTurno,
                    'fecha' => date('Y-m-d'),
                    'hora'  => date('H:i:s'),
                    'tipo'  => $tipo,
                    'tpago' => $tipoPago,
                    'sub'   => $subtotal,
                    'iva'   => $totalIva,
                    'total' => $totalPagar,
                    'pago'  => $pagoRecibido,
                    'cambio'=> $cambio,
                    'desc'  => $descuento,
                    'gan'   => $totalGanancia,
                ]
            );

            // Insertar detalle
            foreach ($detalles as $det) {
                $this->db->insert(
                    "INSERT INTO vb_detalle_facturas
                        (id_factura, id_producto, descripcion, cantidad_unidad, cantidad_fraccion,
                         cantidad_intermedia, precio_unitario, iva, iva_valor, subtotal, total, cantidad_decimal,
                         unidades_inventario)
                     VALUES
                        (:fac, :prod, :desc, :cu, :cf, :ci, :pu, :iva, :ivav, :sub, :tot, :cd, :ui)",
                    [
                        'fac'  => $idFactura,
                        'prod' => $det['id_producto'],
                        'desc' => $det['descripcion'] ?? '',
                        'cu'   => $det['cantidad_unidad'],
                        'cf'   => $det['cantidad_fraccion'] ?? 0,
                        'ci'   => (int)($det['cantidad_intermedia'] ?? 0),
                        'pu'   => $det['precio_unitario'],
                        'iva'  => $det['iva'],
                        'ivav' => $det['iva_valor'],
                        'sub'  => $det['subtotal'],
                        'tot'  => $det['total'],
                        'cd'   => (float)($det['cantidad_decimal'] ?? 0),
                        'ui'   => (float)($det['unidades_inventario'] ?? 0),
                    ]
                );

                // Descontar del inventario
                $inv = $this->db->fetchOne(
                    "SELECT * FROM vb_inventario WHERE id_producto = :id",
                    ['id' => $det['id_producto']]
                );

                if ($inv) {
                    // Obtener configuración de fracción del producto
                    $prodInv = $this->db->fetchOne(
                        "SELECT tipo_venta FROM vb_productos WHERE id_producto = :id",
                        ['id' => $det['id_producto']]
                    );
                    $tipoVentaInv = $prodInv['tipo_venta'] ?? 'UNIDAD';
                    $cantDec = (float)($det['cantidad_decimal'] ?? 0);

                    if ($tipoVentaInv === 'FRACCION_DECIMAL') {
                        // Producto con venta por fracción decimal
                        $nuevaFrac = (float)$inv['fraccion'] - $cantDec;
                        $nuevaUnd = (int)$inv['unidad'];

                        // Si fracción queda negativa, convertir desde unidades
                        if ($nuevaFrac < 0) {
                            $unidadesNecesarias = ceil(abs($nuevaFrac));
                            $nuevaUnd = max(0, $nuevaUnd - $unidadesNecesarias);
                            $nuevaFrac = max(0, $nuevaFrac + $unidadesNecesarias);
                        }

                        $nuevaUnd = max(0, $nuevaUnd);
                        $nuevaFrac = max(0, $nuevaFrac);
                        $movUnd = 0;
                        $movFrac = -$cantDec;
                    } else {
                        // Todo el inventario se lleva en la unidad mínima
                        $totalInternas = (int)($det['unidades_inventario'] ?? 0);
                        if ($totalInternas > (int)$inv['unidad']) {
                            throw new \RuntimeException(
                                'Stock insuficiente: se requieren ' . $totalInternas
                                . ' unidades mínimas y hay ' . (int)$inv['unidad'] . '.'
                            );
                        }
                        $nuevaUnd = (int)$inv['unidad'] - $totalInternas;
                        $nuevaFrac = 0;
                        $movUnd = -$totalInternas;
                        $movFrac = 0;
                    }

                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET unidad = :u, fraccion = :f, ultimo_egreso = NOW() WHERE id_producto = :id",
                        ['u' => $nuevaUnd, 'f' => $nuevaFrac, 'id' => $det['id_producto']]
                    );

                    // Registrar movimiento de egreso
                    $this->db->insert(
                        "INSERT INTO vb_movimientos_inventario
                            (id_producto, tipo, unidad, fraccion, unidad_resultante, fraccion_resultante, id_usuario, created_at)
                         VALUES (:id, 'EGRESO', :du, :df, :ur, :fr, :uid, NOW())",
                        [
                            'id'  => $det['id_producto'],
                            'du'  => $movUnd,
                            'df'  => $movFrac,
                            'ur'  => $nuevaUnd,
                            'fr'  => $nuevaFrac,
                            'uid' => $idVendedor,
                        ]
                    );
                }
            }

            // Detalle de los pagos: uno por cada forma con que pagó el cliente
            foreach ($pagos as $p) {
                $this->db->insert(
                    "INSERT INTO vb_facturas_pagos (id_factura, metodo, monto, referencia, id_usuario, id_turno, created_at)
                     VALUES (:fac, :metodo, :monto, :ref, :uid, :turno, NOW())",
                    [
                        'fac'    => $idFactura,
                        'metodo' => $p['metodo'],
                        'monto'  => $p['monto'],
                        'ref'    => $p['referencia'],
                        'uid'    => $idVendedor,
                        'turno'  => $idTurno,
                    ]
                );
            }

            $idCredito = null;
            if ($saldoCredito > 0) {
                $idCredito = $this->db->insert(
                    "INSERT INTO vb_creditos
                        (codigo, id_cliente, valor_total, valor_aumento, saldo_pendiente, fecha_inicio, fecha_fin, estado)
                     VALUES (:codigo, :id_cliente, :valor_total, 0, :saldo, CURDATE(), :fecha_fin, 'ACTIVO')",
                    [
                        'codigo'      => 'CRE-POS-' . $idFactura,
                        'id_cliente'  => (int)$data['id_cliente'],
                        'valor_total' => $saldoCredito,
                        'saldo'       => $saldoCredito,
                        'fecha_fin'   => $fechaVencimiento,
                    ]
                );
                $this->db->executeAffected(
                    'UPDATE vb_facturas SET id_credito = :credito WHERE id_factura = :factura',
                    ['credito' => $idCredito, 'factura' => $idFactura]
                );
            }

            $this->db->commit();

            return [
                'success'    => true,
                'id_factura' => $idFactura,
                'codigo'     => $codigo,
                'total'      => $totalPagar,
                'cambio'     => $cambio,
                'tipo_pago'  => $tipoPago,
                'pagos'      => $pagos,
                'id_credito' => $idCredito,
                'saldo_credito' => $saldoCredito,
            ];

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    private function validarClienteCredito(int $idCliente): void
    {
        if ($idCliente <= 0) {
            throw new \RuntimeException('Selecciona un cliente activo para registrar una venta a crédito.');
        }

        $cliente = $this->db->fetchOne(
            'SELECT id_cliente, nombre, activo FROM vb_clientes WHERE id_cliente = :id FOR UPDATE',
            ['id' => $idCliente]
        );
        if ($cliente === null) {
            throw new \RuntimeException('El cliente seleccionado no existe.');
        }
        if ((int)$cliente['id_cliente'] === 1 || mb_strtoupper(trim((string)$cliente['nombre']), 'UTF-8') === 'CLIENTE GENERAL') {
            throw new \RuntimeException('Las ventas a crédito requieren un cliente distinto de CLIENTE GENERAL.');
        }
        if ((int)$cliente['activo'] !== 1) {
            throw new \RuntimeException('El cliente seleccionado está inactivo.');
        }
    }

    private function validarFechaVencimiento(mixed $fecha): ?string
    {
        if ($fecha === null || trim((string)$fecha) === '') {
            return null;
        }

        $fecha = trim((string)$fecha);
        $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new \RuntimeException('La fecha de vencimiento no es válida.');
        }

        return $fecha;
    }

    /** @param array<string, mixed> $factura
     *  @return array<string, mixed>
     */
    private function agregarEstadoPago(array $factura): array
    {
        $total = max(0, (float)($factura['total'] ?? 0));
        $estadoOperativo = strtoupper((string)($factura['estado'] ?? ''));
        if ($estadoOperativo === 'ANULADA') {
            $factura['total_pagado'] = 0.0;
            $factura['saldo_pendiente'] = 0.0;
            $factura['estado_pago'] = 'ANULADA';
            $factura['fecha_vencimiento'] = $factura['fecha_vencimiento'] ?? null;
            return $factura;
        }

        if (!empty($factura['id_credito'])) {
            $saldo = round(max(0, (float)($factura['saldo_credito'] ?? 0)), 2);
            $pagado = round(max(0, $total - min($total, $saldo)), 2);
        } else {
            $pagos = $factura['total_pagos_registrados'] ?? null;
            $recibido = $pagos !== null
                ? (float)$pagos
                : (float)($factura['pago_recibido'] ?? 0);
            $pagado = round(min($total, max(0, $recibido)), 2);
            $saldo = round(max(0, $total - $pagado), 2);
        }

        $fechaVencimiento = $factura['fecha_vencimiento'] ?? null;
        $factura['total_pagado'] = $pagado;
        $factura['saldo_pendiente'] = $saldo;
        $factura['estado_pago'] = $saldo <= 0.01
            ? 'PAGADA'
            : ($fechaVencimiento !== null && $fechaVencimiento < date('Y-m-d') ? 'EN MORA' : 'PENDIENTE');

        return $factura;
    }

    /**
     * Obtener factura con detalles (incluye cantidades restantes para devolución)
     */
    /** @return array<string, mixed>|null */
    public function obtenerConDetalle(int $id): ?array
    {
        $factura = $this->db->fetchOne(
            "SELECT f.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    c.direccion AS cliente_direccion, u.nombre_usuario AS vendedor_nombre,
                    e.nombre AS empresa_nombre, e.nit AS empresa_nit,
                    cr.fecha_fin AS fecha_vencimiento, cr.saldo_pendiente AS saldo_credito,
                    (SELECT SUM(pg.monto) FROM vb_facturas_pagos pg WHERE pg.id_factura = f.id_factura)
                        AS total_pagos_registrados
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_creditos cr ON cr.id_credito = f.id_credito
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             LEFT JOIN vb_empresa e ON f.id_empresa = e.id_empresa
             WHERE f.id_factura = :id",
            ['id' => $id]
        );

        if (!$factura) return null;
        $factura = $this->agregarEstadoPago($factura);

        $detalles = $this->db->select(
            "SELECT d.*, p.codigo, p.presentacion, p.fraccion, p.valor_unidad,
                    p.unidades_intermedias, p.unidades_por_intermedio, p.nombre_intermedio, p.valor_intermedio,
                    GREATEST(0, d.cantidad_unidad - IFNULL(
                        (SELECT SUM(dd.cantidad_unidad) FROM vb_devoluciones_detalle dd
                         JOIN vb_devoluciones dev ON dd.id_devolucion = dev.id_devolucion
                         WHERE dev.id_factura = d.id_factura AND dd.id_producto = d.id_producto), 0
                    )) AS restante_unidad,
                    GREATEST(0, d.cantidad_intermedia - IFNULL(
                        (SELECT SUM(dd.cantidad_intermedia) FROM vb_devoluciones_detalle dd
                         JOIN vb_devoluciones dev ON dd.id_devolucion = dev.id_devolucion
                         WHERE dev.id_factura = d.id_factura AND dd.id_producto = d.id_producto), 0
                    )) AS restante_intermedia,
                    GREATEST(0, d.cantidad_fraccion - IFNULL(
                        (SELECT SUM(dd.cantidad_fraccion) FROM vb_devoluciones_detalle dd
                         JOIN vb_devoluciones dev ON dd.id_devolucion = dev.id_devolucion
                         WHERE dev.id_factura = d.id_factura AND dd.id_producto = d.id_producto), 0
                    )) AS restante_fraccion
             FROM vb_detalle_facturas d
             LEFT JOIN vb_productos p ON d.id_producto = p.id_producto
             WHERE d.id_factura = :id
             ORDER BY d.id_detalle ASC",
            ['id' => $id]
        );

        $factura['detalles'] = $detalles;

        // Detalle de pagos: sirve para mostrar el desglose cuando la venta se
        // pagó con varias formas (efectivo + Nequi, por ejemplo)
        $factura['pagos'] = $this->db->select(
            "SELECT id_pago, metodo, monto, referencia, created_at
             FROM vb_facturas_pagos
             WHERE id_factura = :id
             ORDER BY id_pago ASC",
            ['id' => $id]
        );

        return $factura;
    }

    /**
     * Listar facturas con filtros avanzados
     */
    /**
     * @param array<string, mixed> $filtros
     * @return array{data: list<array<string, mixed>>, total: int, page: int, perPage: int, totalPages: float}
     */
    public function listar(array $filtros = []): array
    {
        $page    = (int)($filtros['page'] ?? 1);
        $perPage = (int)($filtros['perPage'] ?? 25);
        $offset  = ($page - 1) * $perPage;

        $where  = 'WHERE 1=1';
        $params = [];
        $saldoPendienteSql = "CASE
            WHEN f.id_credito IS NOT NULL THEN GREATEST(COALESCE(cr.saldo_pendiente, 0), 0)
            ELSE GREATEST(f.total - COALESCE(pagos.total_pagado, f.pago_recibido, 0), 0)
        END";

        // Búsqueda general
        if (!empty($filtros['search'])) {
            $where .= " AND (f.codigo LIKE :s1 OR c.nombre LIKE :s2 OR c.documento LIKE :s3)";
            $params['s1'] = "%{$filtros['search']}%";
            $params['s2'] = "%{$filtros['search']}%";
            $params['s3'] = "%{$filtros['search']}%";
        }

        // Por tipo (NORMAL / ELECTRONICA)
        if (!empty($filtros['tipo'])) {
            $where .= " AND f.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }

        // Por método de pago
        if (!empty($filtros['tipo_pago'])) {
            $where .= " AND f.tipo_pago = :tp";
            $params['tp'] = $filtros['tipo_pago'];
        }

        // Por estado (ACTIVA / ANULADA)
        if (!empty($filtros['estado'])) {
            $where .= " AND f.estado = :est";
            $params['est'] = $filtros['estado'];
        }

        $estadoPago = strtoupper(trim((string)($filtros['estado_pago'] ?? '')));
        if ($estadoPago !== '') {
            if (!in_array($estadoPago, ['PENDIENTE', 'PAGADA', 'EN MORA'], true)) {
                throw new \InvalidArgumentException('El estado de pago seleccionado no es válido.');
            }

            $where .= " AND f.estado IN ('ACTIVA', 'ACTIVO')";
            if ($estadoPago === 'PAGADA') {
                $where .= " AND {$saldoPendienteSql} <= 0.01";
            } elseif ($estadoPago === 'EN MORA') {
                $where .= " AND {$saldoPendienteSql} > 0.01 AND cr.fecha_fin < CURDATE()";
            } else {
                $where .= " AND {$saldoPendienteSql} > 0.01
                            AND (cr.fecha_fin IS NULL OR cr.fecha_fin >= CURDATE())";
            }
        }

        // Por rango de fechas
        if (!empty($filtros['fecha_ini'])) {
            $where .= " AND f.fecha >= :fini";
            $params['fini'] = $filtros['fecha_ini'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $where .= " AND f.fecha <= :ffin";
            $params['ffin'] = $filtros['fecha_fin'];
        }

        // Por código exacto
        if (!empty($filtros['codigo'])) {
            $where .= " AND f.codigo = :cod";
            $params['cod'] = (int)$filtros['codigo'];
        }

        // Por cliente
        if (!empty($filtros['id_cliente'])) {
            $where .= " AND f.id_cliente = :cli";
            $params['cli'] = (int)$filtros['id_cliente'];
        }

        $data = $this->db->select(
            "SELECT f.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    COALESCE(v.nombre, NULLIF(u.nombre_completo, ''), u.nombre_usuario) AS vendedor_nombre,
                    v.codigo AS vendedor_codigo,
                    (v.id_vendedor IS NOT NULL) AS vendedor_es_registrado,
                    (SELECT COUNT(*) FROM vb_devoluciones dv WHERE dv.id_factura = f.id_factura) AS tiene_devoluciones,
                    cr.fecha_fin AS fecha_vencimiento, cr.saldo_pendiente AS saldo_credito,
                    pagos.total_pagado AS total_pagos_registrados
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_creditos cr ON cr.id_credito = f.id_credito
             LEFT JOIN (
                 SELECT id_factura, SUM(monto) AS total_pagado
                 FROM vb_facturas_pagos
                 GROUP BY id_factura
             ) pagos ON pagos.id_factura = f.id_factura
             LEFT JOIN vb_vendedores v ON f.id_vendedor_registrado = v.id_vendedor
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             {$where}
             ORDER BY f.id_factura DESC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
        foreach ($data as &$factura) {
            $factura = $this->agregarEstadoPago($factura);
        }
        unset($factura);

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_creditos cr ON cr.id_credito = f.id_credito
             LEFT JOIN (
                 SELECT id_factura, SUM(monto) AS total_pagado
                 FROM vb_facturas_pagos
                 GROUP BY id_factura
             ) pagos ON pagos.id_factura = f.id_factura
             {$where}",
            $params
        );

        return [
            'data'       => $data,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Anular una factura (devuelve productos al inventario)
     * Para facturas ELECTRONICA se requiere generar Nota Crédito ante la DIAN.
     */
    public function anular(int $id): void
    {
        $factura = $this->db->fetchOne(
            "SELECT * FROM vb_facturas WHERE id_factura = :id AND estado = 'ACTIVA'",
            ['id' => $id]
        );
        if (!$factura) throw new \RuntimeException('Factura no encontrada o ya anulada');

        // Validar factura electrónica
        if ($factura['tipo'] === 'ELECTRONICA') {
            throw new \RuntimeException(
                'Las facturas electrónicas no se pueden anular directamente. ' .
                'Debe generar una Nota Crédito desde el módulo de facturación electrónica.'
            );
        }

        $detalles = $this->db->select(
            "SELECT * FROM vb_detalle_facturas WHERE id_factura = :id", ['id' => $id]
        );

        $this->db->beginTransaction();
        try {
            foreach ($detalles as $det) {
                $cantDec = (float)($det['cantidad_decimal'] ?? 0);
                if ($cantDec > 0) {
                    // Producto con fracción decimal: devolver al stock fraccionado
                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET fraccion = fraccion + :f WHERE id_producto = :id",
                        ['f' => $cantDec, 'id' => $det['id_producto']]
                    );
                } else {
                    $prodInv = $this->db->fetchOne(
                        "SELECT unidad_cerrada, unidades_por_intermedio FROM vb_productos WHERE id_producto = :id",
                        ['id' => $det['id_producto']]
                    );
                    if (isset($det['unidades_inventario'])) {
                        $internas = (int)$det['unidades_inventario'];
                    } else {
                        $porIntermedio = max(1, (int)($prodInv['unidades_por_intermedio'] ?? 1));
                        $internas = ((int)($det['cantidad_unidad'] ?? 0) * max(1, (int)($prodInv['unidad_cerrada'] ?? 1)))
                            + ((int)($det['cantidad_intermedia'] ?? 0) * $porIntermedio)
                            + (int)($det['cantidad_fraccion'] ?? 0);
                    }
                    $this->db->executeAffected(
                        "UPDATE vb_inventario SET unidad = unidad + :u WHERE id_producto = :id",
                        ['u' => $internas, 'id' => $det['id_producto']]
                    );
                }
            }

            $this->db->executeAffected(
                "UPDATE vb_facturas SET estado = 'ANULADA' WHERE id_factura = :id", ['id' => $id]
            );

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
