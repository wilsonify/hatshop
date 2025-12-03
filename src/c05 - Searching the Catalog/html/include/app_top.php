<?php
/**
 * Application Bootstrap File
 *
 * This file bootstraps the application by:
 * - Loading configuration constants
 * - Initializing Composer autoloader for PSR-4 namespaced classes
 * - Setting up the error handler
 * - Making business and presentation tier classes available
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @phpcs:disable SlevomatCodingStandard.Namespaces.UseOnlyWhitelistedNamespaces
 */

// Activate session
session_start();

// Include configuration - must load first as it defines directory constants
// This file is not autoloaded because it only defines constants, not classes
// @phpcs:disable SlevomatCodingStandard.Files.TypeNameMatchesFileName
require_once 'include/config.php'; // NOSONAR - config.php only defines constants, cannot use namespaces
// @phpcs:enable SlevomatCodingStandard.Files.TypeNameMatchesFileName

// Load Composer autoloader for PSR-4 namespaced classes
require_once SITE_ROOT . '/vendor/autoload.php';

// Import namespaced classes using the 'use' keyword
use Hatshop\Business\ErrorHandler;
use Hatshop\Business\Catalog;
use Hatshop\Business\DatabaseHandler;
use Hatshop\Presentation\Page;

// Sets the error handler
ErrorHandler::setHandler();

// Business and Presentation tier classes are now available via autoloading
// No need for explicit require_once statements
