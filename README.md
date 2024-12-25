Beginning PHP and PostgreSQL E-Commerce: From Novice to Professional
=======

#### Code Download Notes (Nov 27, 2006)

# Apress Source Code

This repository accompanies [*Beginning PHP and PostgreSQL E-Commerce*](http://www.apress.com/9781590596487) by Cristian Darie, Mihai Bucica, and Emilian Balanescu (Apress, 2007).

![Cover image](9781590596487.jpg)

# Get Started 
git clone the repository to your machine.

## Releases

Release v1.0 corresponds to the code in the published book, without corrections or updates.

## Contributions

See the file Contributing.md for more information on how you can contribute to this repository.


**Dear reader,**

Thank you for purchasing Beginning PHP and PostgreSQL E-Commerce: From Novice to Professional! 
  We really hope you're enjoying reading this book, and that it is effectively helping you build better web sites with PHP and PostgreSQL!
  Find the most recent version of this document, the latest errata notes and more details about the book at <a href="http://www.cristiandarie.ro/php-postgresql-ecommerce/">http://www.cristiandarie.ro/php-postgresql-ecommerce/</a>. 

The code has been tested with Apache 2, PHP 5, and PostgreSQL 8. 
  This archive contains the code for each chapter, in folders Chapters 02 through Chapter 17.
  Each chapter's folder contains a subfolder named Code, which contains the files you should have in your hatshop folder after you've finished the chapter. 
  For the chapters that require changes to the HatShop database, you'll find a subfolder named Database which contains the database scripts.




# Known Issues

## Problem
```
ERRNO: 2
TEXT: require_once(/var/www/html/libs/smarty/Smarty.class.php): Failed to open stream: No such file or directory
LOCATION: /var/www/html/presentation/page.php, line 3, at December 8, 2024, 3:16 pm
Showing backtrace:
require_once("/var/www/html/business/error_handler.php") # line    3, file: /var/www/html/presentation/page.php
require_once("/var/www/html/presentation/page.php") # line   13, file: /var/www/html/include/app_top.php
require_once("/var/www/html/include/app_top.php") # line    3, file: /var/www/html/index.php

```

## Solution
install and load smarty with composer
```php
require_once '/var/www/html/vendor/autoload.php';
use Smarty\Smarty;
```

## Problem
```
Fatal error: Uncaught Error: Call to undefined method Smarty\Smarty::Smarty() in /var/www/html/presentation/page.php:22 Stack trace: #0 /var/www/html/index.php(15): Page->__construct() #1 {main} thrown in /var/www/html/presentation/page.php on line 22
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
LOCATION: /var/www/html/presentation/page.php, line 28, at December 8, 2024, 3:20 pm
Showing backtrace:
Page.__construct() # line   15, file: /var/www/html/index.php
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
LOCATION: /var/www/html/business/database_handler.php, line 35, at December 8, 2024, 3:26 pm
Showing backtrace:
trigger_error("SQLSTATE[08006] [7] connection to server at "localhost" (::1), p...", "256") # line   35, file: /var/www/html/business/database_handler.php
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


