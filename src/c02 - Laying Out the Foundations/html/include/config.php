<?php
namespace Include;

const SITE_ROOT = dirname(dirname(__FILE__));

const PRESENTATION_DIR = SITE_ROOT . '/presentation/';

const BUSINESS_DIR = SITE_ROOT . '/business/';

const SMARTY_DIR = SITE_ROOT . '/vendor/smarty/smarty/libs/';

const TEMPLATE_DIR = PRESENTATION_DIR . '/templates';

const COMPILE_DIR = PRESENTATION_DIR . '/templates_c';

const CONFIG_DIR = SITE_ROOT . '/include/configs';

const IS_WARNING_FATAL = getenv('HATSHOP_IS_WARNING_FATAL', true);

const DEBUGGING = getenv('HATSHOP_DEBUGGING', true);

const LOG_ERRORS = getenv('HATSHOP_LOG_ERRORS', false);

const LOG_ERRORS_FILE = getenv('HATSHOP_LOG_ERRORS_FILE', '/var/tmp/hatshop_errors.log');

const ERROR_TYPES = E_ALL;

const SITE_GENERIC_ERROR_MESSAGE = '<h2>HatShop Error!</h2>';

const SEND_ERROR_MAIL = getenv('HATSHOP_SEND_ERROR_MAIL', false);

const ADMIN_ERROR_MAIL = getenv('HATSHOP_ADMIN_ERROR_MAIL', 'admin@example.com');

const SENDMAIL_FROM = getenv('HATSHOP_SENDMAIL_FROM', 'errors@example.com');

ini_set('sendmail_from', SENDMAIL_FROM);
