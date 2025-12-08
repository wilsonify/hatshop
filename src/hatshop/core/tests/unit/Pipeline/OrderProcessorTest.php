<?php

namespace Hatshop\Core\Tests\Unit\Pipeline;

use Hatshop\Core\Pipeline\OrderProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for OrderProcessor class.
 */
class OrderProcessorTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(OrderProcessor::class));
    }

    public function testOrderInfoProperty(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasProperty('orderInfo'));
    }

    public function testOrderDetailsInfoProperty(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasProperty('orderDetailsInfo'));
    }

    public function testCustomerInfoProperty(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasProperty('customerInfo'));
    }

    public function testContinueNowProperty(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasProperty('continueNow'));
    }

    public function testHasProcessMethod(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasMethod('process'));
    }

    public function testHasCreateAuditMethod(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasMethod('createAudit'));
    }

    public function testHasSetAuthCodeAndReferenceMethod(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasMethod('setAuthCodeAndReference'));
    }

    public function testHasMailSupplierMethod(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasMethod('mailSupplier'));
    }

    public function testHasMailAdminMethod(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $this->assertTrue($reflection->hasMethod('mailAdmin'));
    }

    public function testConstructorRequiresOrderId(): void
    {
        $reflection = new \ReflectionClass(OrderProcessor::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('orderId', $parameters[0]->getName());
        $this->assertEquals('int', $parameters[0]->getType()->getName());
    }
}
