<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../business/error_handler.php';


use PHPUnit\Framework\TestCase;

class b01_ErrorHandlerTest extends TestCase
{
    public function testFormatErrorMessage()
    {
        $errNo = E_WARNING;
        $errStr = "Test error";
        $errFile = "test.php";
        $errLine = 42;
        $backtrace = "Backtrace content";

        $formatted = ErrorHandler::formatErrorMessage($errNo, $errStr, $errFile, $errLine, $backtrace);

        $this->assertStringContainsString('ERRNO: ' . $errNo, $formatted);
        $this->assertStringContainsString('TEXT: ' . $errStr, $formatted);
        $this->assertStringContainsString('LOCATION: ' . $errFile, $formatted);
        $this->assertStringContainsString('line ' . $errLine, $formatted);
        $this->assertStringContainsString('Showing backtrace:', $formatted);
        $this->assertStringContainsString($backtrace, $formatted);
    }

    public function testIsNonFatalError()
    {
        $this->assertTrue(ErrorHandler::isNonFatalError(E_WARNING));
        $this->assertTrue(ErrorHandler::isNonFatalError(E_NOTICE));
        $this->assertTrue(ErrorHandler::isNonFatalError(E_USER_NOTICE));
        $this->assertFalse(ErrorHandler::isNonFatalError(E_ERROR));
        $this->assertFalse(ErrorHandler::isNonFatalError(E_PARSE));
    }

    public function testGetBacktrace()
    {
        $backtrace = ErrorHandler::getBacktrace();
        $this->assertIsString($backtrace);
        $this->assertStringContainsString('ErrorHandlerTest.testGetBacktrace', $backtrace);
    }

    public function testFormatArgumentsWithVariousTypes()
    {
        $args = [
            null,
            true,
            false,
            [1, 2, 3],
            new stdClass(),
            "A string with > 64 characters is truncated because this is way too long to display fully.",
            42,
        ];

        $formattedArgs = ErrorHandler::formatArguments($args);

        $this->assertStringContainsString('null', $formattedArgs);
        $this->assertStringContainsString('true', $formattedArgs);
        $this->assertStringContainsString('false', $formattedArgs);
        $this->assertStringContainsString('Array[3]', $formattedArgs);
        $this->assertStringContainsString('Object: stdClass', $formattedArgs);
        $this->assertStringContainsString('A string with > 64 characters is truncated because', $formattedArgs);
        $this->assertStringContainsString('42', $formattedArgs);
    }

    public function testHandleErrorNonFatal()
    {
        define('DEBUGGING', true);

        $this->expectOutputRegex('/ERRNO: ' . E_WARNING . '/');

        ErrorHandler::handleError(E_WARNING, "Non-fatal error", "test.php", 42);
    }



    public function testHandleErrorLogging()
    {
        define('SEND_ERROR_MAIL', true);
        define('ADMIN_ERROR_MAIL', 'admin@example.com');
        define('SENDMAIL_FROM', 'no-reply@example.com');
        define('LOG_ERRORS', true);
        define('LOG_ERRORS_FILE', '/tmp/error.log');

        ErrorHandler::handleErrorLogging("Test error message");

        $this->assertFileExists(LOG_ERRORS_FILE);
        $this->assertStringContainsString('Test error message', file_get_contents(LOG_ERRORS_FILE));
    }
}
