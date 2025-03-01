<?php

class ErrorHandler
{

    const DATE_FORMAT = 'F j, Y, g:i a'; // Date format for error messages

    private function __construct()
    { // Private constructor to prevent instantiation
    }

    public static function formatArguments(array $args)
    {
        return implode(', ', array_map([self::class, 'formatSingleArgument'], $args));
    }

    private static function formatSingleArgument($arg): string
    {
        if (is_null($arg)) {
            return self::formatNull();
        }

        if (is_bool($arg)) {
            return self::formatBoolean($arg);
        }

        if (is_array($arg)) {
            return self::formatArray($arg);
        }

        if (is_object($arg)) {
            return self::formatObject($arg);
        }

        if (is_string($arg)) {
            return self::formatString($arg);
        }

        return self::formatOther($arg);
    }

    private static function formatNull(): string
    {
        return 'null';
    }

    private static function formatBoolean(bool $arg): string
    {
        return $arg ? 'true' : 'false';
    }

    private static function formatArray(array $arg): string
    {
        return 'Array[' . count($arg) . ']';
    }

    private static function formatObject(object $arg): string
    {
        return 'Object: ' . get_class($arg);
    }

    private static function formatString(string $arg): string
    {
        return strlen($arg) > 64 ? '"' . substr($arg, 0, 61) . '..."' : '"' . $arg . '"';
    }

    private static function formatOther($arg): string
    {
        return '"' . (string) $arg . '"';
    }


    public static function setHandler($errTypes = E_ALL)
    {
        return set_error_handler([self::class, 'handleError'], $errTypes);
    }

    public static function handleError($errNo, $errStr, $errFile, $errLine)
    {
        $backtrace = self::getBacktrace(2);
        $errorMessage = self::formatErrorMessage($errNo, $errStr, $errFile, $errLine, $backtrace);

        self::handleErrorLogging($errorMessage);

        if (self::isNonFatalError($errNo)) {
            if (DEBUGGING) {
                echo '<pre>' . $errorMessage . '</pre>';
            }
            return true; // Non-fatal error handled.
        }

        // Fatal error handling
        if (DEBUGGING) {
            echo '<pre>' . $errorMessage . '</pre>';
        } else {
            echo defined('SITE_GENERIC_ERROR_MESSAGE') ? SITE_GENERIC_ERROR_MESSAGE : 'An error occurred.';
        }

        exit(1); // Exit for fatal errors.
    }

    public static function getBacktrace($irrelevantFirstEntries = 0)
    {
        $traceArray = array_slice(debug_backtrace(), $irrelevantFirstEntries);
        $result = [];

        foreach ($traceArray as $trace) {
            $class = $trace['class'] ?? '';
            $function = $trace['function'] ?? '';
            $line = $trace['line'] ?? 0;
            $file = $trace['file'] ?? 'unknown';
            $result[] = sprintf("%s.%s() # line %4d, file: %s", $class, $function, $line, $file);
        }

        return implode("\n", $result);
    }

    public static function formatErrorMessage($errNo, $errStr, $errFile, $errLine, $backtrace)
    {
        return sprintf(
            "ERRNO: %d\nTEXT: %s\nLOCATION: %s, line %d, at %s\nShowing backtrace:\n%s\n",
            $errNo,
            $errStr,
            $errFile,
            $errLine,
            date(self::DATE_FORMAT),
            $backtrace
        );
    }

    public static function isNonFatalError($errNo)
    {
        return in_array($errNo, [
            E_WARNING,
            E_NOTICE,
            E_USER_NOTICE,
            E_DEPRECATED,
            E_USER_DEPRECATED
        ]) || ($errNo == E_WARNING && defined('IS_WARNING_FATAL') && !IS_WARNING_FATAL);
    }

    public static function handleErrorLogging($errorMessage)
    {
        self::logErrorToFile($errorMessage);
        self::sendErrorMail($errorMessage);
    }

    private static function sendErrorMail($errorMessage)
    {
        if (defined('SEND_ERROR_MAIL') && SEND_ERROR_MAIL) {
            $recipient = defined('ADMIN_ERROR_MAIL') ? ADMIN_ERROR_MAIL : 'admin@example.com';
            $sender = defined('SENDMAIL_FROM') ? SENDMAIL_FROM : 'no-reply@example.com';
            error_log($errorMessage, 1, $recipient, 'From: ' . $sender);
        }
    }

    private static function logErrorToFile($errorMessage)
    {
        if (defined('LOG_ERRORS') && LOG_ERRORS) {
            $logFile = defined('LOG_ERRORS_FILE') ? LOG_ERRORS_FILE : '/tmp/error.log';
            error_log($errorMessage, 3, $logFile);
        }
    }
}
