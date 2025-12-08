<?php

namespace Hatshop\Core\Pipeline;

/**
 * Ship OK pipeline section (Chapter 14).
 *
 * Called when supplier confirms goods have shipped.
 * Status: 6 -> 7
 */
class PsShipOk implements IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $processor->createAudit('PsShipOk started.', 20600);

        // Set order shipment date
        $processor->setDateShipped();

        $processor->createAudit('Order dispatched by supplier.', 20602);

        // Update order status
        $processor->updateOrderStatus(7);

        // Continue processing
        $processor->continueNow = true;

        $processor->createAudit('PsShipOk finished.', 20601);
    }
}
