<?php

namespace Hatshop\Core\Pipeline;

/**
 * Dummy pipeline section for testing (Chapter 13).
 *
 * Used when order pipeline is disabled or for testing purposes.
 */
class PsDummy implements IPipelineSection
{
    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $processor->createAudit('PsDummy started.', 99999);
        $processor->createAudit('Customer: ' . $processor->customerInfo['name'], 99999);
        $processor->createAudit('Order subtotal: ' . $processor->orderInfo['total_amount'], 99999);
        $processor->mailAdmin('Test.', 'Test mail from PsDummy.', 99999);
        $processor->createAudit('PsDummy finished.', 99999);
    }
}
