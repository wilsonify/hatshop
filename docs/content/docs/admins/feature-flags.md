---
title: "Feature Flags Configuration"
weight: 5
bookToc: true
---

# Feature Flags Configuration

HatShop uses feature flags to enable or disable functionality without code changes. This guide covers how to configure feature flags for different deployment environments.

## Overview

Feature flags allow you to:

- Deploy a unified codebase across all environments
- Enable features progressively as they become ready
- Disable problematic features without redeployment
- A/B test new functionality

## Configuration Methods

### Method 1: Chapter Level (Recommended)

The simplest approach is to set a chapter level that enables all features up to that chapter:

```bash
export HATSHOP_CHAPTER_LEVEL=5
```

| Chapter Level | Features Enabled |
|---------------|------------------|
| 2 | Department navigation |
| 3 | + Category navigation |
| 4 | + Product listing, details, pagination |
| 5 | + Search functionality |

### Method 2: Individual Feature Flags

For fine-grained control, set individual feature flags:

```bash
export HATSHOP_FEATURE_DEPARTMENTS=true
export HATSHOP_FEATURE_CATEGORIES=true
export HATSHOP_FEATURE_PRODUCTS=true
export HATSHOP_FEATURE_SEARCH=false
```

### Method 3: Combination

Use chapter level as a baseline and override specific features:

```bash
export HATSHOP_CHAPTER_LEVEL=5
export HATSHOP_FEATURE_SEARCH=false  # Disable only search
```

## Available Feature Flags

### Core Features (Chapters 2-5)

| Variable | Description | Default |
|----------|-------------|---------|
| `HATSHOP_FEATURE_DEPARTMENTS` | Department listing and navigation | `true` |
| `HATSHOP_FEATURE_CATEGORIES` | Category listing within departments | `true` |
| `HATSHOP_FEATURE_PRODUCTS` | Product listings | `true` |
| `HATSHOP_FEATURE_PRODUCT_DETAILS` | Individual product pages | `true` |
| `HATSHOP_FEATURE_PAGINATION` | Multi-page product listings | `true` |
| `HATSHOP_FEATURE_SEARCH` | Catalog search functionality | `true` |

### E-commerce Features (Chapters 6+)

| Variable | Description | Default |
|----------|-------------|---------|
| `HATSHOP_FEATURE_SHOPPING_CART` | Shopping cart functionality | `false` |
| `HATSHOP_FEATURE_CUSTOMER_ORDERS` | Order management | `false` |
| `HATSHOP_FEATURE_PRODUCT_RECOMMENDATIONS` | Related products | `false` |
| `HATSHOP_FEATURE_CUSTOMER_DETAILS` | Customer profiles | `false` |
| `HATSHOP_FEATURE_ORDER_PIPELINE` | Order processing workflow | `false` |
| `HATSHOP_FEATURE_CREDIT_CARD` | Payment processing | `false` |
| `HATSHOP_FEATURE_PRODUCT_REVIEWS` | Customer reviews | `false` |

## Deployment Configurations

### Docker Compose

In your `docker-compose.yaml`:

```yaml
version: '3.8'
services:
  php:
    image: hatshop-php:latest
    environment:
      # Feature configuration
      HATSHOP_CHAPTER_LEVEL: "5"
      
      # Optional: Override specific features
      HATSHOP_FEATURE_SEARCH: "true"
      
      # Other configuration
      HATSHOP_DB_SERVER: db
      HATSHOP_DB_USERNAME: hatshop
      HATSHOP_DB_PASSWORD: ${DB_PASSWORD}
      HATSHOP_DB_DATABASE: hatshop
      HATSHOP_HTTP_SERVER_HOST: ${PUBLIC_HOSTNAME}
```

Or use an `.env` file:

```bash
# .env
HATSHOP_CHAPTER_LEVEL=5
HATSHOP_FEATURE_SHOPPING_CART=false
HATSHOP_DB_PASSWORD=supersecret
PUBLIC_HOSTNAME=shop.example.com
```

### Kubernetes

#### ConfigMap for Feature Flags

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: hatshop-features
  namespace: hatshop
data:
  HATSHOP_CHAPTER_LEVEL: "5"
  HATSHOP_FEATURE_SEARCH: "true"
  HATSHOP_FEATURE_SHOPPING_CART: "false"
  HATSHOP_DEBUGGING: "false"
  HATSHOP_HTTP_SERVER_HOST: "shop.example.com"
```

#### Deployment Using ConfigMap

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: hatshop-php
  namespace: hatshop
spec:
  replicas: 3
  selector:
    matchLabels:
      app: hatshop-php
  template:
    metadata:
      labels:
        app: hatshop-php
    spec:
      containers:
        - name: php
          image: hatshop-php:latest
          envFrom:
            - configMapRef:
                name: hatshop-features
            - secretRef:
                name: hatshop-db-credentials
          ports:
            - containerPort: 9000
```

#### Secrets for Sensitive Data

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: hatshop-db-credentials
  namespace: hatshop
type: Opaque
stringData:
  HATSHOP_DB_USERNAME: hatshop
  HATSHOP_DB_PASSWORD: supersecretpassword
```

### Environment-Specific Configurations

#### Development

```bash
HATSHOP_CHAPTER_LEVEL=5
HATSHOP_DEBUGGING=true
HATSHOP_IS_WARNING_FATAL=false
HATSHOP_LOG_ERRORS=true
HATSHOP_LOG_ERRORS_FILE=/var/log/hatshop/errors.log
```

#### Staging

```bash
HATSHOP_CHAPTER_LEVEL=5
HATSHOP_DEBUGGING=false
HATSHOP_LOG_ERRORS=true
HATSHOP_SEND_ERROR_MAIL=true
HATSHOP_ADMIN_ERROR_MAIL=devteam@example.com
```

#### Production

```bash
HATSHOP_CHAPTER_LEVEL=5
HATSHOP_DEBUGGING=false
HATSHOP_IS_WARNING_FATAL=true
HATSHOP_LOG_ERRORS=true
HATSHOP_SEND_ERROR_MAIL=true
HATSHOP_ADMIN_ERROR_MAIL=oncall@example.com
```

## Enabling New Features

### Rolling Out a New Feature

1. **Test in Development**
   ```bash
   HATSHOP_FEATURE_SHOPPING_CART=true
   ```

2. **Deploy to Staging**
   Update the staging ConfigMap and apply:
   ```bash
   kubectl apply -f staging-features.yaml
   kubectl rollout restart deployment/hatshop-php -n staging
   ```

3. **Monitor and Validate**
   - Check application logs
   - Verify functionality
   - Monitor error rates

4. **Deploy to Production**
   ```bash
   kubectl apply -f production-features.yaml
   kubectl rollout restart deployment/hatshop-php -n production
   ```

### Emergency Feature Disable

To quickly disable a problematic feature:

```bash
# Kubernetes
kubectl set env deployment/hatshop-php \
  HATSHOP_FEATURE_SEARCH=false -n production

# Docker Compose
docker-compose exec php \
  bash -c "export HATSHOP_FEATURE_SEARCH=false"
# Then restart the container
docker-compose restart php
```

## Monitoring Feature Flags

### Logging Current State

Add to your deployment to log feature states on startup:

```php
// In app_top.php or bootstrap
use Hatshop\Core\FeatureFlags;

$flags = FeatureFlags::getAllFlags();
error_log("HatShop starting with features: " . json_encode($flags));
```

### Health Check Endpoint

Create an endpoint to check feature states:

```php
// health.php
<?php
require_once 'include/app_top.php';

use Hatshop\Core\FeatureFlags;

header('Content-Type: application/json');
echo json_encode([
    'status' => 'healthy',
    'features' => FeatureFlags::getAllFlags(),
    'chapter_level' => getenv('HATSHOP_CHAPTER_LEVEL') ?: 'not set'
]);
```

## Troubleshooting

### Features Not Working

1. **Verify environment variables are set:**
   ```bash
   # Docker
   docker exec hatshop-php env | grep HATSHOP_FEATURE
   
   # Kubernetes
   kubectl exec -it deploy/hatshop-php -n production -- env | grep HATSHOP
   ```

2. **Check for typos** - Variable names are case-sensitive

3. **Verify the application loaded the flags:**
   - Check application logs for feature flag initialization
   - Use the health check endpoint

### Conflicting Settings

`HATSHOP_CHAPTER_LEVEL` sets default values, individual flags override:

```bash
HATSHOP_CHAPTER_LEVEL=5           # Enables search
HATSHOP_FEATURE_SEARCH=false      # Overrides to disable search
```

The final state will have search disabled.

### Container Not Picking Up Changes

After changing ConfigMap or environment variables:

```bash
# Kubernetes - restart pods
kubectl rollout restart deployment/hatshop-php

# Docker Compose
docker-compose down && docker-compose up -d
```

## Complete Environment Variable Reference

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `HATSHOP_CHAPTER_LEVEL` | integer | - | Enable all features up to this chapter |
| `HATSHOP_FEATURE_*` | boolean | varies | Individual feature toggles |
| `HATSHOP_DEBUGGING` | boolean | `true` | Show detailed error messages |
| `HATSHOP_IS_WARNING_FATAL` | boolean | `true` | Stop execution on warnings |
| `HATSHOP_LOG_ERRORS` | boolean | `false` | Log errors to file |
| `HATSHOP_LOG_ERRORS_FILE` | string | `/var/tmp/hatshop_errors.log` | Error log path |
| `HATSHOP_SEND_ERROR_MAIL` | boolean | `false` | Email errors to admin |
| `HATSHOP_ADMIN_ERROR_MAIL` | string | `admin@example.com` | Admin email for errors |
| `HATSHOP_DB_SERVER` | string | `localhost` | Database host |
| `HATSHOP_DB_USERNAME` | string | - | Database username |
| `HATSHOP_DB_PASSWORD` | string | - | Database password |
| `HATSHOP_DB_DATABASE` | string | `hatshop` | Database name |
| `HATSHOP_HTTP_SERVER_HOST` | string | `localhost` | Public hostname |
| `HATSHOP_HTTP_SERVER_PORT` | string | `80` | HTTP port |
| `HATSHOP_PRODUCTS_PER_PAGE` | integer | `4` | Products per page |
| `HATSHOP_SHORT_PRODUCT_DESCRIPTION_LENGTH` | integer | `150` | Description truncation length |
