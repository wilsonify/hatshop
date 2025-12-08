---
title: "Deploy to Stage Environment"
weight: 2
bookToc: true
---

# Deploy HatShop to Stage Environment

This guide covers deploying HatShop to the staging environment using KIND (Kubernetes in Docker) with Cloudflare Tunnel for public access.

## Overview

The stage deployment runs on a local Kubernetes cluster using KIND, providing a production-like environment for testing before release. Configuration is managed via ConfigMaps and SOPS-encrypted Secrets.

### Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared pod)
    │
    ▼
Nginx Service (:30081) ──────► PHP Service (:80)
    │                          (hatshop application)
    │                          ├── ConfigMap (hatshop-config)
    │                          └── SopsSecret → Secret (hatshop-secrets)
    │
    └── /stage path routing
```

### Components

| Component | Description |
|-----------|-------------|
| **KIND Cluster** | Single-node Kubernetes cluster in Docker |
| **PHP Deployment** | HatShop application with chapter-level features |
| **ConfigMap** | Non-sensitive configuration (chapter level, feature flags) |
| **SopsSecret** | SOPS-encrypted secrets (passwords, tokens) |
| **SOPS Operator** | Decrypts SopsSecrets into Kubernetes Secrets |
| **Nginx Deployment** | Reverse proxy with `/stage` path routing |
| **Cloudflared Deployment** | Cloudflare Tunnel connector |
| **NodePort Service** | Exposes nginx on port 30081 (mapped to host 10081) |

### Access URLs

| Environment | URL | Port |
|-------------|-----|------|
| Public | https://hatshop.renewed-renaissance.com/stage | 10081 |
| Local | http://localhost:10081/stage | 10081 |

## Prerequisites

- Docker installed and running
- KIND (Kubernetes in Docker) installed
- kubectl installed
- SOPS and age installed (for secrets management)
- Access to the age private key for decryption

### Install KIND

```bash
# Linux (amd64)
curl -Lo ./kind https://kind.sigs.k8s.io/dl/v0.20.0/kind-linux-amd64
chmod +x ./kind
sudo mv ./kind /usr/local/bin/kind

# Verify installation
kind --version
```

### Install SOPS and age

```bash
# Install SOPS
curl -LO https://github.com/getsops/sops/releases/download/v3.8.1/sops-v3.8.1.linux.amd64
chmod +x sops-v3.8.1.linux.amd64
sudo mv sops-v3.8.1.linux.amd64 /usr/local/bin/sops

# Install age
curl -LO https://github.com/FiloSottile/age/releases/download/v1.1.1/age-v1.1.1-linux-amd64.tar.gz
tar xzf age-v1.1.1-linux-amd64.tar.gz
sudo mv age/age /usr/local/bin/
sudo mv age/age-keygen /usr/local/bin/

# Verify
sops --version
age --version
```

## SOPS Secrets Management

Secrets are encrypted using [SOPS](https://github.com/getsops/sops) with [age](https://github.com/FiloSottile/age) encryption and decrypted in-cluster by the SOPS Secrets Operator.

### Age Encryption Key

| Key Type | Value |
|----------|-------|
| **Public Key** | `age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek` |
| **Private Key Location** | `~/.sops/age/keys.txt` (never commit) |

### Encrypted Files

| File | Purpose |
|------|---------|
| `base/hatshop-secrets.enc.yaml` | SOPS-encrypted SopsSecret CRD |
| `base/hatshop-secrets.dec.yaml` | Decrypted template (gitignored) |
| `base/hatshop-configmap.yaml` | Non-sensitive configuration |

### Working with Secrets

```bash
cd deploy/02_stage

# Decrypt secrets for editing
make sops-decrypt
# or manually:
sops decrypt base/hatshop-secrets.enc.yaml > base/hatshop-secrets.dec.yaml

# Edit the decrypted file
$EDITOR base/hatshop-secrets.dec.yaml

# Re-encrypt after changes
make secrets-encrypt
# or manually:
sops encrypt \
  --age age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek \
  --encrypted-regex "PASSWORD|EMAIL" \
  base/hatshop-secrets.dec.yaml > base/hatshop-secrets.enc.yaml
```

## Directory Structure

```
deploy/02_stage/
├── .env                          # Cloudflare token (not in git)
├── kind-config.yaml              # KIND cluster configuration
├── Makefile                      # Deployment automation
├── README.md
└── base/
    ├── kustomization.yaml
    ├── namespace.yaml
    ├── hatshop-configmap.yaml    # Non-sensitive config (chapter level, features)
    ├── hatshop-secrets.enc.yaml  # SOPS-encrypted secrets
    ├── hatshop-secrets.dec.yaml  # Decrypted template (gitignored)
    ├── .gitignore                # Excludes decrypted secrets
    ├── php-deployment.yaml       # App deployment with envFrom
    ├── nginx-deployment.yaml
    ├── nginx-configmap.yaml
    ├── cloudflared-deployment.yaml
    └── cloudflared-secret.yaml
```

## Feature Levels and Configuration

The stage environment uses `HATSHOP_CHAPTER_LEVEL` to control which features are enabled.

### Available Feature Levels

| Chapter | Features Enabled |
|---------|-----------------|
| 2 | Departments layout |
| 3 | Categories display |
| 4 | Products, product details, pagination |
| 5 | Search functionality |
| 6 | PayPal payments |
| 7 | Catalog administration |
| 8 | Shopping cart |
| 9 | Customer orders |
| 10 | Product recommendations |
| 11 | Customer details management |
| 12 | Order storage |

### Configuration Files

**ConfigMap** (`base/hatshop-configmap.yaml`):
```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: hatshop-config
data:
  HATSHOP_CHAPTER_LEVEL: "12"
  HATSHOP_FEATURE_SHOPPING_CART: "true"
  HATSHOP_FEATURE_CUSTOMER_ORDERS: "true"
  # ... additional feature flags
```

**SopsSecret** (`base/hatshop-secrets.enc.yaml`):
```yaml
apiVersion: isindir.github.com/v1alpha3
kind: SopsSecret
metadata:
  name: hatshop-secrets
spec:
  secretTemplates:
    - name: hatshop-secrets
      stringData:
        HATSHOP_DB_PASSWORD: ENC[AES256_GCM,...]
        HATSHOP_ADMIN_PASSWORD: ENC[AES256_GCM,...]
```

## Deployment Workflow

### 1. Initial Setup (First Time Only)

```bash
cd deploy/02_stage

# Ensure age private key is available
export SOPS_AGE_KEY_FILE=~/.sops/age/keys.txt

# Install SOPS operator in cluster
make sops-install
```

### 2. Promote Configuration from Dev

After testing features in dev, promote configuration to stage:

```bash
# From the dev directory
cd deploy/01_dev/hatshop

# Promote .env to stage (for Cloudflare token)
make promote-stage
```

### 3. Deploy to Stage

```bash
cd deploy/02_stage

# Full deployment (creates cluster, installs SOPS operator, deploys app)
make all
```

The `make all` target executes:
1. `cluster-create` - Creates the KIND cluster
2. `sops-install` - Installs SOPS Secrets Operator
3. `secret` - Creates the Cloudflare secret from `.env`
4. `deploy` - Applies Kustomize manifests (ConfigMap + SopsSecret + Deployments)
5. `status` - Shows deployment status

### 4. Verify the Deployment

```bash
# Check all resources
make status

# View logs from all pods
make logs

# Test the application
curl http://localhost:10081/stage
```

## Makefile Reference

| Target | Description |
|--------|-------------|
| `make all` | Full deployment (cluster + SOPS + secret + deploy + status) |
| `make cluster-create` | Create KIND cluster only |
| `make cluster-delete` | Delete the KIND cluster |
| `make sops-install` | Install SOPS Secrets Operator |
| `make sops-decrypt` | Decrypt secrets for local editing |
| `make secrets-encrypt` | Re-encrypt secrets after editing |
| `make secret` | Create cloudflared secret from `.env` |
| `make deploy` | Apply Kustomize manifests |
| `make undeploy` | Remove all Kubernetes resources |
| `make logs` | View logs from all pods |
| `make logs-php` | View PHP pod logs |
| `make logs-nginx` | View nginx pod logs |
| `make logs-cloudflared` | View cloudflared pod logs |
| `make status` | Show cluster and pod status |
| `make clean` | Delete cluster and clean up |
| `make help` | Show all available targets |

## Upgrading Feature Level

To upgrade the stage environment to a new chapter level:

### Step 1: Update ConfigMap

Edit `base/hatshop-configmap.yaml`:

```yaml
data:
  HATSHOP_CHAPTER_LEVEL: "12"  # Change to desired level
  # Add new feature flags as needed
  HATSHOP_FEATURE_NEW_FEATURE: "true"
```

### Step 2: Update Secrets (If Needed)

If the new features require additional secrets:

```bash
# Decrypt current secrets
make sops-decrypt

# Edit decrypted file
$EDITOR base/hatshop-secrets.dec.yaml

# Add new secrets under stringData:
#   NEW_API_KEY: "your-secret-value"

# Re-encrypt
make secrets-encrypt
```

### Step 3: Apply Changes

```bash
# Apply updated configuration
make deploy

# Restart pods to pick up new config
kubectl rollout restart deployment/php -n hatshop-stage

# Verify
make status
make logs-php
```

### Step 4: Validate Features

```bash
# Test application
curl http://localhost:10081/stage

# Check specific features
curl http://localhost:10081/stage/cart.php
curl http://localhost:10081/stage/admin/
```

## Troubleshooting

### Check pod status

```bash
kubectl get pods -n hatshop-stage
```

All pods should show `Running` status:

```
NAME                           READY   STATUS    RESTARTS   AGE
cloudflared-xxxxx              1/1     Running   0          5m
nginx-xxxxx                    1/1     Running   0          5m
php-xxxxx                      1/1     Running   0          5m
```

### View pod logs

```bash
# Cloudflared logs (check tunnel connection)
kubectl logs -n hatshop-stage deployment/cloudflared

# PHP logs (check application errors)
kubectl logs -n hatshop-stage deployment/php

# Nginx logs (check proxy errors)
kubectl logs -n hatshop-stage deployment/nginx
```

### Common issues

#### 1. Cloudflared token invalid

**Symptom**: Cloudflared pod shows "Provided Tunnel token is not valid"

**Solution**: Re-promote the `.env` from dev and recreate the secret:

```bash
# In dev directory
cd deploy/01_dev/hatshop
make promote-stage

# In stage directory
cd deploy/02_stage
make secret
kubectl rollout restart deployment/cloudflared -n hatshop-stage
```

#### 2. Port already in use

**Symptom**: KIND cluster creation fails with port binding error

**Solution**: Check for existing clusters or services using port 10081:

```bash
# List KIND clusters
kind get clusters

# Delete existing stage cluster
kind delete cluster --name hatshop-stage

# Check port usage
sudo lsof -i :10081
```

#### 3. 502 Bad Gateway

**Symptom**: Nginx returns 502 when accessing the application

**Solution**: Check if PHP pod is running and verify service connectivity:

```bash
# Check PHP pod
kubectl get pods -n hatshop-stage -l app=php

# Test internal connectivity
kubectl exec -n hatshop-stage deployment/nginx -- curl -s http://php/
```

#### 4. 530 Error from Cloudflare

**Symptom**: Public URL returns Cloudflare 530 error

**Solution**: The tunnel cannot reach the origin. Verify:
1. Cloudflared pod is running
2. NodePort service is exposed correctly
3. Port mapping in KIND config matches Cloudflare dashboard

```bash
# Check port mapping
docker port hatshop-stage-control-plane

# Should show:
# 30081/tcp -> 0.0.0.0:10081
```

## Updating the deployment

### Upgrade from Dev Environment

When promoting a tested configuration from dev to stage:

```bash
# 1. Review dev configuration
cat deploy/01_dev/hatshop/.env

# 2. Update stage ConfigMap to match dev chapter level
# Edit deploy/02_stage/base/hatshop-configmap.yaml

# 3. Update secrets if needed
cd deploy/02_stage
make sops-decrypt
# Edit base/hatshop-secrets.dec.yaml
make secrets-encrypt

# 4. Apply and restart
make deploy
kubectl rollout restart deployment/php -n hatshop-stage
```

### Update Application Code

After changes to source code in `src/hatshop/`:

```bash
# Rebuild the container image
cd src/hatshop
docker build -t ghcr.io/wilsonify/hatshop:latest .

# Load into KIND cluster
kind load docker-image ghcr.io/wilsonify/hatshop:latest --name hatshop-stage

# Restart deployment
kubectl rollout restart deployment/php -n hatshop-stage
```

### Update Configuration Only

To change feature flags without rebuilding:

```bash
# Edit ConfigMap
$EDITOR deploy/02_stage/base/hatshop-configmap.yaml

# Apply changes
kubectl apply -f deploy/02_stage/base/hatshop-configmap.yaml

# Restart to pick up changes
kubectl rollout restart deployment/php -n hatshop-stage
```

### Update Secrets

To rotate or add secrets:

```bash
cd deploy/02_stage

# Decrypt
make sops-decrypt

# Edit
$EDITOR base/hatshop-secrets.dec.yaml

# Re-encrypt
make secrets-encrypt

# Apply (SOPS operator will auto-decrypt)
kubectl apply -f base/hatshop-secrets.enc.yaml

# Restart pods
kubectl rollout restart deployment/php -n hatshop-stage
```

### Update nginx configuration

Edit `base/nginx-configmap.yaml`, then:

```bash
kubectl apply -k base/
kubectl rollout restart deployment/nginx -n hatshop-stage
```

### Full redeployment

```bash
make clean
make all
```

## Cleanup

To completely remove the stage environment:

```bash
# Delete everything
make clean

# Or just remove Kubernetes resources (keep cluster)
make undeploy
```

## Port Assignments

| Environment | Host Port | NodePort | Path |
|-------------|-----------|----------|------|
| Dev | 10080 | N/A | `/dev` |
| Stage | 10081 | 30081 | `/stage` |
| Prod | 10082 | 30082 | `/prod` |

## Security Notes

### Secrets Management

- **Never commit** unencrypted secrets (`hatshop-secrets.dec.yaml` is gitignored)
- **SOPS encryption** protects secrets at rest in version control
- **SOPS Operator** decrypts secrets in-cluster automatically
- **age private key** must be kept secure and never committed

### Key Rotation

To rotate the age encryption key:

```bash
# Generate new key pair
age-keygen -o new-keys.txt

# Re-encrypt all secrets with new public key
sops rotate --age <new-public-key> base/hatshop-secrets.enc.yaml

# Update key references in documentation and CI/CD
```

### Access Control

- The `.env` file contains the Cloudflare tunnel token (not SOPS-encrypted)
- ConfigMap values are non-sensitive and can be committed
- Database and admin passwords are SOPS-encrypted

### Encrypted Fields

The following fields are encrypted in `hatshop-secrets.enc.yaml`:

| Field | Purpose |
|-------|---------|
| `HATSHOP_DB_PASSWORD` | PostgreSQL database password |
| `HATSHOP_ADMIN_PASSWORD` | Admin interface password |
| `HATSHOP_PAYPAL_EMAIL` | PayPal business email |
