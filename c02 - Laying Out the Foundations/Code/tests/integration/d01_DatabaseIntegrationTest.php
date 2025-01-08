<?php

use PHPUnit\Framework\TestCase;

class d01_DatabaseIntegrationTest extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        define('DB_SERVER', getenv('HATSHOP_DB_SERVER'));
        define('DB_USERNAME', getenv('HATSHOP_DB_USERNAME'));
        define('DB_PASSWORD', getenv('HATSHOP_DB_PASSWORD'));
        define('DB_DATABASE', getenv('HATSHOP_DB_DATABASE'));
        define('PDO_DSN', 'pgsql:host=' . DB_SERVER . ';dbname=' . DB_DATABASE . ';sslmode=require');

        // Establish connection to PostgreSQL database
        $this->connection = pg_connect(
            "host=" . getenv('HATSHOP_DB_SERVER') .
            " dbname=" . getenv('HATSHOP_DB_DATABASE') .
            " user=" . getenv('HATSHOP_DB_USERNAME') .
            " password=" . getenv('HATSHOP_DB_PASSWORD')
        );

        // Assert that connection is successful
        $this->assertNotFalse($this->connection, "Failed to connect to the database.");
    }

    protected function tearDown(): void
    {
        // Close the connection after test
        if ($this->connection) {
            pg_close($this->connection);
        }
    }

    public function testQueryExecution()
    {
        // Ensure connection exists
        $this->assertNotFalse($this->connection, "No active database connection.");

        // Execute a simple query
        $result = pg_query($this->connection, "SELECT 1 FROM department");

        // Assert query was successful
        $this->assertNotFalse($result, "Query execution failed.");

        // Fetch results and assert values if necessary
        $rows = pg_fetch_all($result);
        $this->assertIsArray($rows, "Query returned no results.");
    }

    public function testConnectionClosure()
    {
        // Close the connection
        pg_close($this->connection);

        // Assert the connection is no longer valid
        $this->assertFalse(@pg_connection_status($this->connection) === PGSQL_CONNECTION_OK, "Connection is still open.");
    }
}
