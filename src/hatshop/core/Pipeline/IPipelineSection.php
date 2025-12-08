<?php

namespace Hatshop\Core\Pipeline;

/**
 * Interface for order pipeline sections (Chapter 13).
 *
 * Each pipeline section implements a specific step in order processing:
 * - Initial notification
 * - Check funds
 * - Check stock
 * - Stock confirmation
 * - Take payment
 * - Ship goods
 * - Ship confirmation
 * - Final notification
 */
interface IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     * @return void
     */
    public function process(OrderProcessor $processor): void;
}
