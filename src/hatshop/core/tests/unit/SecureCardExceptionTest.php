<?php

namespace Hatshop\Core\Tests\Unit;

use Hatshop\Core\SecureCardException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SecureCardException class.
 */
class SecureCardExceptionTest extends TestCase
{
    public function testExceptionCanBeThrown(): void
    {
        $this->expectException(SecureCardException::class);
        throw new SecureCardException('Test exception');
    }

    public function testExceptionMessage(): void
    {
        $message = 'Card decryption failed';
        $exception = new SecureCardException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 42;
        $exception = new SecureCardException('Test', $code);
        
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new SecureCardException('Test', 0, $previous);
        
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionIsInstanceOfException(): void
    {
        $exception = new SecureCardException('Test');
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}
