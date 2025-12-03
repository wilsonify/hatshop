---
title: "c02 - Laying Out the Foundations"
weight: 2
---

# Chapter 02: Laying Out the Foundations

This chapter establishes the core architecture: error handling, templating with Smarty, and configuration management.

## Overview

Key features introduced:
- **Error Handler** - Custom error handling with stack traces
- **Smarty Templating** - Separation of PHP logic and HTML presentation
- **Configuration** - Centralized settings management

## Getting Started

```bash
cd "src/c02 - Laying Out the Foundations"
docker-compose up -d
```

Visit [http://localhost:8080](http://localhost:8080)

## Architecture

### Directory Structure

```
html/
├── index.php                 # Application entry point
├── include/
│   ├── config.php            # Configuration settings
│   ├── app_top.php           # Initialization
│   └── app_bottom.php        # Cleanup
├── business/
│   └── ErrorHandler.php      # Error handling class
├── presentation/
│   ├── page.php              # Smarty page class
│   ├── templates/            # Smarty templates
│   └── templates_c/          # Compiled templates
└── tests/                    # PHPUnit tests
```

### Error Handler

The `ErrorHandler` class provides:
- Custom error handling
- Exception catching
- Stack trace display
- Logging support

```php
namespace Business;

class ErrorHandler {
    public static function handler($errno, $errstr, $errfile, $errline) {
        // Format and log error
        // Display if in development mode
    }
}
```

### Configuration

`include/config.php` defines constants:

```php
// Site settings
define('SITE_ROOT', '/var/www/html');

// Database (not yet used)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// Error handling
define('IS_WARNING_FATAL', false);
define('DEBUGGING', true);
```

### Smarty Integration

The `Page` class extends Smarty:

```php
class Page extends Smarty {
    public function __construct() {
        parent::__construct();
        $this->setTemplateDir(TEMPLATE_DIR);
        $this->setCompileDir(COMPILE_DIR);
    }
}
```

## Testing

Unit tests are provided for the error handler:

```bash
cd html
composer install
./vendor/bin/phpunit
```

## Key Files

| File | Purpose |
|------|---------|
| `business/ErrorHandler.php` | Custom error handling |
| `include/config.php` | Configuration constants |
| `include/app_top.php` | Application bootstrap |
| `presentation/page.php` | Smarty base class |

## What You'll Learn

1. Setting up a PHP project structure
2. Implementing custom error handling
3. Using Smarty for templating
4. Configuration management patterns

## Next Steps

Continue to [Chapter 03: Product Catalog Part I]({{< relref "/docs/chapters/c03-catalog-part1" >}}) to add database connectivity and product display.
