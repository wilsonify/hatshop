<?php
// Load classes and constants via namespaces
use Business\ErrorHandler;
use Presentation\Page;

use const Include\SITE_ROOT;
use const Include\PRESENTATION_DIR;
use const Include\BUSINESS_DIR;
use const Include\SMARTY_DIR;
use const Include\TEMPLATE_DIR;
use const Include\COMPILE_DIR;
use const Include\CONFIG_DIR;
use const Include\IS_WARNING_FATAL;
use const Include\DEBUGGING;
use const Include\LOG_ERRORS;
use const Include\LOG_ERRORS_FILE;
use const Include\ERROR_TYPES;
use const Include\SITE_GENERIC_ERROR_MESSAGE;
use const Include\SEND_ERROR_MAIL;
use const Include\ADMIN_ERROR_MAIL;
use const Include\SENDMAIL_FROM;

// Sets the error handler
ErrorHandler::SetHandler();

// Load Smarty template file
$page = new Page();

// Display the page
$page->display('index.tpl');
