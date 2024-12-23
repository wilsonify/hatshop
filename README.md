# Apress Source Code

This repository accompanies [*Beginning PHP and PostgreSQL E-Commerce*](http://www.apress.com/9781590596487) by Cristian Darie, Mihai Bucica, and Emilian Balanescu (Apress, 2007).

![Cover image](9781590596487.jpg)

Download the files as a zip using the green button, or clone the repository to your machine using Git.

## Releases

Release v1.0 corresponds to the code in the published book, without corrections or updates.

## Contributions

See the file Contributing.md for more information on how you can contribute to this repository.


# Known Issues

## Problem
```
ERRNO: 2
TEXT: require_once(/var/www/hatshop/libs/smarty/Smarty.class.php): Failed to open stream: No such file or directory
LOCATION: /var/www/hatshop/presentation/page.php, line 3, at December 8, 2024, 3:16 pm
Showing backtrace:
require_once("/var/www/hatshop/business/error_handler.php") # line    3, file: /var/www/hatshop/presentation/page.php
require_once("/var/www/hatshop/presentation/page.php") # line   13, file: /var/www/hatshop/include/app_top.php
require_once("/var/www/hatshop/include/app_top.php") # line    3, file: /var/www/hatshop/index.php

```

## Solution
install and load smarty with composer
```php
require_once '/var/www/hatshop/vendor/autoload.php';
use Smarty\Smarty;
```

## Problem
```
Fatal error: Uncaught Error: Call to undefined method Smarty\Smarty::Smarty() in /var/www/hatshop/presentation/page.php:22 Stack trace: #0 /var/www/hatshop/index.php(15): Page->__construct() #1 {main} thrown in /var/www/hatshop/presentation/page.php on line 22
```

## Solution

Call Smarty's constructor
```
parent::__construct();
```

## Problem

```
ERRNO: 8192
TEXT: Creation of dynamic property Page::$plugins_dir is deprecated
LOCATION: /var/www/hatshop/presentation/page.php, line 28, at December 8, 2024, 3:20 pm
Showing backtrace:
Page.__construct() # line   15, file: /var/www/hatshop/index.php
```

## Solution
use registerPlugin to explicitly register each plugin
```php
require_once __DIR__ . '/smarty_plugins/function.load_departments_list.php';
require_once __DIR__ . '/smarty_plugins/modifier.prepare_link.php';
...
// Register the custom plugin
$this->registerPlugin('function', 'load_departments_list', 'smarty_function_load_departments_list');
$this->registerPlugin('modifier', 'prepare_link', 'smarty_modifier_prepare_link');
```

## Problem

```
ERRNO: 256
TEXT: SQLSTATE[08006] [7] connection to server at "localhost" (::1), port 5432 failed: Connection refused
	Is the server running on that host and accepting TCP/IP connections?
connection to server at "localhost" (127.0.0.1), port 5432 failed: Connection refused
	Is the server running on that host and accepting TCP/IP connections?
LOCATION: /var/www/hatshop/business/database_handler.php, line 35, at December 8, 2024, 3:26 pm
Showing backtrace:
trigger_error("SQLSTATE[08006] [7] connection to server at "localhost" (::1), p...", "256") # line   35, file: /var/www/hatshop/business/database_handler.php
```

## Solution
set connection string from environment variables
```
define('DB_SERVER', getenv('HATSHOP_DB_SERVER'));
define('DB_USERNAME', getenv('HATSHOP_DB_USERNAME'));
define('DB_PASSWORD', getenv('HATSHOP_DB_PASSWORD'));
define('DB_DATABASE', getenv('HATSHOP_DB_DATABASE'));
```

```
docker run ...  --env-file ./.env ...
```