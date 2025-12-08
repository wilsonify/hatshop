<?php

namespace Hatshop\Core\Pipeline;

/**
 * Final notification pipeline section (Chapter 14).
 *
 * Sends dispatch confirmation email to customer.
 * Status: 7 -> 8 (completed)
 */
class PsFinalNotification implements IPipelineSection
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

        $processor->createAudit('PsFinalNotification started.', 20700);

        // Send mail to customer
        $processor->mailCustomer('HatShop order dispatched.', $this->getMailBody());

        $processor->createAudit('Dispatch e-mail sent to customer.', 20702);

        // Update order status to completed
        $processor->updateOrderStatus(8);

        // Note: Processing ends here - order is complete

        $processor->createAudit('PsFinalNotification finished.', 20701);
    }

    /**
     * Build the email body.
     *
     * @return string Email body
     */
    private function getMailBody(): string
    {
        $body = 'Your order has now been dispatched! ' .
                'The following products have been shipped:';
        $body .= "\n\n";
        $body .= $this->processor->getOrderAsString(false);
        $body .= "\n\n";
        $body .= 'Your order has been shipped to:';
        $body .= "\n\n";
        $body .= $this->processor->getCustomerAddressAsString();
        $body .= "\n\n";
        $body .= 'Order reference number: ' . $this->processor->orderInfo['order_id'];
        $body .= "\n\n";
        $body .= 'Thank you for shopping at HatShop.com!';

        return $body;
    }
}
