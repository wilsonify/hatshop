<?php
/**
 * HatShop Application Bootstrap.
 *
 * This file initializes the application with all required dependencies.
 */

// Define site root
define('SITE_ROOT', dirname(__DIR__));

// Load composer autoloader (includes core library)
require_once SITE_ROOT . '/vendor/autoload.php';

use Hatshop\Core\Config;
use Hatshop\Core\ErrorHandler;
use Hatshop\Core\FeatureFlags;

// Initialize configuration
Config::initPaths(SITE_ROOT);
Config::defineLegacyConstants();

// Define Smarty directories
define('TEMPLATE_DIR', SITE_ROOT . '/presentation/templates');
define('COMPILE_DIR', SITE_ROOT . '/presentation/templates_c');
define('CONFIG_DIR', SITE_ROOT . '/include/configs');

// Create compile directory if it doesn't exist
if (!is_dir(COMPILE_DIR)) {
    mkdir(COMPILE_DIR, 0777, true);
}

// Initialize error handler
ErrorHandler::init();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load feature flags from environment or use chapter level
$chapterLevel = (int) (getenv('HATSHOP_CHAPTER_LEVEL') ?: 5);
if ($chapterLevel > 0) {
    FeatureFlags::setChapterLevel($chapterLevel);
}
