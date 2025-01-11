<?php
class DatabaseHandler
{
    private static ?PDO $_mHandler;

    private function __construct() {
        // Private constructor to prevent direct creation of object
    }

    // Returns a database connection, creates one if it doesn't exist
    private static function getHandler(): PDO
    {
        if (!isset(self::$_mHandler)) {
            self::$_mHandler = self::createHandler();
        }
        return self::$_mHandler;
    }

    // Creates and configures a PDO handler
    private static function createHandler(): PDO
    {
        try {
            $pdo = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_PERSISTENT => DB_PERSISTENCY,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            return $pdo;
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    // Closes the database handler
    public static function close(): void
    {
        self::$_mHandler = null;
    }

    // Prepares a query
    public static function prepare(string $queryString): PDOStatement
    {
        return self::executeSafely(function () use ($queryString) {
            return self::getHandler()->prepare($queryString);
        });
    }

    // Executes a prepared statement
    public static function execute(PDOStatement $statement, array $params = []): void
    {
        self::executeSafely(function () use ($statement, $params) {
            $statement->execute($params);
        });
    }

    // Fetches all rows from a statement
    public static function getAll(PDOStatement $statement, array $params = [], int $fetchStyle = PDO::FETCH_ASSOC): array
    {
        return self::executeSafely(function () use ($statement, $params, $fetchStyle) {
            self::execute($statement, $params);
            return $statement->fetchAll($fetchStyle);
        });
    }

    // Fetches one row from a statement
    public static function getRow(PDOStatement $statement, array $params = [], int $fetchStyle = PDO::FETCH_ASSOC): ?array
    {
        return self::executeSafely(function () use ($statement, $params, $fetchStyle) {
            self::execute($statement, $params);
            return $statement->fetch($fetchStyle) ?: null;
        });
    }

    // Fetches the first column from the first row
    public static function getOne(PDOStatement $statement, array $params = []): mixed
    {
        return self::executeSafely(function () use ($statement, $params) {
            self::execute($statement, $params);
            $result = $statement->fetch(PDO::FETCH_NUM);
            return $result[0] ?? null;
        });
    }

    // Executes a function with exception handling
    private static function executeSafely(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
