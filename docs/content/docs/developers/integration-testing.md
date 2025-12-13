---
title: "Integration Testing"
weight: 6
bookToc: true
---

# Integration Testing

HatShop uses PHPUnit for both unit and integration testing. Integration tests verify that components work together correctly and that the deployed application functions as expected.

## Test Suites

The project has two test suites defined in `src/hatshop/phpunit.xml`:

| Suite | Directory | Purpose |
|-------|-----------|---------|
| `core-unit` | `core/tests/unit` | Unit tests for individual classes |
| `core-integration` | `core/tests/integration` | Integration tests for component interactions |

## Running Tests

### Prerequisites

Install PHPUnit and dependencies:

```bash
cd src/hatshop/core
composer install
```

### Run All Tests

```bash
cd src/hatshop
./core/vendor/bin/phpunit --colors=always
```

### Run Only Integration Tests

```bash
cd src/hatshop
./core/vendor/bin/phpunit --testsuite core-integration --colors=always
```

### Run Specific Test Class

```bash
cd src/hatshop
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
```

## Integration Test Types

### Class Reference Tests

The `SmartyPluginClassReferencesTest` verifies that Smarty plugins use the correct class methods after refactoring. This prevents runtime errors from calling methods on the wrong classes.

**What it tests:**
- Each core class has its expected public static methods
- Smarty plugins import the correct classes
- Method calls use the appropriate class (not methods that were moved during refactoring)

**Example failure scenario:**
```php
// WRONG - method was moved to CatalogProducts
$product = Catalog::getProductDetails($productId);

// CORRECT
$product = CatalogProducts::getProductDetails($productId);
```

**Class method mapping:**

| Class | Methods |
|-------|---------|
| `Catalog` | `getDepartments`, `getDepartmentDetails`, `getCategoriesInDepartment`, `getCategoryDetails` |
| `CatalogProducts` | `getProductsInCategory`, `getProductsOnDepartmentDisplay`, `getProductsOnCatalogDisplay`, `getProductDetails`, `getRecommendations`, `getProductReviews` |
| `CatalogSearch` | `search` |
| `CatalogAdmin` | `getCategoryProducts`, `addProductToCategory`, `updateProduct`, `deleteProduct`, `setImage`, `setThumbnail`, etc. |
| `CategoryAdmin` | `getDepartmentCategories`, `addCategory`, `updateCategory`, `deleteCategory`, `getCategories` |
| `DepartmentAdmin` | `getDepartmentsWithDescriptions`, `addDepartment`, `updateDepartment`, `deleteDepartment` |

### Production Endpoint Tests

The `ProductionEndpointTest` makes HTTP requests to the deployed production application to verify it functions without fatal errors.

**What it tests:**
- Index page loads successfully
- Product detail pages work (validates `CatalogProducts::getProductDetails`)
- Department pages work
- Category pages work
- Search functionality works (validates `CatalogSearch::search`)

**Configuration:**

The base URL is defined in the test class:

```php
private const PROD_BASE_URL = 'https://hatshop.renewed-renaissance.com/prod';
```

To test a different environment, modify this constant or create environment-specific test classes.

## Writing Integration Tests

### Testing Class Methods Exist

```php
public function testCatalogProductsHasExpectedMethods(): void
{
    $this->assertClassHasMethods(
        'Hatshop\Core\CatalogProducts',
        ['getProductDetails', 'getProductsInCategory']
    );
}
```

### Testing File Contents

```php
public function testPluginUsesCorrectClass(): void
{
    $content = file_get_contents($pluginPath);
    
    $this->assertStringContainsString(
        'CatalogProducts::getProductDetails',
        $content,
        'Should call CatalogProducts::getProductDetails'
    );
    
    $this->assertStringNotContainsString(
        'Catalog::getProductDetails',
        $content,
        'Should NOT call Catalog::getProductDetails (method moved)'
    );
}
```

### Testing Production Endpoints

```php
public function testEndpointLoads(): void
{
    $response = file_get_contents($url);
    
    $this->assertStringNotContainsString(
        'Fatal error',
        $response,
        'Page should not contain fatal errors'
    );
}
```

## Continuous Integration

Integration tests should run:

1. **Before deployment** - Verify code changes are correct
2. **After deployment** - Verify the deployed application works
3. **On pull requests** - Prevent regressions from merging

### Pre-deployment Check

```bash
# Run local tests before building
./core/vendor/bin/phpunit --testsuite core-integration

# If tests pass, build and deploy
docker build -t hatshop:latest .
```

### Post-deployment Verification

```bash
# After deployment, run endpoint tests
./core/vendor/bin/phpunit --filter ProductionEndpointTest

# All tests should pass
# Tests: 5, Assertions: 18
```

## Troubleshooting

### Tests Pass Locally But Fail in Production

This usually means the deployed code differs from local code:

1. Check the image tag being used:
   ```bash
   kubectl get deployment php -n hatshop-prod -o jsonpath='{.spec.template.spec.containers[0].image}'
   ```

2. Verify the container has the correct code:
   ```bash
   kubectl exec -n hatshop-prod deployment/php -- cat /var/www/html/presentation/smarty_plugins/function.load_product.php | head -60
   ```

3. Rebuild and redeploy with the correct image tag:
   ```bash
   docker build --no-cache -t ghcr.io/wilsonify/hatshop:latest .
   kind load docker-image ghcr.io/wilsonify/hatshop:latest --name hatshop-prod
   kubectl rollout restart deployment/php -n hatshop-prod
   ```

### Network Errors in Endpoint Tests

If endpoint tests fail with connection errors:

1. Verify the deployment is running:
   ```bash
   kubectl get pods -n hatshop-prod
   ```

2. Check the Cloudflare tunnel:
   ```bash
   kubectl logs -n hatshop-prod deployment/cloudflared
   ```

3. Test connectivity manually:
   ```bash
   curl -v https://hatshop.renewed-renaissance.com/prod/index.php
   ```

### PHPUnit XML Warning

The warning about XML configuration validation can be ignored - it doesn't affect test execution:

```
Test results may not be as expected because the XML configuration file did not pass validation
```

To fix it, update the `phpunit.xml` schema reference to match your PHPUnit version.
