<?php
// Load Smarty library and config files
require_once 'include/app_top.php';

// Load Smarty template file
$page = new Page();

// Redirect to HTTPS if not already using HTTPS
if (empty($_SERVER['HTTPS'])) {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url);
    exit;
}

// Track current page link for continue shopping
if (!isset($_GET['ProductID']) && !isset($_GET['CartAction'])) {
    $_SESSION['page_link'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
}

// Set template files based on page type
$pageContentsCell = 'first_page_contents.tpl';
$categoriesCell = 'blank.tpl';
$cartSummaryCell = 'blank.tpl';

// Determine page type based on URL parameters
if (isset($_GET['DepartmentID'])) {
    $pageContentsCell = 'department.tpl';
    $categoriesCell = 'categories_list.tpl';
} elseif (isset($_GET['Search'])) {
    $pageContentsCell = 'search_results.tpl';
} elseif (isset($_GET['ProductID'])) {
    $pageContentsCell = 'product.tpl';
} elseif (isset($_GET['CartAction'])) {
    $pageContentsCell = 'cart_details.tpl';
} else {
    $cartSummaryCell = 'cart_summary.tpl';
}

// Set customer login template
$customerLoginOrLogged = Customer::IsAuthenticated() ? 'customer_logged.tpl' : 'customer_login.tpl';

// Handle specific customer pages
$hide_boxes = false;
if (isset($_GET['Checkout'])) {
    $pageContentsCell = Customer::IsAuthenticated() ? 'checkout_info.tpl' : 'checkout_not_logged.tpl';
    $hide_boxes = true;
} elseif (isset($_GET['RegisterCustomer']) || isset($_GET['UpdateAccountDetails'])) {
    $pageContentsCell = 'customer_details.tpl';
} elseif (isset($_GET['UpdateAddressDetails'])) {
    $pageContentsCell = 'customer_address.tpl';
} elseif (isset($_GET['UpdateCreditCardDetails'])) {
    $pageContentsCell = 'customer_credit_card.tpl';
}

// Assign template variables
$page->assign('hide_boxes', $hide_boxes);
$page->assign('customerLoginOrLogged', $customerLoginOrLogged);
$page->assign('cartSummaryCell', $cartSummaryCell);
$page->assign('pageContentsCell', $pageContentsCell);
$page->assign('categoriesCell', $categoriesCell);

// Display the page
$page->display('index.tpl');

// Load app_bottom which closes the database connection
require_once 'include/app_bottom.php';
