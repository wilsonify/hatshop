<?php

namespace Hatshop\Core\Pipeline;

/**
 * Initial notification pipeline section (Chapter 14).
 *
 * Sends order confirmation email to customer.
 * Status: 0 -> 1
 */
class PsInitialNotification implements IPipelineSection
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

        $processor->createAudit('PsInitialNotification started.', 20000);

        // Send mail to customer
        $processor->mailCustomer('HatShop order received.', $this->getMailBody());

        $processor->createAudit('Notification e-mail sent to customer.', 20002);

        // Update order status
        $processor->updateOrderStatus(1);

        // Continue processing
        $processor->continueNow = true;

        $processor->createAudit('PsInitialNotification finished.', 20001);
    }

    /**
     * Build the email body.
     *
     * @return string Email body
     */
    private function getMailBody(): string
    {
        $body = 'Thank you for your order! ' .
                'The products you have ordered are as follows:';
        $body .= "\n\n";
        $body .= $this->processor->getOrderAsString(false);
        $body .= "\n\n";
        $body .= 'Your order will be shipped to:';
        $body .= "\n\n";
        $body .= $this->processor->getCustomerAddressAsString();
        $body .= "\n\n";
        $body .= 'Order reference number: ' . $this->processor->orderInfo['order_id'];
        $body .= "\n\n";
        $body .= 'You will receive a confirmation e-mail when this order ' .
                 'has been dispatched. Thank you for shopping at HatShop.com!';

        return $body;
    }
}
