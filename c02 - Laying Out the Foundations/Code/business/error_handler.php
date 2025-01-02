<?php

class ErrorHandler
{
    private function __construct() {} // Private constructor to prevent instantiation

    public static function setHandler($errTypes = ERROR_TYPES)
    {
        return set_error_handler([self::class, 'handleError'], $errTypes);
    }

    public static function handleError($errNo, $errStr, $errFile, $errLine)
    {
        $backtrace = self::getBacktrace(2);
        $errorMessage = self::formatErrorMessage($errNo, $errStr, $errFile, $errLine, $backtrace);

        self::handleErrorLogging($errorMessage);
        self::handleErrorOutput($errNo, $errorMessage);

        if (self::isFatalError($errNo)) {
            self::terminateRequest($errorMessage);
        }
    }

    private static function formatErrorMessage($errNo, $errStr, $errFile, $errLine, $backtrace)
    {
        return "\nERRNO: $errNo\nTEXT: $errStr" .
               "\nLOCATION: $errFile, line $errLine, at " . date('F j, Y, g:i a') .
               "\nShowing backtrace:\n$backtrace\n\n";
    }

    private static function handleErrorLogging($errorMessage)
    {
        if (defined('SEND_ERROR_MAIL') && SEND_ERROR_MAIL) {
            error_log($errorMessage, 1, ADMIN_ERROR_MAIL, "From: " . SENDMAIL_FROM . "\r\nTo: " . ADMIN_ERROR_MAIL);
        }

        if (defined('LOG_ERRORS') && LOG_ERRORS) {
            error_log($errorMessage, 3, LOG_ERRORS_FILE);
        }
    }

    private static function handleErrorOutput($errNo, $errorMessage)
    {
        if (self::isNonFatalError($errNo) && defined('DEBUGGING') && DEBUGGING) {
            echo '<pre>' . $errorMessage . '</pre>';
        }
    }

    private static function isNonFatalError($errNo)
    {
        return ($errNo == E_WARNING && !(defined('IS_WARNING_FATAL') && IS_WARNING_FATAL)) ||
               in_array($errNo, [E_NOTICE, E_USER_NOTICE], true);
    }

    private static function isFatalError($errNo)
    {
        return !$errNo || !self::isNonFatalError($errNo);
    }

    private static function terminateRequest($errorMessage)
    {
        if (defined('DEBUGGING') && DEBUGGING) {
            echo '<pre>' . $errorMessage . '</pre>';
        } else {
            echo SITE_GENERIC_ERROR_MESSAGE;
        }
        exit;
    }

    public static function getBacktrace($irrelevantFirstEntries = 0)
    {
        $trace = debug_backtrace();
        $trace = array_slice($trace, $irrelevantFirstEntries);

        $result = array_map(function ($entry) {
            $class = $entry['class'] ?? '';
            $function = $entry['function'] ?? '';
            $args = isset($entry['args']) ? self::formatArguments($entry['args']) : '';
            $line = $entry['line'] ?? 'unknown';
            $file = $entry['file'] ?? 'unknown';

            return sprintf("%s%s(%s) # line %4d, file: %s", $class, $function, $args, $line, $file);
        }, $trace);

        return implode("\n", $result);
    }

    private static function formatArguments($args)
    {
        return implode(', ', array_map(function ($arg) {
            if (is_null($arg)) {
                return 'null';
            } elseif (is_array($arg)) {
                return 'Array[' . count($arg) . ']';
            } elseif (is_object($arg)) {
                return 'Object: ' . get_class($arg);
            } elseif (is_bool($arg)) {
                return $arg ? 'true' : 'false';
            } else {
                $string = htmlspecialchars(substr((string)@$arg, 0, 64));
                return strlen($arg) > 64 ? $string . '...' : $string;
            }
        }, $args));
    }
}
