<?php

namespace Hatshop\Core;

use PDO;
use PDOException;

/**
 * Database handler providing generic data access functionality.
 *
 * This is a singleton class that manages PDO database connections
 * and provides wrapper methods for common database operations.
 */
class DatabaseHandler
{
    /** @var PDO|null The PDO database handler instance */
    private static ?PDO $handler = null;

    /**
     * Private constructor to prevent direct object creation.
     */
    private function __construct()
    {
    }

    /**
     * Get an initialized database handler.
     *
     * @return PDO The database handler
     * @throws PDOException If connection fails
     */
    private static function getHandler(): PDO
    {
        if (!isset(self::$handler)) {
            try {
                $dsn = Config::getPdoDsn();
                $username = Config::get('db_username');
                $password = Config::get('db_password');
                $persistency = Config::get('db_persistency');

                self::$handler = new PDO(
                    $dsn,
                    $username,
                    $password,
                    [PDO::ATTR_PERSISTENT => $persistency]
                );

                self::$handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                self::close();
                trigger_error($e->getMessage(), E_USER_ERROR);
                throw $e;
            }
        }

        return self::$handler;
    }

    /**
     * Close the database connection.
     */
    public static function close(): void
    {
        self::$handler = null;
    }

    /**
     * Prepare a SQL statement.
     *
     * @param string $queryString The SQL query string
     * @return \PDOStatement|false The prepared statement
     */
    public static function prepare(string $queryString): \PDOStatement|false
    {
        try {
            $handler = self::getHandler();
            return $handler->prepare($queryString);
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
            return false;
        }
    }

    /**
     * Execute a prepared statement.
     *
     * @param \PDOStatement $statementHandler The prepared statement
     * @param array|null $params Parameters to bind
     * @return bool True on success
     */
    public static function execute(\PDOStatement $statementHandler, ?array $params = null): bool
    {
        try {
            return $statementHandler->execute($params);
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
            return false;
        }
    }

    /**
     * Execute a prepared statement and fetch all results.
     *
     * @param \PDOStatement $statementHandler The prepared statement
     * @param array|null $params Parameters to bind
     * @param int $fetchStyle PDO fetch style constant
     * @return array|null The query results
     */
    public static function getAll(
        \PDOStatement $statementHandler,
        ?array $params = null,
        int $fetchStyle = PDO::FETCH_ASSOC
    ): ?array {
        try {
            self::execute($statementHandler, $params);
            return $statementHandler->fetchAll($fetchStyle);
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
            return null;
        }
    }

    /**
     * Execute a prepared statement and fetch a single row.
     *
     * @param \PDOStatement $statementHandler The prepared statement
     * @param array|null $params Parameters to bind
     * @param int $fetchStyle PDO fetch style constant
     * @return array|false|null The query result row
     */
    public static function getRow(
        \PDOStatement $statementHandler,
        ?array $params = null,
        int $fetchStyle = PDO::FETCH_ASSOC
    ): array|false|null {
        try {
            self::execute($statementHandler, $params);
            return $statementHandler->fetch($fetchStyle);
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
            return null;
        }
    }

    /**
     * Execute a prepared statement and fetch a single value.
     *
     * @param \PDOStatement $statementHandler The prepared statement
     * @param array|null $params Parameters to bind
     * @return mixed The first column of the first row
     */
    public static function getOne(\PDOStatement $statementHandler, ?array $params = null): mixed
    {
        try {
            self::execute($statementHandler, $params);
            $result = $statementHandler->fetch(PDO::FETCH_NUM);
            return $result ? $result[0] : null;
        } catch (PDOException $e) {
            self::close();
            trigger_error($e->getMessage(), E_USER_ERROR);
            return null;
        }
    }
}
