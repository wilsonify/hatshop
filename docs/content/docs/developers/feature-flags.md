---
title: "Feature Flags"
weight: 5
bookToc: true
---

# Feature Flags

HatShop uses a feature flag system to enable or disable functionality at runtime. This allows you to:

- Deploy a single codebase that supports all chapter features
- Gradually roll out new features
- Disable features without code changes
- Test specific feature combinations

## Quick Start

### Using Chapter Levels

The simplest way to configure features is by setting a chapter level:

```bash
# Enable all features through Chapter 5 (search)
export HATSHOP_CHAPTER_LEVEL=5
```

### Individual Feature Control

Override specific features:

```bash
# Disable search while keeping other Chapter 5 features
export HATSHOP_FEATURE_SEARCH=false
```

## Available Features

| Feature | Environment Variable | Default | Chapter |
|---------|---------------------|---------|---------|
| Departments | `HATSHOP_FEATURE_DEPARTMENTS` | `true` | 2 |
| Categories | `HATSHOP_FEATURE_CATEGORIES` | `true` | 3 |
| Products | `HATSHOP_FEATURE_PRODUCTS` | `true` | 4 |
| Product Details | `HATSHOP_FEATURE_PRODUCT_DETAILS` | `true` | 4 |
| Pagination | `HATSHOP_FEATURE_PAGINATION` | `true` | 4 |
| Search | `HATSHOP_FEATURE_SEARCH` | `true` | 5 |
| Shopping Cart | `HATSHOP_FEATURE_SHOPPING_CART` | `false` | 6-8 |
| Customer Orders | `HATSHOP_FEATURE_CUSTOMER_ORDERS` | `false` | 9 |
| Product Recommendations | `HATSHOP_FEATURE_PRODUCT_RECOMMENDATIONS` | `false` | 10 |
| Customer Details | `HATSHOP_FEATURE_CUSTOMER_DETAILS` | `false` | 11 |
| Order Pipeline | `HATSHOP_FEATURE_ORDER_PIPELINE` | `false` | 13-14 |
| Credit Card | `HATSHOP_FEATURE_CREDIT_CARD` | `false` | 15 |
| Product Reviews | `HATSHOP_FEATURE_PRODUCT_REVIEWS` | `false` | 16 |

## Chapter Level Mappings

When you set `HATSHOP_CHAPTER_LEVEL`, the following features are enabled:

| Chapter Level | Enabled Features |
|---------------|------------------|
| 2 | departments |
| 3 | departments, categories |
| 4 | departments, categories, products, product_details, pagination |
| 5 | departments, categories, products, product_details, pagination, search |

## Using Feature Flags in Code

### PHP: Checking Feature Status

```php
use Hatshop\Core\FeatureFlags;

// Check if a feature is enabled
if (FeatureFlags::isEnabled(FeatureFlags::FEATURE_SEARCH)) {
    // Show search box
}

// Get all feature states
$features = FeatureFlags::getAllFlags();
// Returns: ['departments' => true, 'search' => true, ...]
```

### PHP: Programmatic Control (Testing)

```php
use Hatshop\Core\FeatureFlags;

// Enable a specific feature
FeatureFlags::enable(FeatureFlags::FEATURE_SEARCH);

// Disable a specific feature
FeatureFlags::disable(FeatureFlags::FEATURE_SHOPPING_CART);

// Set chapter level
FeatureFlags::setChapterLevel(4);

// Reset to defaults (useful in tests)
FeatureFlags::reset();
```

### Smarty Templates

Feature flags are passed to templates automatically:

```smarty
{if $features.search}
    {load_search_box assign="search_box"}
    {include file="search_box.tpl"}
{/if}
```

## Docker Compose Configuration

### docker-compose.yaml

```yaml
version: '3.8'
services:
  php:
    image: hatshop-php
    environment:
      # Enable features through Chapter 5
      - HATSHOP_CHAPTER_LEVEL=5
      
      # Or set individual features
      - HATSHOP_FEATURE_SEARCH=true
      - HATSHOP_FEATURE_SHOPPING_CART=false
      
      # Database configuration
      - HATSHOP_DB_SERVER=db
      - HATSHOP_DB_USERNAME=hatshop
      - HATSHOP_DB_PASSWORD=secret
      - HATSHOP_DB_DATABASE=hatshop
```

### .env File

```bash
# Feature configuration
HATSHOP_CHAPTER_LEVEL=5

# Individual overrides
HATSHOP_FEATURE_SEARCH=true
HATSHOP_FEATURE_SHOPPING_CART=false

# Application settings
HATSHOP_HTTP_SERVER_HOST=hatshop.example.com
HATSHOP_DEBUGGING=false
```

## Kubernetes Configuration

### ConfigMap

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: hatshop-features
data:
  HATSHOP_CHAPTER_LEVEL: "5"
  HATSHOP_FEATURE_SEARCH: "true"
  HATSHOP_FEATURE_SHOPPING_CART: "false"
```

### Deployment

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: hatshop
spec:
  template:
    spec:
      containers:
        - name: php
          envFrom:
            - configMapRef:
                name: hatshop-features
            - secretRef:
                name: hatshop-secrets
```

## Best Practices

### 1. Use Chapter Levels for Simplicity

For most deployments, set `HATSHOP_CHAPTER_LEVEL` rather than individual features:

```bash
# Good - simple and predictable
HATSHOP_CHAPTER_LEVEL=5

# Avoid - complex and error-prone
HATSHOP_FEATURE_DEPARTMENTS=true
HATSHOP_FEATURE_CATEGORIES=true
HATSHOP_FEATURE_PRODUCTS=true
# ... many more
```

### 2. Override Only When Necessary

Use individual feature flags to disable specific features:

```bash
HATSHOP_CHAPTER_LEVEL=5
HATSHOP_FEATURE_SEARCH=false  # Disable only search
```

### 3. Document Feature Dependencies

Some features depend on others. Enabling `products` without `departments` may cause issues.

### 4. Test Feature Combinations

In your test suite, test with various feature flag configurations:

```php
public function testSearchDisabled(): void
{
    FeatureFlags::disable(FeatureFlags::FEATURE_SEARCH);
    
    $result = Catalog::search('hat', false, 1, $pages);
    
    $this->assertEmpty($result['products']);
    FeatureFlags::reset();
}
```

## Troubleshooting

### Features Not Being Applied

1. Check environment variable names (case-sensitive)
2. Verify the container received the variables:
   ```bash
   docker exec hatshop-php env | grep HATSHOP
   ```
3. Ensure the application is using the core library

### Unexpected Behavior

1. Check if `HATSHOP_CHAPTER_LEVEL` is overriding individual flags
2. Review the precedence: chapter level sets defaults, individual flags override

### Debug Feature States

Add this to your application to debug:

```php
use Hatshop\Core\FeatureFlags;

error_log(print_r(FeatureFlags::getAllFlags(), true));
```
