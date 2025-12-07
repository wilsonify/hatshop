<?php
/**
 * HatShop Unified Application Entry Point.
 *
 * This file replaces the chapter-specific index.php files and uses
 * feature flags to enable/disable functionality.
 */

// Load core bootstrap
require_once __DIR__ . '/include/app_top.php';

use Hatshop\Core\FeatureFlags;
use Hatshop\Core\Presentation\Page;

// Save current page link for navigation
if (!isset($_GET['ProductID'])) {
    $requestUri = getenv('REQUEST_URI') ?: '';
    $lastSlash = strrpos($requestUri, '/');
    if ($lastSlash !== false) {
        $_SESSION['page_link'] = substr($requestUri, $lastSlash + 1, strlen($requestUri) - 1);
    }
}

// Initialize the page
$page = new Page();

// Default template selections
$pageContentsCell = 'first_page_contents.tpl';
$categoriesCell = 'blank.tpl';

// Chapter 3+: Department navigation
if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_DEPARTMENTS) && isset($_GET['DepartmentID'])) {
    $pageContentsCell = 'department.tpl';

    // Chapter 3+: Category navigation
    if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATEGORIES)) {
        $categoriesCell = 'categories_list.tpl';
    }
}

// Chapter 5: Search functionality
if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH) && isset($_GET['Search'])) {
    $pageContentsCell = 'search_results.tpl';
}

// Chapter 4+: Product details
if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_PRODUCT_DETAILS) && isset($_GET['ProductID'])) {
    $pageContentsCell = 'product.tpl';
}

// Assign templates
$page->assign('pageContentsCell', $pageContentsCell);
$page->assign('categoriesCell', $categoriesCell);

// Pass feature flags to templates for conditional rendering
$page->assign('features', FeatureFlags::getAllFlags());

// Display the page
$page->display('index.tpl');

// Cleanup
require_once __DIR__ . '/include/app_bottom.php';
