<?php

/**
 * HatShop Core Library Bootstrap.
 *
 * This file initializes the core library for use in chapter applications.
 * Include this file at the start of app_top.php or similar bootstrap files.
 */

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Hatshop\Core\Config;
use Hatshop\Core\ErrorHandler;

/**
 * Initialize the HatShop core library.
 *
 * @param string $siteRoot The root directory of the application
 */
function hatshopInit(string $siteRoot): void
{
    // Initialize configuration with site root
    Config::initPaths($siteRoot);

    // Define legacy constants for backward compatibility
    Config::defineLegacyConstants();

    // Initialize error handler
    ErrorHandler::init();

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
