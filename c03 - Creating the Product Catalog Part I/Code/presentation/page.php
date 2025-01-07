<?php
// Reference Composer's autoload
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/smarty_plugins/02.function.load_departments_list.php';
require_once __DIR__ . '/smarty_plugins/01.modifier.prepare_link.php';

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

        // Register the custom plugin (function.load_departments_list.php)
        $this->registerPlugin('function', 'load_departments_list', 'smarty_function_load_departments_list');
        $this->registerPlugin('modifier', 'prepare_link', 'smarty_modifier_prepare_link');
    }
}








