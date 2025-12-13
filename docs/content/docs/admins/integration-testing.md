---
title: "Integration Testing"
weight: 8
bookToc: true
---

# Integration Testing for Administrators

This guide covers how to use integration tests to verify deployments and catch issues before they affect users.

## Overview

Integration tests verify that the deployed HatShop application functions correctly. Running these tests after deployment ensures:

- No fatal PHP errors on critical pages
- Core functionality works (browsing, search, product details)
- Code refactoring hasn't broken existing features

## Quick Reference

### Run All Integration Tests

```bash
cd src/hatshop
./core/vendor/bin/phpunit --testsuite core-integration --colors=always
```

### Run Production Endpoint Tests Only

```bash
cd src/hatshop
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
```

### Expected Output (Success)

```
PHPUnit 10.5.60 by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: 00:04.820, Memory: 10.00 MB

OK, but there were issues!
Tests: 5, Assertions: 18, PHPUnit Warnings: 1.
```

## Pre-Deployment Checklist

Before deploying to production, run the full test suite:

```bash
# 1. Run all tests
cd src/hatshop
./core/vendor/bin/phpunit --colors=always

# Expected: All tests pass
# Tests: 171, Assertions: 440
```

If tests fail, **do not deploy**. Fix the issues first.

## Post-Deployment Verification

After deploying, verify the production environment:

```bash
# 1. Wait for deployment to complete
kubectl rollout status deployment/php -n hatshop-prod --timeout=90s

# 2. Run endpoint tests
cd src/hatshop
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
```

### What the Endpoint Tests Verify

| Test | URL Pattern | Verifies |
|------|-------------|----------|
| `testIndexPageLoads` | `/index.php` | Main page loads without errors |
| `testProductDetailPageLoads` | `/index.php?ProductID=7` | Product details work |
| `testDepartmentPageLoads` | `/index.php?DepartmentID=1` | Department browsing works |
| `testCategoryPageLoads` | `/index.php?DepartmentID=1&CategoryID=1` | Category browsing works |
| `testSearchPageLoads` | `/index.php?SearchResults=hat` | Search functionality works |

## Handling Test Failures

### Failure: Fatal Error in Production

If you see output like:

```
FAILURES!
Tests: 5, Assertions: 16, Failures: 1

1) ProductionEndpointTest::testProductDetailPageLoads
Product detail page should not contain fatal errors
Failed asserting that '...<b>Fatal error</b>: Uncaught Error: Call to undefined method...'
```

**Diagnosis:**

1. The deployed code has a bug
2. The fix exists locally but wasn't deployed
3. The wrong Docker image was loaded

**Resolution:**

```bash
# 1. Verify local code is correct
head -55 src/hatshop/app/html/presentation/smarty_plugins/function.load_product.php

# 2. Check what's in the container
kubectl exec -n hatshop-prod deployment/php -- head -55 /var/www/html/presentation/smarty_plugins/function.load_product.php

# 3. If different, rebuild and redeploy
docker build --no-cache -t ghcr.io/wilsonify/hatshop:latest src/hatshop/
kind load docker-image ghcr.io/wilsonify/hatshop:latest --name hatshop-prod
kubectl rollout restart deployment/php -n hatshop-prod

# 4. Wait and re-test
kubectl rollout status deployment/php -n hatshop-prod --timeout=90s
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
```

### Failure: Connection Refused

If tests fail with network errors:

```bash
# Check pods are running
kubectl get pods -n hatshop-prod

# Check cloudflared tunnel
kubectl logs -n hatshop-prod deployment/cloudflared --tail=50

# Manual connectivity test
curl -I https://hatshop.renewed-renaissance.com/prod/index.php
```

### Image Not Updated in KIND

KIND clusters can cache old images. Force update:

```bash
# 1. Check current image in KIND
docker exec hatshop-prod-control-plane crictl images | grep hatshop

# 2. Verify deployment uses correct image name
kubectl get deployment php -n hatshop-prod -o jsonpath='{.spec.template.spec.containers[0].image}'
# Output: ghcr.io/wilsonify/hatshop:latest

# 3. Tag and load with correct name
docker tag hatshop:latest ghcr.io/wilsonify/hatshop:latest
kind load docker-image ghcr.io/wilsonify/hatshop:latest --name hatshop-prod

# 4. Force pod recreation
kubectl delete pods -n hatshop-prod -l app=php

# 5. Verify new pods have correct code
kubectl exec -n hatshop-prod deployment/php -- head -5 /var/www/html/presentation/smarty_plugins/function.load_product.php
```

## Deployment Workflow with Testing

### Standard Deployment Process

```bash
# 1. Pull latest code
git pull origin master

# 2. Run local tests
cd src/hatshop
./core/vendor/bin/phpunit --colors=always
# Verify: All tests pass

# 3. Build Docker image
docker build --no-cache -t ghcr.io/wilsonify/hatshop:latest .

# 4. Load into KIND
kind load docker-image ghcr.io/wilsonify/hatshop:latest --name hatshop-prod

# 5. Deploy
kubectl rollout restart deployment/php -n hatshop-prod
kubectl rollout status deployment/php -n hatshop-prod --timeout=90s

# 6. Verify deployment
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
# Verify: All endpoint tests pass

# 7. Manual smoke test
curl https://hatshop.renewed-renaissance.com/prod/index.php | grep -i "hatshop"
```

### Rollback on Failure

If post-deployment tests fail:

```bash
# 1. Check deployment history
kubectl rollout history deployment/php -n hatshop-prod

# 2. Rollback to previous version
kubectl rollout undo deployment/php -n hatshop-prod

# 3. Wait for rollback
kubectl rollout status deployment/php -n hatshop-prod --timeout=90s

# 4. Verify rollback fixed the issue
./core/vendor/bin/phpunit --filter ProductionEndpointTest --colors=always
```

## Test Configuration

### Changing the Test URL

The production URL is configured in `src/hatshop/core/tests/integration/ProductionEndpointTest.php`:

```php
private const PROD_BASE_URL = 'https://hatshop.renewed-renaissance.com/prod';
```

To test a staging environment, either:

1. Modify the constant temporarily
2. Create a separate `StagingEndpointTest.php` with the staging URL

### Adding New Endpoint Tests

If you add new pages to HatShop, add corresponding tests:

```php
public function testNewFeaturePageLoads(): void
{
    $response = $this->fetchUrl(self::PROD_BASE_URL . '/index.php?NewFeature=1');

    $this->assertNotFalse($response, 'Should be able to fetch new feature page');
    $this->assertStringNotContainsString(
        'Fatal error',
        $response,
        'New feature page should not contain fatal errors'
    );
}
```

## Monitoring and Alerts

Consider setting up automated testing:

1. **Scheduled tests** - Run endpoint tests every hour via cron
2. **Deployment hooks** - Automatically run tests after each deployment
3. **Alerting** - Notify administrators when tests fail

Example cron job:

```bash
# Run endpoint tests every hour, alert on failure
0 * * * * cd /path/to/hatshop/src/hatshop && ./core/vendor/bin/phpunit --filter ProductionEndpointTest --no-interaction 2>&1 | mail -s "HatShop Test Results" admin@example.com
```
