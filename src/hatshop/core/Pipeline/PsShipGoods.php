<?php

namespace Hatshop\Core\Pipeline;

/**
 * Ship goods pipeline section (Chapter 14).
 *
 * Sends email to supplier to ship goods.
 * Status: 5 -> 6
 */
class PsShipGoods implements IPipelineSection
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

        $processor->createAudit('PsShipGoods started.', 20500);

        // Send mail to supplier
        $processor->mailSupplier('HatShop ship goods.', $this->getMailBody());

        $processor->createAudit('Ship goods e-mail sent to supplier.', 20502);

        // Update order status
        $processor->updateOrderStatus(6);

        // Note: This section does NOT continue automatically
        // Supplier must confirm shipment via admin interface

        $processor->createAudit('PsShipGoods finished.', 20501);
    }

    /**
     * Build the email body.
     *
     * @return string Email body
     */
    private function getMailBody(): string
    {
        $body = 'Payment has been received for the following goods:';
        $body .= "\n\n";
        $body .= $this->processor->getOrderAsString(false);
        $body .= "\n\n";
        $body .= 'Please ship to:';
        $body .= "\n\n";
        $body .= $this->processor->getCustomerAddressAsString();
        $body .= "\n\n";
        $body .= 'When goods have been shipped, please confirm via admin interface.';
        $body .= "\n\n";
        $body .= 'Order reference number: ' . $this->processor->orderInfo['order_id'];

        return $body;
    }
}
