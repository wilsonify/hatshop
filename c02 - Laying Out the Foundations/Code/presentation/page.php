<?php
// Reference Composer's autoload
require_once '/var/www/html/vendor/autoload.php';

/* Class that extends Smarty, used to process and display Smarty
   files */
class Page extends \Smarty\Smarty
{
    // Class constructor
    public function __construct()
    {
        // Call Smarty's constructor (without using Smarty())
        parent::__construct();  // This is the correct constructor call

        // Change the default template directories
        $this->template_dir = TEMPLATE_DIR;
        $this->compile_dir = COMPILE_DIR;
        $this->config_dir = CONFIG_DIR;
    }
}
?>
