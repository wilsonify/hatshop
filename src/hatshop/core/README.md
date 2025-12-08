# HatShop Core Library

This directory contains the shared core library that consolidates duplicate code from all chapter directories.

## Structure

```
core/
├── bootstrap.php          # Initialization script
├── composer.json          # Package definition
├── Config.php             # Centralized configuration management
├── Catalog.php            # Business logic for product catalog
├── CatalogAdmin.php       # Admin operations for catalog
├── ShoppingCart.php       # Shopping cart business logic
├── Orders.php             # Customer order management
├── DatabaseHandler.php    # PDO database wrapper
├── ErrorHandler.php       # Custom error handling
├── FeatureFlags.php       # Feature flag system
└── Presentation/          # Presentation layer components
    ├── Page.php                   # Smarty page wrapper
    ├── Link.php                   # URL utilities
    ├── DepartmentsListPlugin.php  # Departments display
    ├── DepartmentPlugin.php       # Department details
    ├── CategoriesListPlugin.php   # Categories display
    ├── ProductsListPlugin.php     # Products listing
    ├── ProductPlugin.php          # Product details
    └── SearchBoxPlugin.php        # Search functionality
```

## Feature Flags

The feature flag system allows enabling/disabling chapter-specific functionality:

| Feature | Environment Variable | Default | Chapter |
|---------|---------------------|---------|---------|
| `departments` | `HATSHOP_FEATURE_DEPARTMENTS` | true | 2 |
| `categories` | `HATSHOP_FEATURE_CATEGORIES` | true | 3 |
| `products` | `HATSHOP_FEATURE_PRODUCTS` | true | 4 |
| `product_details` | `HATSHOP_FEATURE_PRODUCT_DETAILS` | true | 4 |
| `pagination` | `HATSHOP_FEATURE_PAGINATION` | true | 4 |
| `search` | `HATSHOP_FEATURE_SEARCH` | true | 5 |
| `paypal` | `HATSHOP_FEATURE_PAYPAL` | false | 6 |
| `catalog_admin` | `HATSHOP_FEATURE_CATALOG_ADMIN` | false | 7 |
| `shopping_cart` | `HATSHOP_FEATURE_SHOPPING_CART` | false | 8 |
| `customer_orders` | `HATSHOP_FEATURE_CUSTOMER_ORDERS` | false | 9 |
| `product_recommendations` | `HATSHOP_FEATURE_PRODUCT_RECOMMENDATIONS` | false | 10 |
| `customer_details` | `HATSHOP_FEATURE_CUSTOMER_DETAILS` | false | 11 |
| `order_pipeline` | `HATSHOP_FEATURE_ORDER_PIPELINE` | false | 13-14 |
| `credit_card` | `HATSHOP_FEATURE_CREDIT_CARD` | false | 15 |
| `product_reviews` | `HATSHOP_FEATURE_PRODUCT_REVIEWS` | false | 16 |

### Using Chapter Levels

Set `HATSHOP_CHAPTER_LEVEL` to enable all features up to a specific chapter:

```bash
# Enable features for chapter 3 (departments + categories)
export HATSHOP_CHAPTER_LEVEL=3

# Enable features for chapter 5 (all catalog features + search)
export HATSHOP_CHAPTER_LEVEL=5

# Enable features for chapter 8 (catalog + paypal + admin + shopping cart)
export HATSHOP_CHAPTER_LEVEL=8
```

### Individual Feature Control

```bash
# Disable search while keeping other chapter 5 features
export HATSHOP_FEATURE_SEARCH=false
```

## Configuration

All configuration is managed through the `Config` class:

```php
use Hatshop\Core\Config;

// Get a configuration value
$productsPerPage = Config::get('products_per_page', 4);

// Set a configuration value (for testing)
Config::set('products_per_page', 10);
```

Environment variables are automatically loaded with the `HATSHOP_` prefix:

| Config Key | Environment Variable | Default |
|------------|---------------------|---------|
| `db_server` | `HATSHOP_DB_SERVER` | localhost |
| `db_username` | `HATSHOP_DB_USERNAME` | - |
| `db_password` | `HATSHOP_DB_PASSWORD` | - |
| `db_database` | `HATSHOP_DB_DATABASE` | hatshop |
| `debugging` | `HATSHOP_DEBUGGING` | true |
| `products_per_page` | `HATSHOP_PRODUCTS_PER_PAGE` | 4 |

## Usage in Chapter Applications

Each chapter can use the core library by including it in `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../../core"
        }
    ],
    "require": {
        "hatshop/core": "*"
    }
}
```

In `app_top.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Hatshop\Core\Config;
use Hatshop\Core\ErrorHandler;
use Hatshop\Core\FeatureFlags;

// Initialize
Config::initPaths(dirname(__DIR__));
Config::defineLegacyConstants();
ErrorHandler::init();

// Set chapter level for this instance
FeatureFlags::setChapterLevel(3);
```

## Migration from Chapter Directories

The core library consolidates code from:

- `c02/html/business/` - Basic database handler
- `c03/html/business/catalog.php` - Department functions
- `c04/html/business/catalog.php` - Products and pagination
- `c05/html/business/catalog.php` - Search functionality
- `c06/html/` - PayPal integration
- `c07/html/business/catalog.php` - Admin functions (CatalogAdmin)
- `c08/html/business/shopping_cart.php` - Shopping cart (ShoppingCart)
- `c09/html/business/orders.php` - Customer orders (Orders)
- `c02-c09/html/include/config.php` - Configuration
- `c02-c09/html/presentation/` - Smarty templates and plugins

Benefits:
1. Single source of truth for business logic
2. Feature flags for gradual feature enablement
3. Consistent error handling across all chapters
4. Easier testing with centralized configuration
5. Reduced code duplication (~80% reduction in business logic code)
