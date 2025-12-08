<?php

namespace Hatshop\Core\Tests\Unit\Payment;

use Hatshop\Core\Payment\AuthorizeNetRequest;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for AuthorizeNetRequest class.
 */
class AuthorizeNetRequestTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(AuthorizeNetRequest::class));
    }

    public function testHasSetRequestMethod(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $this->assertTrue($reflection->hasMethod('setRequest'));
    }

    public function testHasGetResponseMethod(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $this->assertTrue($reflection->hasMethod('getResponse'));
    }

    public function testSetRequestReturnsVoid(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $method = $reflection->getMethod('setRequest');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    public function testGetResponseHasReturnType(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $method = $reflection->getMethod('getResponse');
        $returnType = $method->getReturnType();

        // Returns string|false which is a union type in PHP 8
        $this->assertNotNull($returnType);
    }

    public function testSetRequestAcceptsArray(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $method = $reflection->getMethod('setRequest');
        $parameters = $method->getParameters();

        // Should have 1 parameter: request (array)
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType()->getName());
    }

    public function testConstructorAcceptsOptionalUrl(): void
    {
        $reflection = new \ReflectionClass(AuthorizeNetRequest::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        // Should have 1 optional parameter: url
        $this->assertCount(1, $parameters);
        $this->assertEquals('url', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->isOptional());
    }

    public function testCanInstantiate(): void
    {
        $request = new AuthorizeNetRequest('https://test.example.com');
        $this->assertInstanceOf(AuthorizeNetRequest::class, $request);
    }
}
