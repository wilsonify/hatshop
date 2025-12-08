<?php

namespace Hatshop\Core\Tests\Unit\Pipeline;

use Hatshop\Core\Pipeline\IPipelineSection;
use Hatshop\Core\Pipeline\OrderProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for IPipelineSection interface.
 */
class IPipelineSectionTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(IPipelineSection::class));
    }

    public function testInterfaceHasProcessMethod(): void
    {
        $reflection = new \ReflectionClass(IPipelineSection::class);
        $this->assertTrue($reflection->hasMethod('process'));

        $method = $reflection->getMethod('process');
        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('processor', $parameters[0]->getName());
    }
}
