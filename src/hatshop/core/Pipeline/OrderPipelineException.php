<?php

namespace Hatshop\Core\Pipeline;

use Exception;

/**
 * Exception thrown when order pipeline processing fails.
 */
class OrderPipelineException extends Exception
{
    /**
     * Create exception for completed order.
     */
    public static function orderCompleted(): self
    {
        return new self('Order has already been completed.');
    }

    /**
     * Create exception for unknown pipeline section.
     */
    public static function unknownSection(): self
    {
        return new self('Unknown pipeline section requested.');
    }

    /**
     * Create exception for mail failure to admin.
     *
     * @param string $body Email body that failed to send
     */
    public static function mailAdminFailed(string $body): self
    {
        return new self("Failed sending mail to administrator:\n" . $body);
    }

    /**
     * Create exception for mail failure to customer.
     */
    public static function mailCustomerFailed(): self
    {
        return new self('Unable to send e-mail to customer.');
    }

    /**
     * Create exception for mail failure to supplier.
     */
    public static function mailSupplierFailed(): self
    {
        return new self('Unable to send email to supplier.');
    }

    /**
     * Create exception for general processing error.
     *
     * @param string $message Error message
     */
    public static function processingError(string $message): self
    {
        return new self('Order processing error: ' . $message);
    }
}
