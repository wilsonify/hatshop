<?php
// SITE_ROOT contains the full path to the hatshop folder
define('SITE_ROOT', dirname(dirname(__FILE__)));

// Application directories
define('PRESENTATION_DIR', SITE_ROOT . '/presentation/');
define('BUSINESS_DIR', SITE_ROOT . '/business/');

// Settings needed to configure the Smarty template engine
define('SMARTY_DIR', SITE_ROOT . '/vendor/smarty/smarty/libs/');
define('TEMPLATE_DIR', PRESENTATION_DIR . '/templates');
define('COMPILE_DIR', PRESENTATION_DIR . '/templates_c');
define('CONFIG_DIR', SITE_ROOT . '/include/configs');

// Settings for developer exerience
define('IS_WARNING_FATAL', getenv('HATSHOP_IS_WARNING_FATAL',true));
define('DEBUGGING', getenv('HATSHOP_DEBUGGING',true));
define('LOG_ERRORS', getenv('HATSHOP_LOG_ERRORS', false));
define('LOG_ERRORS_FILE', getenv('HATSHOP_LOG_ERRORS_FILE', '/var/tmp/hatshop_errors.log'));

// The error types to be reported
define('ERROR_TYPES', E_ALL);

// Generic error message to be displayed instead of debug info (when DEBUGGING is false) */
define('SITE_GENERIC_ERROR_MESSAGE', '<h2>HatShop Error!</h2>');

// Settings about mailing the error messages to admin
define('SEND_ERROR_MAIL', getenv('HATSHOP_SEND_ERROR_MAIL',false));
define('ADMIN_ERROR_MAIL', getenv('HATSHOP_ADMIN_ERROR_MAIL','admin@example.com'));
define('SENDMAIL_FROM', getenv('HATSHOP_SENDMAIL_FROM','errors@example.com'));
ini_set('sendmail_from', SENDMAIL_FROM);



