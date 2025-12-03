<?php
// Include utility files
require_once 'include/config.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading
require_once BUSINESS_DIR . 'error_handler.php'; // NOSONAR

// Sets the error handler
ErrorHandler::SetHandler();

// Load the page template
require_once PRESENTATION_DIR . 'page.php'; // NOSONAR

// Load the database handler
require_once BUSINESS_DIR . 'database_handler.php'; // NOSONAR

// Load Business Tier
require_once BUSINESS_DIR . 'catalog.php'; // NOSONAR

