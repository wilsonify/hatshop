<?php
// Activate session
session_start();

// Include utility files
require_once 'include/config.php'; // NOSONAR - Config file must use require_once
require_once BUSINESS_DIR . 'error_handler.php'; // NOSONAR - Error handler must load early

// Sets the error handler
ErrorHandler::SetHandler();

// Load the page template
require_once PRESENTATION_DIR . 'page.php'; // NOSONAR - Template system requires require_once

// Load the database handler
require_once BUSINESS_DIR . 'database_handler.php'; // NOSONAR - Database handler requires require_once

// Load Business Tier
require_once BUSINESS_DIR . 'catalog.php'; // NOSONAR - Business tier requires require_once

