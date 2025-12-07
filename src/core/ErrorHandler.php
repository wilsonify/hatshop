<?php

namespace Hatshop\Core;

/**
 * Error handling for HatShop application.
 *
 * This class provides centralized error handling with support for:
 * - Custom error display in debug/production modes
 * - Error logging to files
 * - Error notification via email
 */
class ErrorHandler
{
    /** @var bool Whether error handler is initialized */
    private static bool $initialized = false;

    /**
     * Initialize the error handler.
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Set the user error handler
        set_error_handler([self::class, 'handler'], Config::get('error_types', E_ALL));

        self::$initialized = true;
    }

    /**
     * Custom error handler function.
     *
     * @param int $errNo Error number
     * @param string $errStr Error message
     * @param string $errFile File where error occurred
     * @param int $errLine Line number where error occurred
     * @return bool True to prevent default error handler
     */
    public static function handler(int $errNo, string $errStr, string $errFile, int $errLine): bool
    {
        $backtrace = self::getBacktraceStr();

        // Error message for display
        $errorMessage = "\nERRNO: {$errNo}\nTEXT: {$errStr}\n"
            . "LOCATION: {$errFile}, line {$errLine}\n\n"
            . "Showing backtrace:\n{$backtrace}\n";

        // Email error report if configured
        if (Config::get('send_error_mail')) {
            $adminEmail = Config::get('admin_error_mail');
            error_log($errorMessage, 1, $adminEmail, "From: " . Config::get('sendmail_from'));
        }

        // Log error to file if configured
        if (Config::get('log_errors')) {
            $logFile = Config::get('log_errors_file');
            error_log(date('Y-m-d H:i:s') . $errorMessage, 3, $logFile);
        }

        // Display error based on debug mode
        if (Config::get('debugging')) {
            echo '<pre>' . htmlspecialchars($errorMessage) . '</pre>';
        } else {
            echo Config::get('site_generic_error_message');
        }

        // Stop processing if warning is fatal or if it's a user error
        if (Config::get('is_warning_fatal') || $errNo === E_USER_ERROR) {
            exit();
        }

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Get a formatted backtrace string.
     *
     * @return string Formatted backtrace
     */
    private static function getBacktraceStr(): string
    {
        $backtrace = debug_backtrace();
        $result = '';

        // Remove first two entries (this function and handler)
        array_shift($backtrace);
        array_shift($backtrace);

        foreach ($backtrace as $i => $item) {
            $file = $item['file'] ?? '[internal function]';
            $line = $item['line'] ?? '?';
            $function = $item['function'] ?? '';
            $class = $item['class'] ?? '';
            $type = $item['type'] ?? '';

            $result .= "#{$i} {$file}({$line}): ";
            if ($class) {
                $result .= "{$class}{$type}";
            }
            $result .= "{$function}()\n";
        }

        return $result;
    }
}
