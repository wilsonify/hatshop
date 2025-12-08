<?php

namespace Hatshop\Core\Tests\Unit\Pipeline;

use Hatshop\Core\Pipeline\IPipelineSection;
use Hatshop\Core\Pipeline\PsInitialNotification;
use Hatshop\Core\Pipeline\PsCheckFunds;
use Hatshop\Core\Pipeline\PsCheckStock;
use Hatshop\Core\Pipeline\PsStockOk;
use Hatshop\Core\Pipeline\PsTakePayment;
use Hatshop\Core\Pipeline\PsShipGoods;
use Hatshop\Core\Pipeline\PsShipOk;
use Hatshop\Core\Pipeline\PsFinalNotification;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for all pipeline section classes.
 */
class PipelineSectionsTest extends TestCase
{
    /**
     * @dataProvider pipelineSectionProvider
     */
    public function testPipelineSectionExists(string $className): void
    {
        $this->assertTrue(class_exists($className), "Class {$className} should exist");
    }

    /**
     * @dataProvider pipelineSectionProvider
     */
    public function testPipelineSectionImplementsInterface(string $className): void
    {
        $section = new $className();
        $this->assertInstanceOf(
            IPipelineSection::class,
            $section,
            "{$className} should implement IPipelineSection"
        );
    }

    /**
     * @dataProvider pipelineSectionProvider
     */
    public function testPipelineSectionHasProcessMethod(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        $this->assertTrue(
            $reflection->hasMethod('process'),
            "{$className} should have a process method"
        );
    }

    /**
     * Data provider for pipeline section tests.
     *
     * @return array<array<string>>
     */
    public static function pipelineSectionProvider(): array
    {
        return [
            'PsInitialNotification' => [PsInitialNotification::class],
            'PsCheckFunds' => [PsCheckFunds::class],
            'PsCheckStock' => [PsCheckStock::class],
            'PsStockOk' => [PsStockOk::class],
            'PsTakePayment' => [PsTakePayment::class],
            'PsShipGoods' => [PsShipGoods::class],
            'PsShipOk' => [PsShipOk::class],
            'PsFinalNotification' => [PsFinalNotification::class],
        ];
    }
}
