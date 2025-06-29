<?php

namespace Roger;
use \PDO as PDO;
use \PDOStatement as PDOStatement;
use \Exception as Exception;

/**
 * Class Database
 *
 * Handles database connections, query execution, and error management.
 * Implements singleton pattern to ensure a single database connection.
 *
 * @package Roger
 * @author gav.corbett@gmail.com
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Database constructor.
     *
     * @param string $host The database host.
     * @param string $dbname The database name.
     * @param string $username The database username.
     * @param string $password The database password.
     */
    private function __construct(string $host, string $dbname, string $username, string $password)
    {
        try {
            $dsn = "mysql:host=$host;dbname=$dbname";
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the singleton instance of the Database class.
     *
     * @return Database The instance of the Database class.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database('127.0.0.1:3333', 'robert_php_dev_test', 'gavin', 'C8h10n4o2');
        }
        return self::$instance;
    }

    /**
     * Execute a SQL query with optional parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @return PDOStatement The executed statement.
     */
    public function execute(string $query, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch a single row from a query result.
     *
     * @param string $query The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @return array|null The resulting row or null if not found.
     */
    public function fetch(string $query, array $params = []): ?array
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get the last insert ID.
     *
     * @return int|false The resulting row or null if not found.
     */
    public function getLastInsertId(): ?int
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Fetch all rows from a query result.
     *
     * @param string $query The SQL query to execute.
     * @param array $params The parameters to bind to the query.
     * @return array The resulting rows.
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction.
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback a transaction.
     */
    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }
}
