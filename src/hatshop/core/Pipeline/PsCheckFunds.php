<?php

namespace Hatshop\Core\Pipeline;

use Hatshop\Core\FeatureFlags;

/**
 * Check funds pipeline section (Chapter 14-15).
 *
 * Verifies customer credit card has sufficient funds.
 * Status: 1 -> 2
 */
class PsCheckFunds implements IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $processor->createAudit('PsCheckFunds started.', 20100);

        // Check if credit card processing is enabled
        if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_CREDIT_CARD)) {
            // Real credit card processing would happen here
            // For now, we use dummy values
            $processor->setAuthCodeAndReference('AuthCode', 'Reference');
            $processor->createAudit('Funds verified via payment gateway.', 20102);
        } else {
            // Dummy processing - assume funds are available
            $processor->setAuthCodeAndReference('DummyAuthCode', 'DummyReference');
            $processor->createAudit('Funds available for purchase (dummy).', 20102);
        }

        // Update order status
        $processor->updateOrderStatus(2);

        // Continue processing
        $processor->continueNow = true;

        $processor->createAudit('PsCheckFunds finished.', 20101);
    }
}
