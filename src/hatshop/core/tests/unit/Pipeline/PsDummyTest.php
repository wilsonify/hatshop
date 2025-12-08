<?php

namespace Hatshop\Core\Tests\Unit\Pipeline;

use Hatshop\Core\Pipeline\PsDummy;
use Hatshop\Core\Pipeline\OrderProcessor;
use Hatshop\Core\Pipeline\IPipelineSection;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PsDummy pipeline section.
 */
class PsDummyTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(PsDummy::class));
    }

    public function testImplementsInterface(): void
    {
        $dummy = new PsDummy();
        $this->assertInstanceOf(IPipelineSection::class, $dummy);
    }

    public function testHasProcessMethod(): void
    {
        $reflection = new \ReflectionClass(PsDummy::class);
        $this->assertTrue($reflection->hasMethod('process'));
    }
}
