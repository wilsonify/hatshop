<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\ErrorHandler;
use Hatshop\Core\Config;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ErrorHandler class.
 *
 * Note: Testing error handlers is tricky because they involve global state.
 * These tests focus on the handler's behavior in isolation.
 */
class ErrorHandlerTest extends TestCase
{
    private const TEST_FILE_PATH = '/test/file.php';
    private const TEST_WARNING_MESSAGE = 'Test warning';

    protected function setUp(): void
    {
        Config::reset();
        Config::set('debugging', false);
        Config::set('is_warning_fatal', false);
        Config::set('log_errors', false);
        Config::set('send_error_mail', false);
    }

    protected function tearDown(): void
    {
        Config::reset();
        restore_error_handler();
    }

    public function testInitSetsErrorHandler(): void
    {
        // Get current handler before init
        $previousHandler = set_error_handler(function () {
            return true;
        });
        restore_error_handler();

        ErrorHandler::init();

        // Get handler after init
        $currentHandler = set_error_handler(function () {
            return true;
        });
        restore_error_handler();

        // Handler should have changed
        $this->assertNotSame($previousHandler, $currentHandler);
    }

    public function testInitOnlyInitializesOnce(): void
    {
        ErrorHandler::init();

        // Call init again - should be idempotent
        ErrorHandler::init();

        // No exception means it handled the re-initialization gracefully
        $this->assertTrue(true);
    }

    public function testHandlerReturnsTrue(): void
    {
        Config::set('debugging', false);
        Config::set('is_warning_fatal', false);
        Config::set('site_generic_error_message', 'Error occurred');

        // Capture output
        ob_start();
        $result = ErrorHandler::handler(E_WARNING, self::TEST_WARNING_MESSAGE, self::TEST_FILE_PATH, 42);
        ob_end_clean();

        $this->assertTrue($result);
    }

    public function testHandlerDisplaysGenericMessageInProductionMode(): void
    {
        Config::set('debugging', false);
        Config::set('is_warning_fatal', false);
        Config::set('site_generic_error_message', 'Generic error message');

        ob_start();
        ErrorHandler::handler(E_WARNING, self::TEST_WARNING_MESSAGE, self::TEST_FILE_PATH, 42);
        $output = ob_get_clean();

        $this->assertStringContainsString('Generic error message', $output);
        $this->assertStringNotContainsString(self::TEST_WARNING_MESSAGE, $output);
    }

    public function testHandlerDisplaysDetailedMessageInDebugMode(): void
    {
        Config::set('debugging', true);
        Config::set('is_warning_fatal', false);

        ob_start();
        ErrorHandler::handler(E_WARNING, 'Test warning message', self::TEST_FILE_PATH, 42);
        $output = ob_get_clean();

        $this->assertStringContainsString('Test warning message', $output);
        $this->assertStringContainsString(self::TEST_FILE_PATH, $output);
        $this->assertStringContainsString('42', $output);
    }

    public function testHandlerIncludesErrorNumber(): void
    {
        Config::set('debugging', true);
        Config::set('is_warning_fatal', false);

        ob_start();
        ErrorHandler::handler(E_WARNING, 'Test', self::TEST_FILE_PATH, 10);
        $output = ob_get_clean();

        $this->assertStringContainsString('ERRNO:', $output);
    }

    public function testHandlerIncludesBacktrace(): void
    {
        Config::set('debugging', true);
        Config::set('is_warning_fatal', false);

        ob_start();
        ErrorHandler::handler(E_WARNING, 'Test', self::TEST_FILE_PATH, 10);
        $output = ob_get_clean();

        $this->assertStringContainsString('backtrace', strtolower($output));
    }

    public function testHandlerHtmlEscapesOutput(): void
    {
        Config::set('debugging', true);
        Config::set('is_warning_fatal', false);

        ob_start();
        ErrorHandler::handler(E_WARNING, '<script>alert("xss")</script>', self::TEST_FILE_PATH, 10);
        $output = ob_get_clean();

        // Should be HTML escaped, not contain raw script tags
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }
}
