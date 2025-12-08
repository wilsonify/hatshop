<?php

namespace Hatshop\Core\Pipeline;

/**
 * Check stock pipeline section (Chapter 14).
 *
 * Sends email to supplier to check stock availability.
 * Status: 2 -> 3
 */
class PsCheckStock implements IPipelineSection
{
    private OrderProcessor $processor;

    /**
     * Process this pipeline section.
     *
     * @param OrderProcessor $processor The order processor instance
     */
    public function process(OrderProcessor $processor): void
    {
        $this->processor = $processor;

        $processor->createAudit('PsCheckStock started.', 20200);

        // Send mail to supplier
        $processor->mailSupplier('HatShop stock check.', $this->getMailBody());

        $processor->createAudit('Notification email sent to supplier.', 20202);

        // Update order status
        $processor->updateOrderStatus(3);

        // Note: This section does NOT continue automatically
        // Supplier must confirm stock via admin interface

        $processor->createAudit('PsCheckStock finished.', 20201);
    }

    /**
     * Build the email body.
     *
     * @return string Email body
     */
    private function getMailBody(): string
    {
        $body = 'The following goods have been ordered:';
        $body .= "\n\n";
        $body .= $this->processor->getOrderAsString(false);
        $body .= "\n\n";
        $body .= 'Please check availability and confirm via admin interface.';
        $body .= "\n\n";
        $body .= 'Order reference number: ' . $this->processor->orderInfo['order_id'];

        return $body;
    }
}
