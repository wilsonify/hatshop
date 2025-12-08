<?php
/**
 * HatShop Admin Entry Point
 *
 * This file handles the catalog administration interface.
 * It requires the catalog_admin feature to be enabled.
 */

// Include application bootstrap
require_once __DIR__ . '/include/app_top.php';

use Hatshop\Core\FeatureFlags;
use Hatshop\App\Presentation\Page;

// Check if catalog admin feature is enabled
if (!FeatureFlags::isEnabled(FeatureFlags::FEATURE_CATALOG_ADMIN)) {
    header('Location: index.php');
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create page object
$page = new Page();

// Assign feature flags to template
$page->assign('features', FeatureFlags::getAllFlags());

// Handle logout
if (isset($_GET['Page']) && $_GET['Page'] === 'Logout') {
    unset($_SESSION['admin_logged']);
}

// Check if administrator is logged in
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    // Admin is logged in - show admin interface
    $page->assign('pageMenuCell', 'admin_menu.tpl');

    // Determine which admin page to show
    $adminPage = $_GET['Page'] ?? 'Departments';

    switch ($adminPage) {
        case 'Departments':
            $page->assign('pageContentsCell', 'admin_departments.tpl');
            break;

        case 'Categories':
            $page->assign('pageContentsCell', 'admin_categories.tpl');
            break;

        case 'Products':
            $page->assign('pageContentsCell', 'admin_products.tpl');
            break;

        case 'ProductDetails':
            $page->assign('pageContentsCell', 'admin_product.tpl');
            break;

        default:
            $page->assign('pageContentsCell', 'admin_departments.tpl');
            break;
    }
} else {
    // Admin not logged in - show login page
    $page->assign('pageMenuCell', 'blank.tpl');
    $page->assign('pageContentsCell', 'admin_login.tpl');
}

// Display the admin page
$page->display('admin.tpl');

// Include application footer
require_once __DIR__ . '/include/app_bottom.php';
