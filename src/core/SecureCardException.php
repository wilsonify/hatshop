<?php

namespace Hatshop\Core;

/**
 * Exception thrown when secure card operations fail.
 *
 * Chapter 12: Storing Customer Orders - Credit Card Security
 */
class SecureCardException extends \RuntimeException
{
    /**
     * Create a new SecureCardException.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
