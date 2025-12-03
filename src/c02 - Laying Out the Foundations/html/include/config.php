<?php
namespace Include;

// Use define() for constants that require function calls
define('Include\SITE_ROOT', dirname(__DIR__));

define('Include\PRESENTATION_DIR', SITE_ROOT . '/presentation/');

define('Include\BUSINESS_DIR', SITE_ROOT . '/business/');

define('Include\SMARTY_DIR', SITE_ROOT . '/vendor/smarty/smarty/libs/');

define('Include\TEMPLATE_DIR', PRESENTATION_DIR . 'templates');

define('Include\COMPILE_DIR', PRESENTATION_DIR . 'templates_c');

define('Include\CONFIG_DIR', SITE_ROOT . '/include/configs');

define('Include\IS_WARNING_FATAL', getenv('HATSHOP_IS_WARNING_FATAL') ?: true);

define('Include\DEBUGGING', getenv('HATSHOP_DEBUGGING') ?: true);

define('Include\LOG_ERRORS', getenv('HATSHOP_LOG_ERRORS') ?: false);

define('Include\LOG_ERRORS_FILE', getenv('HATSHOP_LOG_ERRORS_FILE') ?: '/var/tmp/hatshop_errors.log');

const ERROR_TYPES = E_ALL;

const SITE_GENERIC_ERROR_MESSAGE = '<h2>HatShop Error!</h2>';

define('Include\SEND_ERROR_MAIL', getenv('HATSHOP_SEND_ERROR_MAIL') ?: false);

define('Include\ADMIN_ERROR_MAIL', getenv('HATSHOP_ADMIN_ERROR_MAIL') ?: 'admin@example.com');

define('Include\SENDMAIL_FROM', getenv('HATSHOP_SENDMAIL_FROM') ?: 'errors@example.com');

ini_set('sendmail_from', SENDMAIL_FROM);
