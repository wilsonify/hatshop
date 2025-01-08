<?php
// Reference Smarty library
require_once '/var/www/html/vendor/autoload.php';

// Require all PHP files in the smart_plugins directory
foreach (glob(__DIR__ . '/smarty_plugins/*.php') as $pluginFile) {
    require_once $pluginFile;
}

use Smarty\Smarty;

/* Class that extends Smarty, used to process and display Smarty
   files */
class Page extends Smarty
{
  // Class constructor
  public function __construct()
  {
    // Call Smarty's constructor
    parent::__construct();

    // Change the default template directories
    $this->template_dir = TEMPLATE_DIR;
    $this->compile_dir = COMPILE_DIR;
    $this->config_dir = CONFIG_DIR;

    $this->registerPlugin('function', 'load_admin_cart', 'smarty_function_load_admin_cart');
    $this->registerPlugin('function', 'load_admin_categories', 'smarty_function_load_admin_categories');
    $this->registerPlugin('function', 'load_admin_departments', 'smarty_function_load_admin_departments');
    $this->registerPlugin('function', 'load_admin_login', 'smarty_function_load_admin_login');
    $this->registerPlugin('function', 'load_admin_order_details', 'smarty_function_load_admin_order_details');
    $this->registerPlugin('function', 'load_admin_orders', 'smarty_function_load_admin_orders');
    $this->registerPlugin('function', 'load_admin_product', 'smarty_function_load_admin_product');
    $this->registerPlugin('function', 'load_admin_products', 'smarty_function_load_admin_products');
    $this->registerPlugin('function', 'load_cart_details', 'smarty_function_load_cart_details');
    $this->registerPlugin('function', 'load_cart_summary', 'smarty_function_load_cart_summary');
    $this->registerPlugin('function', 'load_categories_list', 'smarty_function_load_categories_list');
    $this->registerPlugin('function', 'load_checkout_info', 'smarty_function_load_checkout_info');
    $this->registerPlugin('function', 'load_customer_address', 'smarty_function_load_customer_address');
    $this->registerPlugin('function', 'load_customer_credit_card', 'smarty_function_load_customer_credit_card');
    $this->registerPlugin('function', 'load_customer_details', 'smarty_function_load_customer_details');
    $this->registerPlugin('function', 'load_customer_logged', 'smarty_function_load_customer_logged');
    $this->registerPlugin('function', 'load_customer_login', 'smarty_function_load_customer_login');
    $this->registerPlugin('function', 'load_department', 'smarty_function_load_department');
    $this->registerPlugin('function', 'load_departments_list', 'smarty_function_load_departments_list');
    $this->registerPlugin('function', 'load_product', 'smarty_function_load_product');
    $this->registerPlugin('function', 'load_products_list', 'smarty_function_load_products_list');
    $this->registerPlugin('function', 'load_search_box', 'smarty_function_load_search_box');
    $this->registerPlugin('modifier', 'prepare_link', 'smarty_modifier_prepare_link');
    }
}

