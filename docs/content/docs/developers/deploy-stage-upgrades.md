---
title: "Stage Deployment Upgrades"
weight: 4
bookToc: true
---

# Deploying Upgrades to Stage

This guide explains how developers can prepare and deploy feature upgrades to the stage environment.

## Overview

The stage environment uses Kubernetes (KIND) with:
- **ConfigMap** for feature flags and chapter levels
- **SopsSecret** for encrypted sensitive configuration
- **SOPS Operator** for automatic secret decryption

## Workflow Summary

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Development   │───►│   Dev Testing   │───►│   Stage Deploy  │
│   (local/IDE)   │    │ (Docker Compose)│    │   (Kubernetes)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                      │                      │
    Code changes         Test features           Promote config
    Unit tests           Verify .env             Update ConfigMap
                                                 Update Secrets
```

## Prerequisites

Before deploying to stage, ensure:

1. **Code changes are committed** to the repository
2. **Tests pass** locally and in CI
3. **Dev environment** has been tested with the new features
4. **SOPS tools installed**:
   ```bash
   # Verify installations
   sops --version
   age --version
   kubectl version --client
   kind --version
   ```

## Step-by-Step Upgrade Process

### Step 1: Test in Dev Environment

```bash
# Navigate to dev environment
cd deploy/01_dev/hatshop

# Update .env with new chapter level
echo "HATSHOP_CHAPTER_LEVEL=12" >> .env

# Restart services
docker compose down
docker compose up -d

# Verify features work
curl http://localhost:10080/dev
```

### Step 2: Update Stage ConfigMap

Edit `deploy/02_stage/base/hatshop-configmap.yaml`:

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: hatshop-config
  namespace: hatshop-stage
data:
  # Update chapter level
  HATSHOP_CHAPTER_LEVEL: "12"
  
  # Feature flags for Chapter 8-12
  HATSHOP_FEATURE_SHOPPING_CART: "true"
  HATSHOP_FEATURE_CUSTOMER_ORDERS: "true"
  HATSHOP_FEATURE_PRODUCT_RECOMMENDATIONS: "true"
  HATSHOP_FEATURE_CUSTOMER_DETAILS: "true"
  HATSHOP_FEATURE_ORDER_STORAGE: "true"
  
  # Database configuration
  HATSHOP_DB_HOST: "postgres"
  HATSHOP_DB_USER: "hatshop"
  HATSHOP_DB_DATABASE: "hatshop"
  
  # Application settings
  HATSHOP_HTTP_SERVER_HOST: "hatshop.renewed-renaissance.com"
  HATSHOP_VIRTUAL_LOCATION: "/stage"
```

### Step 3: Update Secrets (If Needed)

If new features require additional secrets:

```bash
cd deploy/02_stage

# Set age key location
export SOPS_AGE_KEY_FILE=~/.sops/age/keys.txt

# Decrypt secrets
make sops-decrypt

# Edit decrypted file
$EDITOR base/hatshop-secrets.dec.yaml
```

Add new secrets to the `stringData` section:

```yaml
apiVersion: isindir.github.com/v1alpha3
kind: SopsSecret
metadata:
  name: hatshop-secrets
  namespace: hatshop-stage
spec:
  secretTemplates:
    - name: hatshop-secrets
      stringData:
        HATSHOP_DB_PASSWORD: "your-db-password"
        HATSHOP_ADMIN_PASSWORD: "your-admin-password"
        HATSHOP_PAYPAL_EMAIL: "paypal@example.com"
        # Add new secrets here
        NEW_API_KEY: "new-secret-value"
```

Re-encrypt:

```bash
make secrets-encrypt
```

### Step 4: Deploy to Stage

```bash
cd deploy/02_stage

# If cluster doesn't exist, create everything
make all

# If cluster exists, just deploy updates
make deploy

# Restart deployments to pick up config changes
kubectl rollout restart deployment/php -n hatshop-stage
```

### Step 5: Verify Deployment

```bash
# Check pod status
make status

# View logs
make logs-php

# Test application
curl http://localhost:10081/stage

# Test specific features
curl http://localhost:10081/stage/cart.php
```

## Configuration Reference

### Feature Flags

| Flag | Chapter | Description |
|------|---------|-------------|
| `HATSHOP_FEATURE_SHOPPING_CART` | 8 | Enable shopping cart |
| `HATSHOP_FEATURE_CUSTOMER_ORDERS` | 9 | Enable order management |
| `HATSHOP_FEATURE_PRODUCT_RECOMMENDATIONS` | 10 | Enable recommendations |
| `HATSHOP_FEATURE_CUSTOMER_DETAILS` | 11 | Enable customer profiles |
| `HATSHOP_FEATURE_ORDER_STORAGE` | 12 | Enable persistent orders |

### Chapter Levels

| Level | Features Included |
|-------|------------------|
| 2 | Departments |
| 3 | Categories |
| 4 | Products, Details, Pagination |
| 5 | Search |
| 6 | PayPal |
| 7 | Admin |
| 8 | Shopping Cart |
| 9 | Orders |
| 10 | Recommendations |
| 11 | Customer Details |
| 12 | Order Storage |

## Common Tasks

### View Current Configuration

```bash
# Check ConfigMap
kubectl get configmap hatshop-config -n hatshop-stage -o yaml

# Check Secrets (encrypted)
kubectl get secret hatshop-secrets -n hatshop-stage -o yaml

# Check environment in running pod
kubectl exec -n hatshop-stage deployment/php -- env | grep HATSHOP
```

### Rollback Configuration

```bash
# Revert ConfigMap changes
git checkout deploy/02_stage/base/hatshop-configmap.yaml
kubectl apply -f deploy/02_stage/base/hatshop-configmap.yaml
kubectl rollout restart deployment/php -n hatshop-stage

# Revert Secrets
git checkout deploy/02_stage/base/hatshop-secrets.enc.yaml
kubectl apply -f deploy/02_stage/base/hatshop-secrets.enc.yaml
kubectl rollout restart deployment/php -n hatshop-stage
```

### Debug Configuration Issues

```bash
# Check pod events
kubectl describe pod -n hatshop-stage -l app.kubernetes.io/component=php

# Check SOPS operator logs
kubectl logs -n sops deployment/sops-secrets-operator

# Verify secret was created
kubectl get secrets -n hatshop-stage
```

## SOPS Commands Reference

```bash
# Decrypt to stdout
sops decrypt base/hatshop-secrets.enc.yaml

# Edit in place (opens $EDITOR)
sops base/hatshop-secrets.enc.yaml

# Encrypt with specific key
sops encrypt \
  --age age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek \
  --encrypted-regex "PASSWORD|EMAIL|KEY|TOKEN" \
  base/hatshop-secrets.dec.yaml > base/hatshop-secrets.enc.yaml

# Rotate encryption key
sops rotate --age <new-public-key> base/hatshop-secrets.enc.yaml
```

## Troubleshooting

### Secrets Not Decrypting

1. **Check SOPS operator is running**:
   ```bash
   kubectl get pods -n sops
   ```

2. **Check operator has the age key**:
   ```bash
   kubectl get secret sops-age-key -n sops -o yaml
   ```

3. **Check SopsSecret status**:
   ```bash
   kubectl describe sopssecret hatshop-secrets -n hatshop-stage
   ```

### ConfigMap Changes Not Applied

1. **Verify ConfigMap was updated**:
   ```bash
   kubectl get configmap hatshop-config -n hatshop-stage -o yaml
   ```

2. **Restart deployment** to pick up changes:
   ```bash
   kubectl rollout restart deployment/php -n hatshop-stage
   ```

3. **Check environment variables in pod**:
   ```bash
   kubectl exec -n hatshop-stage deployment/php -- printenv | grep HATSHOP
   ```

### Pod Not Starting

```bash
# Check pod status
kubectl get pods -n hatshop-stage

# Describe pod for events
kubectl describe pod <pod-name> -n hatshop-stage

# Check logs
kubectl logs <pod-name> -n hatshop-stage
```

## Related Documentation

- [Admin Deploy Guide]({{< relref "/docs/admins/deploy-stage" >}})
- [Feature Flags]({{< relref "feature-flags" >}})
- [Shopping Cart Development]({{< relref "shopping-cart" >}})
