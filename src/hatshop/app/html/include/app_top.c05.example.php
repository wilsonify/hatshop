<?php
/**
 * Chapter 5 Application Bootstrap - Refactored Version.
 *
 * This file demonstrates how to use the core library in a chapter application.
 * Set HATSHOP_CHAPTER_LEVEL=5 to enable all features through chapter 5.
 */

// Define site root
define('SITE_ROOT', dirname(__DIR__));

// Load composer autoloader (includes core library)
require_once SITE_ROOT . '/vendor/autoload.php';

use Hatshop\Core\Config;
use Hatshop\Core\ErrorHandler;
use Hatshop\Core\FeatureFlags;

// Initialize configuration with site root
Config::initPaths(SITE_ROOT);

// Define legacy constants for backward compatibility with existing code
Config::defineLegacyConstants();

// Initialize error handler
ErrorHandler::init();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable chapter 5 features (departments, categories, products, search)
FeatureFlags::setChapterLevel(5);

// Alternatively, enable individual features:
// FeatureFlags::enable(FeatureFlags::FEATURE_DEPARTMENTS);
// FeatureFlags::enable(FeatureFlags::FEATURE_CATEGORIES);
// FeatureFlags::enable(FeatureFlags::FEATURE_PRODUCTS);
// FeatureFlags::enable(FeatureFlags::FEATURE_PRODUCT_DETAILS);
// FeatureFlags::enable(FeatureFlags::FEATURE_PAGINATION);
// FeatureFlags::enable(FeatureFlags::FEATURE_SEARCH);
