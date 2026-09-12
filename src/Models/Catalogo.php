<?php
namespace SIG\Models;

use SIG\Core\Database;

/**
 * Modelo Catalogo - CRUD genérico para tablas simples de catálogo
 *
 * Tablas soportadas: categorías, secciones y tipos de IVA.
 * Los nombres de tabla/columna se toman de una lista blanca interna,
 * nunca de la entrada del usuario, para evitar inyección SQL.
 */
class Catalogo
{
    private Database $db;

    /** Lista blanca de catálogos soportados */
    private const TIPOS = [
        'categorias' => [
            'tabla'    => 'vb_categorias',
            'pk'       => 'id_categoria',
            'etiqueta' => 'Categoría',
            'extra'    => ['descripcion'],
        ],
        'secciones' => [
            'tabla'    => 'vb_secciones',
            'pk'       => 'id_seccion',
            'etiqueta' => 'Sección',
            'extra'    => [],
        ],
        'ivas' => [
            'tabla'    => 'vb_ivas',
            'pk'       => 'id_iva',
            'etiqueta' => 'Tipo de IVA',
            'extra'    => ['iva'],
        ],
    ];

    /** Relación catálogo -> columna que lo referencia en vb_productos */
    private const USO_PRODUCTOS = [
        'categorias' => 'id_categoria',
        'secciones'  => 'id_seccion',
        'ivas'       => 'id_iva',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Tipos válidos de catálogo */
    public static function tipos(): array
    {
        return array_keys(self::TIPOS);
    }

    /** Devuelve la configuración de un tipo o lanza excepción */
    private function config(string $tipo): array
    {
        if (!isset(self::TIPOS[$tipo])) {
            throw new \RuntimeException('Catálogo no válido');
        }
        return self::TIPOS[$tipo];
    }

    /**
     * Listar los registros de un catálogo, incluyendo cuántos productos lo usan
     */
    public function listar(string $tipo): array
    {
        $cfg = $this->config($tipo);

        $cols = array_unique(array_merge([$cfg['pk'], 'nombre', 'activo'], $cfg['extra']));
        $sqlCols = implode(', ', array_map(fn($c) => "t.`{$c}`", $cols));

        $colUso = self::USO_PRODUCTOS[$tipo];
        $sqlUso = "(SELECT COUNT(*) FROM vb_productos p WHERE p.`{$colUso}` = t.`{$cfg['pk']}`)";

        return $this->db->select(
            "SELECT {$sqlCols}, {$sqlUso} AS productos
             FROM `{$cfg['tabla']}` t
             ORDER BY t.nombre ASC"
        );
    }

    /**
     * Crear o actualizar un registro del catálogo
     */
    public function guardar(string $tipo, array $data): int
    {
        $cfg = $this->config($tipo);
        $id  = (int)($data[$cfg['pk']] ?? $data['id'] ?? 0);

        $nombre = trim((string)($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \RuntimeException('El nombre es obligatorio');
        }
        if (mb_strlen($nombre) > 100) {
            throw new \RuntimeException('El nombre no puede superar los 100 caracteres');
        }

        $activo = (int)($data['activo'] ?? 1);
        $activo = ($activo === 0) ? 0 : 1;

        // Evitar nombres duplicados
        $duplicado = $this->db->fetchOne(
            "SELECT `{$cfg['pk']}` FROM `{$cfg['tabla']}`
             WHERE nombre = :nombre AND `{$cfg['pk']}` <> :id",
            ['nombre' => $nombre, 'id' => $id]
        );
        if ($duplicado) {
            throw new \RuntimeException('Ya existe un registro con ese nombre');
        }

        $valores = ['nombre' => $nombre, 'activo' => $activo];

        if ($tipo === 'categorias') {
            $valores['descripcion'] = trim((string)($data['descripcion'] ?? ''));
        }

        if ($tipo === 'ivas') {
            $porcentaje = (float)($data['iva'] ?? 0);
            if ($porcentaje < 0 || $porcentaje > 100) {
                throw new \RuntimeException('El porcentaje de IVA debe estar entre 0 y 100');
            }
            $valores['iva'] = $porcentaje;
        }

        if ($id > 0) {
            $sets = [];
            $params = [];
            foreach ($valores as $col => $val) {
                $sets[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $params['__id'] = $id;

            $this->db->executeAffected(
                "UPDATE `{$cfg['tabla']}` SET " . implode(', ', $sets) . " WHERE `{$cfg['pk']}` = :__id",
                $params
            );
            return $id;
        }

        $cols    = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($valores)));
        $holders = implode(', ', array_map(fn($c) => ":{$c}", array_keys($valores)));

        return $this->db->insert(
            "INSERT INTO `{$cfg['tabla']}` ({$cols}) VALUES ({$holders})",
            $valores
        );
    }

    /**
     * Cuántos productos usan este registro del catálogo
     */
    public function contarUso(string $tipo, int $id): int
    {
        $col = self::USO_PRODUCTOS[$tipo] ?? null;
        if ($col === null || $id <= 0) {
            return 0;
        }

        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM vb_productos WHERE `{$col}` = :id",
            ['id' => $id]
        );

        return (int)($row['total'] ?? 0);
    }

    /**
     * Eliminar un registro del catálogo.
     * Las FK de vb_productos son ON DELETE SET NULL, por eso se avisa
     * cuántos productos quedarán sin este dato.
     */
    public function eliminar(string $tipo, int $id): array
    {
        $cfg = $this->config($tipo);

        $registro = $this->db->fetchOne(
            "SELECT nombre FROM `{$cfg['tabla']}` WHERE `{$cfg['pk']}` = :id",
            ['id' => $id]
        );
        if (!$registro) {
            throw new \RuntimeException('El registro no existe');
        }

        $afectados = $this->contarUso($tipo, $id);

        $this->db->executeAffected(
            "DELETE FROM `{$cfg['tabla']}` WHERE `{$cfg['pk']}` = :id",
            ['id' => $id]
        );

        return ['nombre' => $registro['nombre'], 'productos_afectados' => $afectados];
    }
}
