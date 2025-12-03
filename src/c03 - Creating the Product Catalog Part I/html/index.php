<?php
// Load Smarty library and config files
require_once 'include/app_top.php'; // NOSONAR - Legacy PHP application without PSR-4 autoloading

// Load Smarty template file
$page = new Page();

// Display the page
$page->display('index.tpl');

// Load app_bottom which closes the database connection
require_once 'include/app_bottom.php'; // NOSONAR

