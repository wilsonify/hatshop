<?php
// Reference Smarty library
require_once '/var/www/html/vendor/autoload.php'; // NOSONAR - Composer autoloader must use require_once
require_once __DIR__ . '/smarty_plugins/01.modifier.prepare_link.php'; // NOSONAR - Plugin file loading
require_once __DIR__ . '/smarty_plugins/02.function.load_departments_list.php'; // NOSONAR - Plugin file loading
require_once __DIR__ . '/smarty_plugins/03.function.load_department.php'; // NOSONAR - Plugin file loading
require_once __DIR__ . '/smarty_plugins/04.function.load_categories_list.php'; // NOSONAR - Plugin file loading
require_once __DIR__ . '/smarty_plugins/05.function.load_products_list.php'; // NOSONAR - Plugin file loading
require_once __DIR__ . '/smarty_plugins/06.function.load_product.php'; // NOSONAR - Plugin file loading

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
    $this->registerPlugin('modifier', 'prepare_link', 'smarty_modifier_prepare_link');
  }
}

