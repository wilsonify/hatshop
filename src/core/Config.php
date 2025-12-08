<?php

namespace Hatshop\Core;

/**
 * Core configuration for HatShop application.
 *
 * This file centralizes all configuration settings that were previously
 * duplicated across chapter directories. Settings can be overridden via
 * environment variables prefixed with HATSHOP_.
 */
class Config
{
    /** @var Config|null Singleton instance */
    private static ?Config $instance = null;

    /** @var array<string, mixed> Configuration values */
    private array $config = [];

    private function __construct()
    {
        $this->loadDefaults();
        $this->loadEnvironmentOverrides();
    }

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /**
     * Get a configuration value.
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getInstance()->config[$key] ?? $default;
    }

    /**
     * Set a configuration value (useful for testing).
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public static function set(string $key, mixed $value): void
    {
        self::getInstance()->config[$key] = $value;
    }

    /**
     * Load default configuration values.
     */
    private function loadDefaults(): void
    {
        $this->config = [
            // Application paths (will be set based on SITE_ROOT)
            'site_root' => '',
            'presentation_dir' => '',
            'business_dir' => '',
            'template_dir' => '',
            'compile_dir' => '',
            'config_dir' => '',

            // Development settings
            'is_warning_fatal' => true,
            'debugging' => true,
            'error_types' => E_ALL,
            'site_generic_error_message' => '<h2>HatShop Error!</h2>',

            // Error logging
            'log_errors' => false,
            'log_errors_file' => '/var/tmp/hatshop_errors.log',

            // Error mailing
            'send_error_mail' => false,
            'admin_error_mail' => 'admin@example.com',
            'sendmail_from' => 'errors@example.com',

            // Database settings
            'db_persistency' => true,
            'db_server' => 'localhost',
            'db_username' => '',
            'db_password' => '',
            'db_database' => 'hatshop',

            // Server settings
            'http_server_host' => 'localhost',
            'http_server_port' => '80',
            'use_ssl' => true,

            // Product display settings
            'short_product_description_length' => 150,
            'products_per_page' => 4,

            // PayPal settings (Chapter 6)
            'paypal_url' => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
            'paypal_email' => '',
            'paypal_return_url' => '',
            'paypal_cancel_url' => '',
            'paypal_ipn_url' => '',
            'paypal_currency_code' => 'USD',

            // Admin settings (Chapter 7)
            'admin_username' => 'hatshopadmin',
            'admin_password' => 'hatshopadmin',
        ];
    }

    /**
     * Load configuration overrides from environment variables.
     */
    private function loadEnvironmentOverrides(): void
    {
        $envMappings = [
            'is_warning_fatal' => 'HATSHOP_IS_WARNING_FATAL',
            'debugging' => 'HATSHOP_DEBUGGING',
            'log_errors' => 'HATSHOP_LOG_ERRORS',
            'log_errors_file' => 'HATSHOP_LOG_ERRORS_FILE',
            'send_error_mail' => 'HATSHOP_SEND_ERROR_MAIL',
            'admin_error_mail' => 'HATSHOP_ADMIN_ERROR_MAIL',
            'sendmail_from' => 'HATSHOP_SENDMAIL_FROM',
            'db_server' => 'HATSHOP_DB_SERVER',
            'db_username' => 'HATSHOP_DB_USERNAME',
            'db_password' => 'HATSHOP_DB_PASSWORD',
            'db_database' => 'HATSHOP_DB_DATABASE',
            'http_server_host' => 'HATSHOP_HTTP_SERVER_HOST',
            'http_server_port' => 'HATSHOP_HTTP_SERVER_PORT',
            'use_ssl' => 'HATSHOP_USE_SSL',
            'short_product_description_length' => 'HATSHOP_SHORT_PRODUCT_DESCRIPTION_LENGTH',
            'products_per_page' => 'HATSHOP_PRODUCTS_PER_PAGE',
            // PayPal settings
            'paypal_url' => 'HATSHOP_PAYPAL_URL',
            'paypal_email' => 'HATSHOP_PAYPAL_EMAIL',
            'paypal_return_url' => 'HATSHOP_PAYPAL_RETURN_URL',
            'paypal_cancel_url' => 'HATSHOP_PAYPAL_CANCEL_URL',
            'paypal_ipn_url' => 'HATSHOP_PAYPAL_IPN_URL',
            'paypal_currency_code' => 'HATSHOP_PAYPAL_CURRENCY_CODE',
            // Admin settings
            'admin_username' => 'HATSHOP_ADMIN_USERNAME',
            'admin_password' => 'HATSHOP_ADMIN_PASSWORD',
        ];

        $booleanKeys = ['is_warning_fatal', 'debugging', 'log_errors', 'send_error_mail',
                        'db_persistency', 'use_ssl'];
        $integerKeys = ['short_product_description_length', 'products_per_page', 'http_server_port'];

        foreach ($envMappings as $configKey => $envKey) {
            $value = getenv($envKey);
            if ($value !== false) {
                if (in_array($configKey, $booleanKeys, true)) {
                    $this->config[$configKey] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif (in_array($configKey, $integerKeys, true)) {
                    $this->config[$configKey] = (int) $value;
                } else {
                    $this->config[$configKey] = $value;
                }
            }
        }

        // Configure sendmail
        if ($this->config['sendmail_from']) {
            ini_set('sendmail_from', $this->config['sendmail_from']);
        }
    }

    /**
     * Initialize paths based on the site root directory.
     *
     * @param string $siteRoot The root directory of the application
     */
    public static function initPaths(string $siteRoot): void
    {
        $instance = self::getInstance();
        $instance->config['site_root'] = $siteRoot;
        $instance->config['presentation_dir'] = $siteRoot . '/presentation/';
        $instance->config['business_dir'] = $siteRoot . '/business/';
        $instance->config['template_dir'] = $siteRoot . '/presentation/templates';
        $instance->config['compile_dir'] = $siteRoot . '/presentation/templates_c';
        $instance->config['config_dir'] = $siteRoot . '/include/configs';
    }

    /**
     * Get the PDO DSN string for database connection.
     *
     * @return string PDO DSN string
     */
    public static function getPdoDsn(): string
    {
        $instance = self::getInstance();
        return sprintf(
            'pgsql:host=%s;dbname=%s',
            $instance->config['db_server'],
            $instance->config['db_database']
        );
    }

    /**
     * Define legacy constants for backward compatibility.
     * Call this method after initPaths() for code that still uses constants.
     */
    public static function defineLegacyConstants(): void
    {
        $instance = self::getInstance();

        $constants = [
            'SITE_ROOT' => $instance->config['site_root'],
            'PRESENTATION_DIR' => $instance->config['presentation_dir'],
            'BUSINESS_DIR' => $instance->config['business_dir'],
            'TEMPLATE_DIR' => $instance->config['template_dir'],
            'COMPILE_DIR' => $instance->config['compile_dir'],
            'CONFIG_DIR' => $instance->config['config_dir'],
            'IS_WARNING_FATAL' => $instance->config['is_warning_fatal'],
            'DEBUGGING' => $instance->config['debugging'],
            'ERROR_TYPES' => $instance->config['error_types'],
            'SITE_GENERIC_ERROR_MESSAGE' => $instance->config['site_generic_error_message'],
            'LOG_ERRORS' => $instance->config['log_errors'],
            'LOG_ERRORS_FILE' => $instance->config['log_errors_file'],
            'SEND_ERROR_MAIL' => $instance->config['send_error_mail'],
            'ADMIN_ERROR_MAIL' => $instance->config['admin_error_mail'],
            'SENDMAIL_FROM' => $instance->config['sendmail_from'],
            'DB_PERSISTENCY' => $instance->config['db_persistency'],
            'DB_SERVER' => $instance->config['db_server'],
            'DB_USERNAME' => $instance->config['db_username'],
            'DB_PASSWORD' => $instance->config['db_password'],
            'DB_DATABASE' => $instance->config['db_database'],
            'PDO_DSN' => self::getPdoDsn(),
            'HTTP_SERVER_HOST' => $instance->config['http_server_host'],
            'HTTP_SERVER_PORT' => $instance->config['http_server_port'],
            'USE_SSL' => $instance->config['use_ssl'],
            'SHORT_PRODUCT_DESCRIPTION_LENGTH' => $instance->config['short_product_description_length'],
            'PRODUCTS_PER_PAGE' => $instance->config['products_per_page'],
            // PayPal constants
            'PAYPAL_URL' => $instance->config['paypal_url'],
            'PAYPAL_EMAIL' => $instance->config['paypal_email'],
            'PAYPAL_RETURN_URL' => $instance->config['paypal_return_url'],
            'PAYPAL_CANCEL_URL' => $instance->config['paypal_cancel_url'],
            'PAYPAL_IPN_URL' => $instance->config['paypal_ipn_url'],
            'PAYPAL_CURRENCY_CODE' => $instance->config['paypal_currency_code'],
            // Admin constants
            'ADMIN_USERNAME' => $instance->config['admin_username'],
            'ADMIN_PASSWORD' => $instance->config['admin_password'],
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    /**
     * Reset configuration to defaults (useful for testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
