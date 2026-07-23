<?php
namespace SIG\Services;

use SIG\Core\Database;

/**
 * Servicio de Facturación Electrónica (DIAN)
 * 
 * Integración con API 
 * Lee configuraciones desde vb_configuraciones (grupo 'fe')
 * PermitE configurar todo desde la UI de administración
 */
class FacturacionElectronicaService
{
    private Database $db;
    private array $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = $this->cargarConfiguracion();
    }

    /**
     * Cargar configuración de FE desde la BD
     */
    private function cargarConfiguracion(): array
    {
        $rows = $this->db->select(
            "SELECT clave, valor FROM vb_configuraciones WHERE grupo = 'fe'"
        );

        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }

        // Valores por defecto
        $defaults = [
            'nit'               => '',
            'software_id'       => '',
            'clave_certificado' => '',
            'llave_envio'       => '',
            'usuario_api'       => '',
            'llave_api'         => '',
            'url_api'           => 'https://www.factin.app:8443/Movimientoapi',
            'url_api_articulos' => 'http://159.65.32.58:5041/Inarticulosapi',
            'prefijo'           => 'SD30',
            'resolucion'        => '',
            'rango_inicio'      => '1',
            'rango_fin'         => '2000',
        ];

        return array_merge($defaults, $config);
    }

    /**
     * Probar conexión con la API
     */
    public function testConexion(): array
    {
        $url = $this->config['url_api'];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "Error de conexión: {$error}", 'code' => 0];
        }

        return [
            'success' => $httpCode < 500,
            'message' => "API respondió con código {$httpCode}",
            'code'    => $httpCode,
        ];
    }

    /**
     * Sincronizar un producto con Factin
     */
    public function sincronizarProducto(int $idProducto): array
    {
        $producto = $this->db->fetchOne(
            "SELECT p.*, i.iva FROM vb_productos p
             LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
             WHERE p.id_producto = :id",
            ['id' => $idProducto]
        );

        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        $url = $this->config['url_api_articulos'] . '?llave=' . $this->config['llave_api'];

        $payload = [
            'codigo' => $producto['codigo'],
            'nombre' => $producto['descripcion'] . ' ' . ($producto['presentacion'] ?? ''),
            'inarticulosbodega' => [
                ['bodega' => '1', 'costo' => (float)$producto['valor_compra'], 'precio' => (float)$producto['valor_venta']]
            ],
            'inarticuloslistaprecio' => [
                ['lista' => '1', 'precio' => (float)$producto['valor_venta']]
            ],
            'inarticuloscompuesto' => [],
            'inarticulosstock' => [],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success'    => $httpCode < 400,
            'http_code'  => $httpCode,
            'response'   => json_decode($response, true) ?? $response,
            'producto'   => $producto['codigo'],
        ];
    }

    /**
     * Enviar factura electrónica a la DIAN vía Factin
     */
    public function enviarFactura(int $idFactura): array
    {
        $factura = $this->db->fetchOne(
            "SELECT f.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento,
                    c.direccion AS cliente_direccion, c.tipo_documento,
                    u.nombre_usuario AS vendedor_nombre
             FROM vb_facturas f
             LEFT JOIN vb_clientes c ON f.id_cliente = c.id_cliente
             LEFT JOIN vb_usuarios u ON f.id_vendedor = u.id_usuario
             WHERE f.id_factura = :id AND f.estado = 'ACTIVA'",
            ['id' => $idFactura]
        );

        if (!$factura) {
            return ['success' => false, 'message' => 'Factura no encontrada o no activa'];
        }

        // Validar cliente (no genérico)
        $doc = trim($factura['cliente_documento'] ?? '');
        $nombre = trim($factura['cliente_nombre'] ?? '');
        if (empty($doc) || strlen(preg_replace('/\D+/', '', $doc)) < 5 || $doc === '12345' ||
            preg_match('/cliente\s+estandar|cliente\s+general|consumidor\s+final/i', $nombre)) {
            return ['success' => false, 'message' => 'Cliente no válido para facturación electrónica. Seleccione un cliente real.'];
        }

        // Validar rango FE
        $codigo = (int)$factura['codigo'];
        if ($codigo < (int)$this->config['rango_inicio'] || $codigo > (int)$this->config['rango_fin']) {
            return ['success' => false, 'message' => "Factura #{$codigo} fuera de rango FE ({$this->config['rango_inicio']}-{$this->config['rango_fin']})"];
        }

        // Obtener detalles
        $detalles = $this->db->select(
            "SELECT d.*, p.codigo_producto, p.descripcion, p.presentacion, i.iva
             FROM vb_detalle_facturas d
             LEFT JOIN vb_productos p ON d.id_producto = p.id_producto
             LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
             WHERE d.id_factura = :id",
            ['id' => $idFactura]
        );

        // Construir payload para Factin
        $items = [];
        $totalIva = 0;
        $totalBruto = 0;

        foreach ($detalles as $idx => $det) {
            $cantidad = $det['cantidad_unidad'] + ($det['cantidad_fraccion'] / max($det['fraccion'] ?? 1, 1));
            $items[] = [
                'codigo'     => $det['codigo_producto'] ?? ('PROD' . $det['id_producto']),
                'nombre'     => $det['descripcion'] . ' ' . ($det['presentacion'] ?? ''),
                'cantidad'   => round($cantidad, 2),
                'valor'      => round((float)$det['precio_unitario'], 0),
                'subtotal'   => round((float)$det['subtotal'], 0),
                'iva'        => (float)$det['iva'],
                'iva_valor'  => round((float)$det['iva_valor'], 0),
            ];
            $totalIva += (float)$det['iva_valor'];
            $totalBruto += (float)$det['subtotal'];
        }

        $url = $this->config['url_api']
             . '?llave=' . urlencode($this->config['llave_api'])
             . '&nuevo=false&bodegg=-&usuario=' . urlencode($this->config['usuario_api'])
             . '&tipocosto=promedio&llaveusuario=' . urlencode($this->config['llave_api']);

        $payload = [
            'consecutivo'        => $codigo,
            'prefijo'            => $this->config['prefijo'],
            'idtercero'          => $doc,
            'tercero'            => $nombre,
            'nit'                => $this->config['nit'],
            'software_id'        => $this->config['software_id'],
            'clave_certificado'  => $this->config['clave_certificado'],
            'llave_envio'        => $this->config['llave_envio'],
            'bruto'              => round($totalBruto, 0),
            'iva'                => round($totalIva, 0),
            'subtotal'           => round($totalBruto, 0),
            'total'              => round((float)$factura['total'], 0),
            'compa'              => '1',
            'fecha'              => $factura['fecha'],
            'tipodoc'            => $factura['tipo_documento'] ?? 'CC',
            'documento'          => $doc,
            'direccion'          => $factura['cliente_direccion'] ?? '',
            'detallesFactura'    => $items,
        ];

        // Log para debug
        $logFile = __DIR__ . '/../../var/logs/fe_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        file_put_contents($logFile, date('H:i:s') . " Enviando factura #{$codigo} a DIAN\n", FILE_APPEND);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        file_put_contents($logFile, date('H:i:s') . " HTTP {$httpCode}: " . substr($response, 0, 500) . "\n", FILE_APPEND);

        if ($error) {
            return ['success' => false, 'message' => "Error de conexión con Factin: {$error}"];
        }

        $data = json_decode($response, true);

        // Procesar respuesta
        $cufe = null;
        $qr   = null;

        if ($data) {
            if (isset($data['movimiento']['faencmovi']['cufe'])) {
                $cufe = $data['movimiento']['faencmovi']['cufe'];
                $qr   = $data['movimiento']['faencmovi']['qr'] ?? null;
            } elseif (isset($data['data']['movimiento']['faencmovi']['cufe'])) {
                $cufe = $data['data']['movimiento']['faencmovi']['cufe'];
                $qr   = $data['data']['movimiento']['faencmovi']['qr'] ?? null;
            } elseif (isset($data['cufe'])) {
                $cufe = $data['cufe'];
                $qr   = $data['qr'] ?? null;
            }
        }

        // Guardar CUFE en la factura
        if ($cufe) {
            $this->db->executeAffected(
                "UPDATE vb_facturas SET cufe = :cufe, qr = :qr WHERE id_factura = :id",
                ['cufe' => $cufe, 'qr' => $qr, 'id' => $idFactura]
            );
        }

        $exito = $httpCode === 200 && ($data['codigo'] ?? '') === '200' || ($data['success'] ?? false);

        return [
            'success'    => $exito || !empty($cufe),
            'http_code'  => $httpCode,
            'cufe'       => $cufe,
            'qr'         => $qr,
            'message'    => $exito ? 'Factura electrónica emitida exitosamente' : ($data['message'] ?? 'Error al emitir factura electrónica'),
            'raw'        => $data,
        ];
    }

    /**
     * Guardar/actualizar configuración de FE
     */
    public function guardarConfiguracion(array $data): void
    {
        foreach ($data as $clave => $valor) {
            // Verificar si existe
            $existe = $this->db->fetchOne(
                "SELECT id_config FROM vb_configuraciones WHERE grupo = 'fe' AND clave = :clave",
                ['clave' => $clave]
            );

            if ($existe) {
                $this->db->executeAffected(
                    "UPDATE vb_configuraciones SET valor = :valor, updated_at = NOW() WHERE grupo = 'fe' AND clave = :clave",
                    ['valor' => (string)$valor, 'clave' => $clave]
                );
            } else {
                $this->db->insert(
                    "INSERT INTO vb_configuraciones (grupo, clave, valor, tipo) VALUES ('fe', :clave, :valor, 'TEXTO')",
                    ['clave' => $clave, 'valor' => (string)$valor]
                );
            }
        }

        // Recargar configuración
        $this->config = $this->cargarConfiguracion();
    }

    /**
     * Obtener configuración actual
     */
    public function getConfiguracion(): array
    {
        return $this->config;
    }
}
