<?php
// Reference Smarty library
require_once '/var/www/html/vendor/autoload.php';

// Require all PHP files in the smart_plugins directory
foreach (glob(__DIR__ . '/smarty_plugins/*.php') as $pluginFile) {
    require_once $pluginFile;
}

/* Class that extends Smarty, used to process and display Smarty
   files */
class Page extends Smarty
{
  // Class constructor
  public function __construct()
  {
    // Call Smarty's constructor
    parent::Smarty();

    // Change the default template directories
    $this->template_dir = TEMPLATE_DIR;
    $this->compile_dir = COMPILE_DIR;
    $this->config_dir = CONFIG_DIR;
    $this->plugins_dir[0] = SMARTY_DIR . 'plugins';
    $this->plugins_dir[1] = PRESENTATION_DIR . 'smarty_plugins';
  }
}
?>
