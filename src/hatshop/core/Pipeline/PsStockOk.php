<?php

namespace Hatshop\Core\Pipeline;

/**
 * Stock OK pipeline section (Chapter 14).
 *
 * Called when supplier confirms stock is available.
 * Status: 3 -> 4
 */
class PsStockOk implements IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $processor->createAudit('PsStockOk started.', 20300);

        // Stock confirmed by supplier (triggered via admin interface)
        $processor->createAudit('Stock confirmed by supplier.', 20302);

        // Update order status
        $processor->updateOrderStatus(4);

        // Continue processing
        $processor->continueNow = true;

        $processor->createAudit('PsStockOk finished.', 20301);
    }
}
