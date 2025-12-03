<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Business\ErrorHandler;
use PHPUnit\Framework\TestCase;

class BusinessErrorHandlerIntegrationTest extends TestCase
{
    private $logFile;

    protected function setUp(): void
    {
        // Define constants for testing
        define('DEBUGGING', false);
        define('SEND_ERROR_MAIL', true);
        define('ADMIN_ERROR_MAIL', 'admin@example.com');
        define('SENDMAIL_FROM', 'no-reply@example.com');
        define('LOG_ERRORS', true);

        // Create a temporary log file
        $this->logFile = sys_get_temp_dir() . '/test_error.log';
        define('LOG_ERRORS_FILE', $this->logFile);

        // Clear the log file before each test
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    protected function tearDown(): void
    {
        // Clean up the temporary log file
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testHandleErrorIntegration()
    {
        // Set the error handler
        ErrorHandler::setHandler();

        // Trigger a non-fatal error
        trigger_error("This is a test warning", E_USER_WARNING);

        // Assert the log file contains the error message
        $this->assertFileExists($this->logFile);
        $logContents = file_get_contents($this->logFile);
        $this->assertStringContainsString("This is a test warning", $logContents);

        // Trigger a fatal error and capture output
        $this->expectOutputRegex('/An error occurred./');
        trigger_error("This is a test fatal error", E_USER_ERROR);
    }
}
