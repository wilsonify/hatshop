<?php

namespace Hatshop\Core\Pipeline;

use Hatshop\Core\FeatureFlags;

/**
 * Take payment pipeline section (Chapter 14-15).
 *
 * Charges the customer's credit card.
 * Status: 4 -> 5
 */
class PsTakePayment implements IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $processor->createAudit('PsTakePayment started.', 20400);

        // Check if credit card processing is enabled
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_CREDIT_CARD)) {
            // Real credit card charge would happen here
            $processor->createAudit('Funds deducted via payment gateway.', 20402);
        } else {
            // Dummy processing - assume payment succeeds
            $processor->createAudit('Funds deducted from customer credit card account (dummy).', 20402);
        }

        // Update order status
        $processor->updateOrderStatus(5);

        // Continue processing
        $processor->continueNow = true;

        $processor->createAudit('PsTakePayment finished.', 20401);
    }
}
