<?php
namespace SIG\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Clase Database - Conexión PDO con prepared statements
 * 
 * Singleton para manejar la conexión a la base de datos
 * con soporte para consultas preparadas (seguridad contra SQL Injection).
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private array $config;

    private function __construct()
    {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connect();
    }

    /**
     * Obtener la instancia única (Singleton)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer la conexión PDO
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['db_name'],
                $this->config['charset']
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            // Configurar zona horaria y charset
            $this->pdo->exec("SET time_zone = '-05:00'");
            $this->pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        } catch (PDOException $e) {
            throw new RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Ejecutar una consulta SELECT con prepared statements
     * 
     * @param string $sql  Consulta SQL con placeholders (:nombre o ?)
     * @param array  $params Parámetros para la consulta
     * @return array  Array de resultados
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener una sola fila
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecutar una consulta INSERT y devolver el ID insertado
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->execute($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Ejecutar una consulta UPDATE/DELETE y devolver filas afectadas
     */
    public function executeAffected(string $sql, array $params = []): int
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Ejecutar cualquier consulta preparada
     * 
     * @param string $sql  Consulta SQL con placeholders
     * @param array  $params Parámetros seguros
     * @return \PDOStatement
     */
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Iniciar una transacción
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar una transacción
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir una transacción
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Prevenir clonación (Singleton)
     */
    private function __clone() {}
    public function __wakeup() {
        throw new RuntimeException('Cannot unserialize singleton');
    }
}
