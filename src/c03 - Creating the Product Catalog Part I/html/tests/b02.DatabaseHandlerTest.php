<?php

require_once __DIR__ . '/../vendor/autoload.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading
require_once __DIR__ . '/../business/database_handler.php'; // NOSONAR

use PHPUnit\Framework\TestCase;

class DatabaseHandlerTest extends TestCase
{
    private $pdoMock;
    private $statementMock;

    protected function setUp(): void
    {
        // Create mock objects for PDO and PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->statementMock = $this->createMock(PDOStatement::class);

        // Inject the PDO mock into the static property using reflection (NOSONAR S3011)
        // SAFETY: This accessibility bypass is safe because:
        // 1. It's confined to test scope only - never used in production code
        // 2. The property is properly restored in tearDown() after each test
        // 3. Passing null as first parameter to setValue() explicitly indicates we're working with a static property
        // 4. This is the standard approach for testing singleton patterns without requiring a real database
        // 5. Test isolation is maintained - no state leaks between test methods
        $reflection = new ReflectionClass(DatabaseHandler::class);
        $property = $reflection->getProperty('mHandler');
        $property->setAccessible(true); // NOSONAR - Required to inject mock into private static property
        $property->setValue(null, $this->pdoMock); // NOSONAR - Safe: null = static property, properly cleaned in tearDown()
    }

    protected function tearDown(): void
    {
        // Clean up the static property using reflection to ensure complete reset (NOSONAR S3011)
        // SAFETY: This cleanup is critical for test isolation:
        // 1. Prevents state pollution between test methods
        // 2. Ensures each test starts with a clean slate
        // 3. Uses the same reflection approach as setUp() for consistency
        // 4. Explicitly sets to null to match the initial state of DatabaseHandler
        $reflection = new ReflectionClass(DatabaseHandler::class);
        $property = $reflection->getProperty('mHandler');
        $property->setAccessible(true); // NOSONAR - Required to reset private static property
        $property->setValue(null, null); // NOSONAR - Safe: Restore to initial uninitialized state
    }

    public function testPrepare(): void
    {
        $query = "SELECT * FROM users";

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($this->statementMock);

        $result = DatabaseHandler::prepare($query);
        $this->assertInstanceOf(PDOStatement::class, $result);
    }

    public function testExecute(): void
    {
        $params = ['id' => 1];

        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with($params);

        DatabaseHandler::execute($this->statementMock, $params);
    }

    public function testGetAll(): void
    {
        $queryResult = [['id' => 1, 'name' => 'John Doe']];
        $this->statementMock->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($queryResult);

        $this->pdoMock->method('prepare')
            ->willReturn($this->statementMock);

        $query = "SELECT * FROM users";
        $statement = DatabaseHandler::prepare($query);
        $result = DatabaseHandler::getAll($statement);

        $this->assertEquals($queryResult, $result);
    }

    public function testGetRow(): void
    {
        $queryResult = ['id' => 1, 'name' => 'John Doe'];
        $this->statementMock->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($queryResult);

        $this->pdoMock->method('prepare')
            ->willReturn($this->statementMock);

        $query = "SELECT * FROM users WHERE id = :id";
        $statement = DatabaseHandler::prepare($query);
        $result = DatabaseHandler::getRow($statement, ['id' => 1]);

        $this->assertEquals($queryResult, $result);
    }

    public function testGetOne(): void
    {
        $queryResult = [1];
        $this->statementMock->method('fetch')
            ->with(PDO::FETCH_NUM)
            ->willReturn($queryResult);

        $this->pdoMock->method('prepare')
            ->willReturn($this->statementMock);

        $query = "SELECT COUNT(*) FROM users";
        $statement = DatabaseHandler::prepare($query);
        $result = DatabaseHandler::getOne($statement);

        $this->assertEquals(1, $result);
    }

    public function testClose(): void
    {
        DatabaseHandler::close();

        // Using reflection to verify internal state (NOSONAR S3011)
        // SAFETY: This verification is safe because:
        // 1. We're only reading the property value, not modifying it
        // 2. It's necessary to verify close() correctly nullifies the internal handler
        // 3. There's no public API to check if the handler is closed
        // 4. Passing null to getValue() indicates we're reading a static property
        $reflection = new ReflectionClass(DatabaseHandler::class);
        $property = $reflection->getProperty('mHandler');
        $property->setAccessible(true); // NOSONAR - Required to read private static property

        $this->assertNull($property->getValue(null)); // NOSONAR - Safe: null = static property, read-only verification
    }
}
