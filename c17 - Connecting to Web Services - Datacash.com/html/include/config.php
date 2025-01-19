<?php
// SITE_ROOT contains the full path to the hatshop folder
define('SITE_ROOT', dirname(dirname(__FILE__)));

// Application directories
define('PRESENTATION_DIR', SITE_ROOT . '/presentation/');
define('BUSINESS_DIR', SITE_ROOT . '/business/');

// Settings needed to configure the Smarty template engine
define('SMARTY_DIR', SITE_ROOT . '/libs/smarty/');
define('TEMPLATE_DIR', PRESENTATION_DIR . '/templates');
define('COMPILE_DIR', PRESENTATION_DIR . '/templates_c');
define('CONFIG_DIR', SITE_ROOT . '/include/configs');

// Settings for developer exerience
define('IS_WARNING_FATAL', getenv('HATSHOP_IS_WARNING_FATAL',true));
define('DEBUGGING', getenv('HATSHOP_DEBUGGING',true));

// The error types to be reported
define('ERROR_TYPES', E_ALL);

// Settings about mailing the error messages to admin
define('SEND_ERROR_MAIL', getenv('HATSHOP_SEND_ERROR_MAIL',false));
define('ADMIN_ERROR_MAIL', getenv('HATSHOP_ADMIN_ERROR_MAIL','admin@example.com'));
define('SENDMAIL_FROM', getenv('HATSHOP_SENDMAIL_FROM','errors@example.com'));
ini_set('sendmail_from', SENDMAIL_FROM);

// By default we don't log errors to a file
define('LOG_ERRORS', getenv('HATSHOP_LOG_ERRORS', false));
define('LOG_ERRORS_FILE', getenv('HATSHOP_LOG_ERRORS_FILE', '/var/tmp/hatshop_errors.log'));


/* Generic error message to be displayed instead of debug info
   (when DEBUGGING is false) */
define('SITE_GENERIC_ERROR_MESSAGE', '<h2>HatShop Error!</h2>');

// Database login info
define('DB_PERSISTENCY', 'true');
define('DB_SERVER', getenv('HATSHOP_DB_SERVER'));
define('DB_USERNAME', getenv('HATSHOP_DB_USERNAME'));
define('DB_PASSWORD', getenv('HATSHOP_DB_PASSWORD'));
define('DB_DATABASE', getenv('HATSHOP_DB_DATABASE'));
define('PDO_DSN', 'pgsql:host=' . DB_SERVER . ';dbname=' . DB_DATABASE);

// Server HTTP port (can omit if the default 80 is used)
define('HTTP_SERVER_PORT', getenv('HATSHOP_HTTP_SERVER_PORT'));

// We enable and enforce SSL when this is set to anything else than 'no'
define('USE_SSL', true);

// Configure product lists display options
define('SHORT_PRODUCT_DESCRIPTION_LENGTH', 150);
define('PRODUCTS_PER_PAGE', 4);

// Administrator login information
define('ADMIN_USERNAME', getenv('HATSHOP_ADMIN_USERNAME','hatshopadmin'));
define('ADMIN_PASSWORD', getenv('HATSHOP_ADMIN_PASSWORD','hatshopadmin'));

// Shopping cart item types
define('GET_CART_PRODUCTS', 1);
define('GET_CART_SAVED_PRODUCTS', 2);

// Cart actions
define('ADD_PRODUCT', 1);
define('REMOVE_PRODUCT', 2);
define('UPDATE_PRODUCTS_QUANTITIES', 3);
define('SAVE_PRODUCT_FOR_LATER', 4);
define('MOVE_PRODUCT_TO_CART', 5);

// Random value used for hashing
define('HASH_PREFIX', 'K1-');

// Constant definitions for order handling related messages
define('ADMIN_EMAIL', 'Admin@example.com');
define('CUSTOMER_SERVICE_EMAIL', 'CustomerService@example.com');
define('ORDER_PROCESSOR_EMAIL', 'OrderProcessor@example.com');
define('SUPPLIER_EMAIL', 'Supplier@example.com');

// Constant definitions for datacash
define('DATACASH_URL', 'https://testserver.datacash.com/Transaction');
define('DATACASH_CLIENT', 'your account client number');
define('DATACASH_PASSWORD', 'your account password');

// Amazon E-Commerce Service
// define('AMAZON_METHOD', 'REST');
define('AMAZON_METHOD', 'SOAP');
define('AMAZON_WSDL',
  'https://webservices.amazon.com/AWSECommerceService/AWSECommerceService.wsdl');
define('AMAZON_REST_BASE_URL',
  'https://webservices.amazon.com/onca/xml?Service=AWSECommerceService');

// Set Amazon Access Key ID
define('AMAZON_ACCESS_KEY_ID', '[Your Access Key ID]');

// Set Amazon Associates ID
define('AMAZON_ASSOCIATES_ID', '[Your amazon associates ID]');

// Set Amazon request options
define('AMAZON_SEARCH_KEYWORDS', 'super hat');
define('AMAZON_SEARCH_NODE', 'Apparel');
define('AMAZON_RESPONSE_GROUPS', 'Request,Medium');

// Amazon.com department configuration options
define('AMAZON_DEPARTMENT_TITLE', 'Amazon Super Hats');
define('AMAZON_DEPARTMENT_DESCRIPTION',
       'Browse these super hats that Amazon.com offers');

