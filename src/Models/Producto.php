<?php
namespace SIG\Models;

use SIG\Core\Database;

/**
 * Modelo de Productos
 */
class Producto
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar todos los productos (con paginación)
     *
     * @param string $estado ACTIVOS (por defecto), INACTIVOS o TODOS
     */
    /** @return array<string, mixed> */
    public function listar(int $page = 1, int $perPage = 25, string $search = '', string $estado = 'ACTIVOS'): array
    {
        $offset = ($page - 1) * $perPage;

        $where = $this->filtroEstado($estado);
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.codigo LIKE :search 
                          OR p.descripcion LIKE :search2 
                          OR p.codigo_barras_1 LIKE :search3
                          OR p.codigo_barras_2 LIKE :search4
                          OR p.codigo_barras_3 LIKE :search5)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
            $params['search5'] = "%{$search}%";
        }

        $sql = "SELECT p.*, prov.nombre AS proveedor_nombre, 
                       cat.nombre AS categoria_nombre, i.iva AS iva_valor
                FROM vb_productos p
                LEFT JOIN vb_proveedores prov ON p.id_proveedor = prov.id_proveedor
                LEFT JOIN vb_categorias cat ON p.id_categoria = cat.id_categoria
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                {$where}
                ORDER BY p.descripcion ASC
                LIMIT :limit OFFSET :offset";

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $productos = $this->db->select($sql, $params);

        // Total de registros
        $countSql = "SELECT COUNT(*) as total FROM vb_productos p {$where}";
        $totalParams = $params;
        unset($totalParams['limit'], $totalParams['offset']);
        $total = $this->db->fetchOne($countSql, $totalParams);

        return [
            'data'       => $productos,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil(($total['total'] ?? 0) / $perPage),
        ];
    }

    /**
     * Condición SQL del filtro de estado del producto.
     * Cualquier valor distinto de INACTIVOS/TODOS se trata como ACTIVOS.
     */
    private function filtroEstado(string $estado): string
    {
        $estado = strtoupper(trim($estado));

        if ($estado === 'INACTIVOS') {
            return 'WHERE p.activo = 0';
        }
        if ($estado === 'TODOS') {
            return 'WHERE 1 = 1';
        }

        return 'WHERE p.activo = 1';
    }

    /**
     * Listar productos para POS (con stock e inventario incluido)
     */
    /** @return array<string, mixed> */
    public function listarPos(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE p.activo = 1';
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.codigo LIKE :search 
                          OR p.descripcion LIKE :search2 
                          OR p.codigo_barras_1 LIKE :search3
                          OR p.codigo_barras_2 LIKE :search4
                          OR p.codigo_barras_3 LIKE :search5)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
            $params['search5'] = "%{$search}%";
        }

        $sql = "SELECT p.id_producto, p.codigo, p.descripcion, p.presentacion, 
                       p.valor_venta, p.valor_unidad, p.id_iva,
                       p.stock_minimo, p.fraccion, p.unidad_cerrada,
                       p.unidades_intermedias, p.unidades_por_intermedio, p.nombre_intermedio, p.valor_intermedio,
                       p.tipo_venta, p.unidad_medida, p.cantidad_por_unidad,
                       i.iva AS iva_porcentaje,
                       inv.unidad AS stock_unidad, inv.fraccion AS stock_fraccion,
                       CASE WHEN p.tipo_venta = 'FRACCION_DECIMAL'
                            THEN COALESCE(inv.fraccion, 0) + COALESCE(inv.unidad, 0)
                            ELSE COALESCE(inv.unidad, 0) END AS stock_vendible,
                       CASE WHEN COALESCE(inv.unidad, 0) > 0 THEN 'disponible' ELSE 'agotado' END AS estado_stock
                FROM vb_productos p
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
                {$where}
                ORDER BY p.descripcion ASC
                LIMIT :limit OFFSET :offset";

        $params['limit']  = $perPage;
        $params['offset'] = $offset;

        $productos = $this->db->select($sql, $params);

        $countSql = "SELECT COUNT(*) as total FROM vb_productos p {$where}";
        $totalParams = $params;
        unset($totalParams['limit'], $totalParams['offset']);
        $total = $this->db->fetchOne($countSql, $totalParams);

        return [
            'data'       => $productos,
            'total'      => (int)($total['total'] ?? 0),
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => max(1, ceil(($total['total'] ?? 0) / $perPage)),
        ];
    }

    /**
     * Buscar productos para autocomplete (facturación)
     */
    /** @return list<array<string, mixed>> */
    public function buscar(string $query): array
    {
        $sql = "SELECT p.*, i.iva AS iva_valor,
                       inv.unidad, inv.fraccion AS stock_fraccion,
                       CASE WHEN p.tipo_venta = 'FRACCION_DECIMAL'
                            THEN COALESCE(inv.fraccion, 0) + COALESCE(inv.unidad, 0)
                            ELSE COALESCE(inv.unidad, 0) END AS stock_vendible,
                       p.tipo_venta, p.unidad_medida, p.cantidad_por_unidad,
                       p.unidades_intermedias, p.unidades_por_intermedio, p.nombre_intermedio, p.valor_intermedio
                FROM vb_productos p
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                LEFT JOIN vb_inventario inv ON p.id_producto = inv.id_producto
                WHERE p.activo = 1
                  AND (p.codigo LIKE :q1 OR p.descripcion LIKE :q2 
                       OR p.codigo_barras_1 LIKE :q3 OR p.codigo_barras_2 LIKE :q4
                       OR p.codigo_barras_3 LIKE :q5)
                ORDER BY p.descripcion ASC
                LIMIT 20";

        $params = [
            'q1' => "%{$query}%",
            'q2' => "%{$query}%",
            'q3' => "%{$query}%",
            'q4' => "%{$query}%",
            'q5' => "%{$query}%",
        ];

        return $this->db->select($sql, $params);
    }

    /**
     * Obtener un producto por ID
     */
    /** @return array<string, mixed>|null */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT p.*, prov.nombre AS proveedor_nombre,
                       cat.nombre AS categoria_nombre, i.iva AS iva_valor
                FROM vb_productos p
                LEFT JOIN vb_proveedores prov ON p.id_proveedor = prov.id_proveedor
                LEFT JOIN vb_categorias cat ON p.id_categoria = cat.id_categoria
                LEFT JOIN vb_ivas i ON p.id_iva = i.id_iva
                WHERE p.id_producto = :id";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Generar presentación automática para productos de fracción decimal
     *
     * @param array<string, mixed> $data
     */
    private function generarPresentacion(array $data): string
    {
        $tipoVenta = $data['tipo_venta'] ?? 'UNIDAD';
        $presentacion = $data['presentacion'] ?? '';

        if ($tipoVenta === 'FRACCION_DECIMAL' && empty($presentacion)) {
            $umedida = $data['unidad_medida'] ?? '';
            $cant = (float)($data['cantidad_por_unidad'] ?? 1);
            if (!empty($umedida)) {
                return 'X' . rtrim(rtrim(number_format($cant, 4, '.', ''), '0'), '.') . ' ' . $umedida;
            }
        }

        return $presentacion;
    }

    /**
     * Crear un nuevo producto
     */
    /**
     * Siguiente código numérico disponible.
     * Se usa un consecutivo largo (1000001, 1000002, ...) para que no se
     * agote nunca y no choque con códigos de barras ni claves escritas a mano.
     */
    public function siguienteCodigo(): int
    {
        $r = $this->db->fetchOne(
            "SELECT COALESCE(MAX(codigo), 1000000) + 1 AS siguiente FROM vb_productos"
        );

        return (int)($r['siguiente'] ?? 1000001);
    }

    /**
     * Resuelve el código de un producto.
     * - Si viene un código válido (solo dígitos) y está libre, se respeta.
     * - En cualquier otro caso se genera el siguiente consecutivo.
     */
    private function resolverCodigo(mixed $codigo, int $idExcluir = 0): int
    {
        $codigo = is_string($codigo) ? trim($codigo) : $codigo;

        if ($codigo !== null && $codigo !== '' && ctype_digit((string)$codigo) && (int)$codigo > 0) {
            $sql = "SELECT id_producto FROM vb_productos WHERE codigo = :c";
            $params = ['c' => (int)$codigo];

            if ($idExcluir > 0) {
                $sql .= " AND id_producto <> :id";
                $params['id'] = $idExcluir;
            }

            if ($this->db->fetchOne($sql, $params)) {
                throw new \RuntimeException('El código ' . (int)$codigo . ' ya está asignado a otro producto.');
            }

            return (int)$codigo;
        }

        return $this->siguienteCodigo();
    }

    /**
     * Normaliza y valida el tipo de venta y la equivalencia de empaque.
     *
     * Reglas:
     *   - FRACCION_DECIMAL requiere unidad de medida y cantidad equivalente.
     *   - Una caja con varias unidades internas debe indicar el precio de la
     *     unidad interna si esas unidades se venden sueltas.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $antes Producto actual (vacío al crear)
     * @return array<string, mixed>
     */
    private function normalizarEquivalencia(array $data, array $antes = []): array
    {
        $tiposValidos = ['UNIDAD', 'CAJA', 'FRACCION_DECIMAL', 'CAJA_UNIDAD', 'CAJA_INTERMEDIO'];
        $tipoVenta = strtoupper(trim((string)($data['tipo_venta'] ?? $antes['tipo_venta'] ?? 'UNIDAD')));
        if (!in_array($tipoVenta, $tiposValidos, true)) {
            throw new \RuntimeException('La forma de venta no es válida.');
        }

        // Las formas de venta se traducen a las 3 configuraciones internas:
        // CAJA_UNIDAD / CAJA_INTERMEDIO se guardan como UNIDAD con caja y fracción.
        if ($tipoVenta === 'CAJA_UNIDAD' || $tipoVenta === 'CAJA_INTERMEDIO') {
            $data['fraccion'] = 1;
            $tipoVenta = 'UNIDAD';
        } elseif ($tipoVenta === 'CAJA') {
            $data['fraccion'] = 0;
        } elseif ($tipoVenta === 'UNIDAD' && !array_key_exists('fraccion', $data)) {
            $data['fraccion'] = (int)($antes['fraccion'] ?? 0);
        }
        $data['tipo_venta'] = $tipoVenta;

        if ($tipoVenta === 'FRACCION_DECIMAL') {
            $unidadMedida = trim((string)($data['unidad_medida'] ?? $antes['unidad_medida'] ?? ''));
            if ($unidadMedida === '') {
                throw new \RuntimeException('Indique la unidad de medida del producto (litro, metro, kilo...).');
            }
            $cantidad = (float)($data['cantidad_por_unidad'] ?? $antes['cantidad_por_unidad'] ?? 1);
            if ($cantidad <= 0) {
                throw new \RuntimeException('La cantidad por unidad debe ser mayor que cero.');
            }
            $data['unidad_medida'] = $unidadMedida;
            $data['cantidad_por_unidad'] = $cantidad;
            $data['unidad_cerrada'] = 1;
            $data['fraccion'] = 0;

            return $data;
        }

        $porCaja = (int)($data['unidad_cerrada'] ?? $antes['unidad_cerrada'] ?? 1);
        if ($porCaja < 1) {
            throw new \RuntimeException('Una caja debe contener al menos una unidad interna.');
        }
        // "Caja completa" exige más de una unidad: con 1 el producto se vendería
        // como unidad suelta y el POS no mostraría la caja.
        if ($tipoVenta === 'CAJA' && $porCaja < 2) {
            throw new \RuntimeException('Para vender por caja completa, indique cuántas unidades internas trae la caja (mínimo 2). Si no se vende por caja, use el tipo de venta "Unidad (entera)".');
        }
        $sueltas = (int)($data['fraccion'] ?? $antes['fraccion'] ?? 0) > 0;

        // Se valida con el nivel intermedio ya normalizado: si la caja trae
        // sobres, la unidad que se vende suelta es el sobre, no la pastilla.
        $data = $this->normalizarNivelIntermedio($data);
        $sueltas = (int)$data['fraccion'] > 0;

        // Si la caja no se abre, el producto no vende nivel intermedio ni
        // unidades sueltas: se limpian los datos viejos para que la edición
        // no muestre ni cobre una forma de venta que ya no aplica.
        if (!$sueltas) {
            $data['unidades_intermedias'] = 0;
            $data['unidades_por_intermedio'] = 1;
            $data['nombre_intermedio'] = null;
            $data['valor_intermedio'] = 0;
        }

        if ($porCaja > 1 && $sueltas && (int)$data['unidades_intermedias'] === 0) {
            $precioInterno = $data['valor_unidad'] ?? $antes['valor_unidad'] ?? 0;
            if ((float)$precioInterno <= 0) {
                throw new \RuntimeException('Indique el precio de la unidad interna para vender unidades sueltas de la caja.');
            }
            $data['valor_unidad'] = (float)$precioInterno;
        }

        // Si no se venden sueltas, el precio de la unidad interna no aplica
        if (!$sueltas) {
            $data['valor_unidad'] = 0;
        }

        $data['unidad_cerrada'] = $porCaja;
        $data['fraccion'] = $sueltas ? 1 : 0;

        // `cantidad_por_unidad` es la equivalencia de la caja en unidades internas.
        // El POS la usa para saber si el producto se vende por caja.
        $data['cantidad_por_unidad'] = $porCaja;

        return $data;
    }

    /**
     * Nivel intermedio opcional dentro de la caja (caja -> sobre -> pastilla).
     * Con unidades_intermedias = 0 el producto conserva el comportamiento de
     * dos niveles (caja -> unidad interna).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizarNivelIntermedio(array $data): array
    {
        $intermedios = (int)($data['unidades_intermedias'] ?? 0);
        if ($intermedios < 0) {
            throw new \RuntimeException('La cantidad del nivel intermedio no puede ser negativa.');
        }
        if ($intermedios === 0) {
            $data['unidades_intermedias'] = 0;
            $data['unidades_por_intermedio'] = 1;
            $data['nombre_intermedio'] = null;
            $data['valor_intermedio'] = 0;
            return $data;
        }

        $porIntermedio = (int)($data['unidades_por_intermedio'] ?? 0);
        if ($porIntermedio < 1) {
            throw new \RuntimeException('Indique cuántas unidades trae cada nivel intermedio (ej: pastillas por sobre).');
        }
        $nombre = trim((string)($data['nombre_intermedio'] ?? ''));
        if ($nombre === '') {
            throw new \RuntimeException('Indique el nombre del nivel intermedio (ej: sobre, ampolla).');
        }
        $precioIntermedio = (float)($data['valor_intermedio'] ?? 0);
        if ($precioIntermedio <= 0) {
            throw new \RuntimeException('Indique el precio del nivel intermedio (ej: precio del sobre).');
        }

        $data['unidades_intermedias'] = $intermedios;
        $data['unidades_por_intermedio'] = $porIntermedio;
        $data['nombre_intermedio'] = $nombre;
        $data['valor_intermedio'] = $precioIntermedio;
        // Con nivel intermedio la caja siempre se puede abrir y vender por
        // niveles (sobres). La unidad mínima suelta (pastilla) es opcional y
        // solo se vende si el usuario también marcó "unidades internas sueltas".
        $data['fraccion'] = (int)($data['fraccion'] ?? 0) > 0 ? 1 : 0;

        return $data;
    }

    /**
     * Normaliza el estado del producto: 1 = activo, 0 = inactivo.
     */
    private function normalizarActivo(mixed $valor): int
    {
        if (is_string($valor)) {
            $valor = strtoupper(trim($valor));
            if (in_array($valor, ['0', '', 'INACTIVO', 'INACTIVA', 'FALSE', 'NO'], true)) {
                return 0;
            }
            if (in_array($valor, ['1', 'ACTIVO', 'ACTIVA', 'TRUE', 'SI'], true)) {
                return 1;
            }
        }

        return ((int)$valor) === 0 ? 0 : 1;
    }

    /** @param array<string, mixed> $data */
    public function crear(array $data): int
    {
        $data = $this->normalizarEquivalencia($data);
        $data['presentacion'] = $this->generarPresentacion($data);

        $params = [
            'barras1'            => $data['codigo_barras_1'] ?? '',
            'barras2'            => $data['codigo_barras_2'] ?? '',
            'barras3'            => $data['codigo_barras_3'] ?? '',
            'descripcion'        => $data['descripcion'],
            'presentacion'       => $data['presentacion'],
            'marca'              => $data['marca'] ?? '',
            'proveedor'          => ($data['id_proveedor'] ?? null) ?: null,
            'categoria'          => ($data['id_categoria'] ?? null) ?: null,
            'iva'                => ($data['id_iva'] ?? null) ?: null,
            'seccion'            => ($data['id_seccion'] ?? null) ?: null,
            'unidad_cerrada'     => (int)($data['unidad_cerrada'] ?? 1),
            'fraccion'           => (int)($data['fraccion'] ?? 0),
            'unidades_intermedias' => (int)($data['unidades_intermedias'] ?? 0),
            'unidades_por_intermedio' => (int)($data['unidades_por_intermedio'] ?? 1),
            'nombre_intermedio'  => $data['nombre_intermedio'] ?? null,
            'valor_intermedio'   => (float)($data['valor_intermedio'] ?? 0),
            'compra'             => $data['valor_compra'] ?? 0,
            'venta'              => $data['valor_venta'] ?? 0,
            'unidad'             => $data['valor_unidad'] ?? 0,
            'tope'               => (isset($data['precio_maximo_regulado']) && $data['precio_maximo_regulado'] !== '' && $data['precio_maximo_regulado'] !== null)
                                        ? (float)$data['precio_maximo_regulado'] : null,
            'rentabilidad'       => $this->calcularRentabilidad(
                                        (float)($data['valor_compra'] ?? 0),
                                        (float)($data['valor_venta'] ?? 0),
                                        (int)($data['unidad_cerrada'] ?? 1)
                                    ),
            'stock_minimo'       => (int)($data['stock_minimo'] ?? 1),
            'imagen'             => $data['imagen'] ?? '',
            'tipo_venta'         => $data['tipo_venta'] ?? 'UNIDAD',
            'unidad_medida'      => $data['unidad_medida'] ?? '',
            'cantidad_por_unidad'=> (float)($data['cantidad_por_unidad'] ?? 1.0000),
            'activo'             => $this->normalizarActivo($data['activo'] ?? 1),
        ];

        $sql = "INSERT INTO vb_productos 
                    (codigo, codigo_barras_1, codigo_barras_2, codigo_barras_3,
                     descripcion, presentacion, marca, id_proveedor, id_categoria,
                     id_iva, id_seccion, unidad_cerrada, fraccion,
                     unidades_intermedias, unidades_por_intermedio, nombre_intermedio, valor_intermedio,
                     valor_compra, valor_venta, valor_unidad, precio_maximo_regulado, rentabilidad, stock_minimo, imagen,
                     tipo_venta, unidad_medida, cantidad_por_unidad, activo)
                 VALUES 
                    (:codigo, :barras1, :barras2, :barras3,
                     :descripcion, :presentacion, :marca, :proveedor, :categoria,
                     :iva, :seccion, :unidad_cerrada, :fraccion,
                     :unidades_intermedias, :unidades_por_intermedio, :nombre_intermedio, :valor_intermedio,
                     :compra, :venta, :unidad, :tope, :rentabilidad, :stock_minimo, :imagen,
                     :tipo_venta, :unidad_medida, :cantidad_por_unidad, :activo)";

        // El código lo asigna el sistema. Si dos productos se crean al mismo
        // tiempo y el consecutivo se cruza, se recalcula y se reintenta.
        $productoId = null;
        for ($intento = 1; $intento <= 3; $intento++) {
            try {
                $params['codigo'] = ($intento === 1)
                    ? $this->resolverCodigo($data['codigo'] ?? null)
                    : $this->siguienteCodigo();

                $productoId = $this->db->insert($sql, $params);
                break;
            } catch (\RuntimeException $e) {
                throw $e; // código repetido enviado a mano: error claro para el usuario
            } catch (\Throwable $e) {
                if ($intento >= 3 || strpos($e->getMessage(), 'uq_prod_codigo') === false) {
                    throw $e;
                }
            }
        }

        if ($productoId === null) {
            throw new \RuntimeException('No se pudo asignar un código al producto. Intente de nuevo.');
        }

        // Crear registro en inventario
        $this->db->insert(
            "INSERT INTO vb_inventario (id_producto, unidad, fraccion, created_at) 
             VALUES (:id, 0, 0, NOW())",
            ['id' => $productoId]
        );

        return $productoId;
    }

    /**
     * Actualizar un producto
     *
     * Importante: si $data NO trae los precios (por ejemplo cuando un cajero
     * edita solo la descripción), se conservan los que ya tenía el producto.
     * Antes se sobrescribían con 0 y el producto quedaba en $0.
     *
     * @param array<string, mixed> $data
     */
    public function actualizar(int $id, array $data, ?int $idUsuario = null): int
    {
        // Valores anteriores: sirven para conservar precios y para la trazabilidad
        $antes = $this->db->fetchOne(
            "SELECT valor_compra, valor_venta, valor_unidad, rentabilidad, precio_maximo_regulado,
                    tipo_venta, unidad_medida, cantidad_por_unidad, unidad_cerrada, fraccion,
                    unidades_intermedias, unidades_por_intermedio, nombre_intermedio, valor_intermedio,
                    activo
             FROM vb_productos WHERE id_producto = :id",
            ['id' => $id]
        ) ?: [];

        $data = $this->normalizarEquivalencia($data, $antes);
        $data['presentacion'] = $this->generarPresentacion($data);

        $compra = (array_key_exists('valor_compra', $data) && $data['valor_compra'] !== '' && $data['valor_compra'] !== null)
            ? (float)$data['valor_compra'] : (float)($antes['valor_compra'] ?? 0);
        $venta = (array_key_exists('valor_venta', $data) && $data['valor_venta'] !== '' && $data['valor_venta'] !== null)
            ? (float)$data['valor_venta'] : (float)($antes['valor_venta'] ?? 0);
        $unidad = (array_key_exists('valor_unidad', $data) && $data['valor_unidad'] !== '' && $data['valor_unidad'] !== null)
            ? (float)$data['valor_unidad'] : (float)($antes['valor_unidad'] ?? 0);

        $tope = (array_key_exists('precio_maximo_regulado', $data) && $data['precio_maximo_regulado'] !== '' && $data['precio_maximo_regulado'] !== null)
            ? (float)$data['precio_maximo_regulado']
            : ($antes['precio_maximo_regulado'] ?? null);

        $sql = "UPDATE vb_productos SET 
                    codigo_barras_1 = :barras1,
                    codigo_barras_2 = :barras2,
                    codigo_barras_3 = :barras3,
                    descripcion = :descripcion,
                    presentacion = :presentacion,
                    marca = :marca,
                    id_proveedor = :proveedor,
                    id_categoria = :categoria,
                    id_iva = :iva,
                    id_seccion = :seccion,
                    unidad_cerrada = :unidad_cerrada,
                    fraccion = :fraccion,
                    unidades_intermedias = :unidades_intermedias,
                    unidades_por_intermedio = :unidades_por_intermedio,
                    nombre_intermedio = :nombre_intermedio,
                    valor_intermedio = :valor_intermedio,
                    valor_compra = :compra,
                    valor_venta = :venta,
                    valor_unidad = :unidad,
                    rentabilidad = :rentabilidad,
                    precio_maximo_regulado = :tope,
                    stock_minimo = :stock_minimo,
                    imagen = :imagen,
                    tipo_venta = :tipo_venta,
                    unidad_medida = :unidad_medida,
                    cantidad_por_unidad = :cantidad_por_unidad,
                    activo = :activo
                WHERE id_producto = :id";

        $afectados = $this->db->executeAffected($sql, [
            'id'                 => $id,
            'barras1'            => $data['codigo_barras_1'] ?? '',
            'barras2'            => $data['codigo_barras_2'] ?? '',
            'barras3'            => $data['codigo_barras_3'] ?? '',
            'descripcion'        => $data['descripcion'],
            'presentacion'       => $data['presentacion'],
            'marca'              => $data['marca'] ?? '',
            'proveedor'          => ($data['id_proveedor'] ?? null) ?: null,
            'categoria'          => ($data['id_categoria'] ?? null) ?: null,
            'iva'                => ($data['id_iva'] ?? null) ?: null,
            'seccion'            => ($data['id_seccion'] ?? null) ?: null,
            'unidad_cerrada'     => (int)($data['unidad_cerrada'] ?? 1),
            'fraccion'           => (int)($data['fraccion'] ?? 0),
            'unidades_intermedias' => (int)($data['unidades_intermedias'] ?? 0),
            'unidades_por_intermedio' => (int)($data['unidades_por_intermedio'] ?? 1),
            'nombre_intermedio'  => $data['nombre_intermedio'] ?? null,
            'valor_intermedio'   => (float)($data['valor_intermedio'] ?? 0),
            'compra'             => $compra,
            'venta'              => $venta,
            'unidad'             => $unidad,
            'rentabilidad'       => $this->calcularRentabilidad($compra, $venta, (int)($data['unidad_cerrada'] ?? 1)),
            'tope'               => $tope,
            'stock_minimo'       => (int)($data['stock_minimo'] ?? 1),
            'imagen'             => $data['imagen'] ?? '',
            'tipo_venta'         => $data['tipo_venta'],
            'unidad_medida'      => $data['unidad_medida'] ?? '',
            'cantidad_por_unidad'=> (float)($data['cantidad_por_unidad'] ?? 1.0000),
            'activo'             => array_key_exists('activo', $data)
                                        ? $this->normalizarActivo($data['activo'])
                                        : (int)($antes['activo'] ?? 1),
        ]);

        // Trazabilidad: si el admin cambió algún precio, queda registrado para siempre
        $cambioCosto  = abs($compra - (float)($antes['valor_compra'] ?? 0)) >= 0.01;
        $cambioVenta  = abs($venta - (float)($antes['valor_venta'] ?? 0)) >= 0.01;
        $cambioUnidad = abs($unidad - (float)($antes['valor_unidad'] ?? 0)) >= 0.01;

        if ($cambioCosto || $cambioVenta || $cambioUnidad) {
            (new Ingreso())->registrarHistorial([
                'id_producto'            => $id,
                'origen'                 => 'EDICION_PRODUCTO',
                'id_referencia'          => null,
                'cantidad'               => 0,
                'precio_compra'          => $cambioCosto ? $compra : null,
                'costo_anterior'         => (float)($antes['valor_compra'] ?? 0),
                'costo_nuevo'            => $compra,
                'precio_venta_anterior'  => (float)($antes['valor_venta'] ?? 0),
                'precio_venta_nuevo'     => $venta,
                'precio_unidad_anterior' => (float)($antes['valor_unidad'] ?? 0),
                'precio_unidad_nuevo'    => $unidad,
                'rentabilidad'           => $this->calcularRentabilidad($compra, $venta, (int)($data['unidad_cerrada'] ?? 1)),
                'motivo'                 => $data['motivo_precio'] ?? 'Cambio manual desde Productos',
                'id_usuario'             => $idUsuario,
            ]);
        }

        return $afectados;
    }

    /**
     * Eliminar (desactivar) un producto
     */
    public function eliminar(int $id): int
    {
        return $this->db->executeAffected(
            "UPDATE vb_productos SET activo = 0 WHERE id_producto = :id",
            ['id' => $id]
        );
    }

    /**
     * Obtener listas para selects (categorías, proveedores, IVAs, secciones)
     */
    /** @return array<string, mixed> */
    public function obtenerCatalogos(): array
    {
        return [
            'categorias'  => $this->db->select("SELECT id_categoria, nombre FROM vb_categorias WHERE activo = 1 ORDER BY nombre"),
            'proveedores' => $this->db->select("SELECT id_proveedor, nombre FROM vb_proveedores WHERE estado = 1 ORDER BY nombre"),
            'ivas'        => $this->db->select("SELECT id_iva, iva, nombre FROM vb_ivas WHERE activo = 1"),
            'secciones'   => $this->db->select("SELECT id_seccion, nombre FROM vb_secciones WHERE activo = 1"),
        ];
    }

    /**
     * Calcular rentabilidad
     *
     * El costo se guarda por unidad interna y el precio de venta es por caja,
     * así que el costo de la venta se multiplica por las unidades de la caja.
     */
    private function calcularRentabilidad(float $compra, float $venta, int $unidadesPorCaja = 1): float
    {
        if ($compra <= 0 || $venta <= 0) return 0;
        $costoVenta = $compra * max(1, $unidadesPorCaja);
        return round((($venta - $costoVenta) / $venta) * 100, 2);
    }
}
