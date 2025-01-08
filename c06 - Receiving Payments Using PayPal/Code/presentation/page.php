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

    $this->registerPlugin('function', 'load_categories_list', 'smarty_function_load_categories_list');
    $this->registerPlugin('function', 'load_department', 'smarty_function_load_department');
    $this->registerPlugin('function', 'load_departments_list', 'smarty_function_load_departments_list');
    $this->registerPlugin('function', 'load_product', 'smarty_function_load_product');
    $this->registerPlugin('function', 'load_products_list', 'smarty_function_load_products_list');
    $this->registerPlugin('function', 'load_search_box', 'smarty_function_load_search_box');
    $this->registerPlugin('modifier', 'prepare_link', 'smarty_modifier_prepare_link');
    }
}

