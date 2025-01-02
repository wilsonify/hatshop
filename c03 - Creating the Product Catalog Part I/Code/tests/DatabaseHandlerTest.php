
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../business/database_handler.php';

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

        // Inject the PDO mock into the static property using reflection
        $reflection = new ReflectionClass(DatabaseHandler::class);
        $property = $reflection->getProperty('_mHandler');
        $property->setAccessible(true);
        $property->setValue($this->pdoMock);
    }

    protected function tearDown(): void
    {
        DatabaseHandler::close();
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

        $reflection = new ReflectionClass(DatabaseHandler::class);
        $property = $reflection->getProperty('_mHandler');
        $property->setAccessible(true);

        $this->assertNull($property->getValue());
    }
}
