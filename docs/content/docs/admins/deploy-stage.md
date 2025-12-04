---
title: "Deploy to Stage Environment"
weight: 2
bookToc: true
---

# Deploy HatShop to Stage Environment

This guide covers deploying HatShop to the staging environment using KIND (Kubernetes in Docker) with Cloudflare Tunnel for public access.

## Overview

The stage deployment runs on a local Kubernetes cluster using KIND, providing a production-like environment for testing before release.

### Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared pod)
    │
    ▼
Nginx Service (:30081) ──────► PHP Service (:80)
    │                          (hatshop c02)
    │
    └── /stage path routing
```

### Components

| Component | Description |
|-----------|-------------|
| **KIND Cluster** | Single-node Kubernetes cluster in Docker |
| **PHP Deployment** | HatShop c02 application on Apache |
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
- Access to the Cloudflare tunnel token (via dev `.env`)

### Install KIND

```bash
# Linux (amd64)
curl -Lo ./kind https://kind.sigs.k8s.io/dl/v0.20.0/kind-linux-amd64
chmod +x ./kind
sudo mv ./kind /usr/local/bin/kind

# Verify installation
kind --version
```

## Directory Structure

```
deploy/02_stage/
├── .env                  # Environment variables (promoted from dev)
├── kind-config.yaml      # KIND cluster configuration
├── Makefile              # Deployment automation
├── README.md
└── base/
    ├── kustomization.yaml
    ├── namespace.yaml
    ├── php-deployment.yaml
    ├── nginx-deployment.yaml
    ├── nginx-configmap.yaml
    ├── cloudflared-deployment.yaml
    └── cloudflared-secret.yaml
```

## Deployment Workflow

### 1. Promote secrets from dev

The stage environment requires a `.env` file with the Cloudflare tunnel token. This file is promoted from the dev environment after testing.

```bash
# From the dev directory
cd deploy/01_dev/hatshop

# Promote .env to stage
make promote-stage
```

This copies the tested `.env` file to `deploy/02_stage/.env`.

### 2. Deploy to stage

```bash
cd deploy/02_stage

# Full deployment (creates cluster, secret, and deploys)
make all
```

The `make all` target executes:
1. `cluster-create` - Creates the KIND cluster
2. `secret` - Creates the Kubernetes secret from `.env`
3. `deploy` - Applies Kustomize manifests
4. `status` - Shows deployment status

### 3. Verify the deployment

```bash
# Check all resources
make status

# View logs from all pods
make logs

# Or view specific service logs
make logs-php
make logs-nginx
make logs-cloudflared
```

## Makefile Reference

| Target | Description |
|--------|-------------|
| `make all` | Full deployment (cluster + secret + deploy + status) |
| `make cluster-create` | Create KIND cluster only |
| `make cluster-delete` | Delete the KIND cluster |
| `make secret` | Create cloudflared secret from `.env` |
| `make deploy` | Apply Kustomize manifests |
| `make undeploy` | Remove all Kubernetes resources |
| `make logs` | View logs from all pods |
| `make logs-php` | View PHP pod logs |
| `make logs-nginx` | View nginx pod logs |
| `make logs-cloudflared` | View cloudflared pod logs |
| `make status` | Show cluster and pod status |
| `make clean` | Delete cluster and clean up |

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

### Update application code

After changes to `src/c02 - Laying Out the Foundations/`:

```bash
# Rebuild and push image (if using remote registry)
# Or for local development, delete and recreate pod

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

- The `.env` file contains sensitive tokens and should never be committed to git
- The `cloudflared-secret.yaml` in `base/` contains a placeholder value
- Actual secrets are created at deployment time via `make secret`
- Consider using SOPS for encrypting `.env` files (see dev documentation)
