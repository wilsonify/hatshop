<?php

namespace Hatshop\Core\Tests\Unit\WebServices;

use Hatshop\Core\WebServices\Amazon;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Amazon web services class.
 */
class AmazonTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(Amazon::class));
    }

    public function testHasGetProductsMethod(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $this->assertTrue($reflection->hasMethod('getProducts'));
    }

    public function testGetProductsReturnsArray(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $method = $reflection->getMethod('getProducts');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function testGetProductsIsPublic(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $method = $reflection->getMethod('getProducts');

        $this->assertTrue($method->isPublic());
    }

    public function testPrivateDataFormatMethod(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $this->assertTrue($reflection->hasMethod('dataFormat'));

        $method = $reflection->getMethod('dataFormat');
        $this->assertTrue($method->isPrivate());
    }

    public function testPrivateGetDataWithRestMethod(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $this->assertTrue($reflection->hasMethod('getDataWithRest'));

        $method = $reflection->getMethod('getDataWithRest');
        $this->assertTrue($method->isPrivate());
    }

    public function testPrivateGetDataWithSoapMethod(): void
    {
        $reflection = new \ReflectionClass(Amazon::class);
        $this->assertTrue($reflection->hasMethod('getDataWithSoap'));

        $method = $reflection->getMethod('getDataWithSoap');
        $this->assertTrue($method->isPrivate());
    }
}
