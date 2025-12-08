# HatShop Stage Deployment

Kubernetes deployment for HatShop staging environment using KIND (Kubernetes in Docker).

## Overview

This deployment runs on a local KIND cluster with **Chapter 12 features**:
- **PHP Application** - HatShop application with all features up to Chapter 12
- **Nginx** - Reverse proxy for `/stage` path routing
- **Cloudflared** - Cloudflare Tunnel for public access

## Features Enabled (Chapter 12)

| Chapter | Feature |
|---------|---------|
| 2 | Departments |
| 3 | Categories |
| 4 | Products, Product Details, Pagination |
| 5 | Search |
| 6 | PayPal Payments |
| 7 | Catalog Administration |
| 8 | Shopping Cart |
| 9 | Customer Orders |
| 10 | Product Recommendations |
| 11 | Customer Details |
| 12 | Order Storage |

### Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared pod)
    │
    ▼
Nginx Service (NodePort :30081 → host :10080)
    │
    └── /stage path routing
            │
            ▼
      PHP Service (:80)
        ├── ConfigMap (hatshop-config)
        └── Secret (hatshop-secrets)
```

### Access URLs

| Environment | URL |
|-------------|-----|
| Public | https://hatshop.renewed-renaissance.com/stage |
| Local | http://localhost:10080/stage |

## Prerequisites

- Docker
- KIND (`go install sigs.k8s.io/kind@latest`)
- kubectl
- make
- SOPS/age (for secrets decryption)

## Quick Start

```bash
cd deploy/02_stage

# 1. Create .env file with Cloudflare token
cp ../01_dev/hatshop/.env .env
# Or decrypt from encrypted source:
# sops decrypt ../01_dev/hatshop/.env.enc > .env

# 2. Set age secret key for SOPS decryption
export AGE_SECRET_KEY=$(cat ~/.sops/age/keys.txt)

# 3. Deploy everything (cluster + SOPS operator + secrets + app)
make all

# 4. Verify deployment
make status
make test
```

## Secrets Management with SOPS

Secrets are encrypted using [SOPS](https://github.com/getsops/sops) with [age](https://github.com/FiloSottile/age) encryption.

### Age Key
- **Public Key**: `age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek`
- **Private Key Location**: `~/.sops/age/keys.txt`

### Encrypted Files
- `base/hatshop-secrets.enc.yaml` - SOPS-encrypted SopsSecret resource
- `base/hatshop-secrets.dec.yaml` - Decrypted source (in .gitignore, never commit)

### Working with Secrets

```bash
# View decrypted secrets
sops decrypt base/hatshop-secrets.enc.yaml

# Edit secrets in place
sops base/hatshop-secrets.enc.yaml

# Re-encrypt after editing decrypted file
sops encrypt --age age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek \
  --encrypted-regex "PASSWORD|EMAIL" \
  base/hatshop-secrets.dec.yaml > base/hatshop-secrets.enc.yaml
```

### SOPS Operator
The [SOPS Secrets Operator](https://github.com/isindir/sops-secrets-operator) runs in the cluster and automatically decrypts `SopsSecret` resources into Kubernetes `Secret` resources.

## Directory Structure

```
deploy/02_stage/
├── Makefile              # Deployment automation
├── kind-config.yaml      # KIND cluster configuration
├── .env                  # Environment variables (not in git)
├── README.md
└── base/
    ├── kustomization.yaml
    ├── namespace.yaml
    ├── hatshop-configmap.yaml    # Non-sensitive config
    ├── hatshop-secrets.enc.yaml  # SOPS-encrypted secrets
    ├── hatshop-secrets.dec.yaml  # Decrypted source (gitignored)
    ├── .gitignore
    ├── php-deployment.yaml
    ├── nginx-configmap.yaml
    ├── nginx-deployment.yaml
    ├── cloudflared-secret.yaml
    └── cloudflared-deployment.yaml
```

## Make Targets

| Target | Description |
|--------|-------------|
| `make all` | Create cluster, secret, and deploy (default) |
| `make cluster-create` | Create KIND cluster |
| `make cluster-delete` | Delete KIND cluster |
| `make secret` | Create cloudflared secret from .env |
| `make deploy` | Deploy application with kustomize |
| `make undeploy` | Remove application |
| `make logs` | View recent logs from all pods |
| `make logs-php` | Follow PHP logs |
| `make logs-nginx` | Follow Nginx logs |
| `make logs-cloudflared` | Follow Cloudflared logs |
| `make status` | Show cluster and pod status |
| `make port-forward` | Forward localhost:8080 to nginx |
| `make test` | Test endpoints |
| `make restart` | Restart all deployments |
| `make clean` | Remove deployment and cluster |
| `make help` | Show help |

## Manual Deployment Steps

### 1. Create KIND Cluster

```bash
kind create cluster --config kind-config.yaml
kubectl config use-context kind-hatshop-stage
```

### 2. Create Secret

```bash
# From .env file
kubectl create namespace hatshop-stage
kubectl -n hatshop-stage create secret generic cloudflared-secret \
  --from-literal=CLOUD_FLARE_TOKEN="your-token-here"
```

### 3. Deploy with Kustomize

```bash
kubectl apply -k base/
```

### 4. Verify

```bash
kubectl -n hatshop-stage get pods
kubectl -n hatshop-stage get svc
curl http://localhost:10080/stage
```

## Configuration

### KIND Cluster (kind-config.yaml)

```yaml
nodes:
  - role: control-plane
    extraPortMappings:
      - containerPort: 30081
        hostPort: 10080
        protocol: TCP
```

### Nginx Path Routing

The nginx ConfigMap routes `/stage` to the PHP application:

```nginx
location /stage {
    rewrite ^/stage$ / break;
    rewrite ^/stage/(.*)$ /$1 break;
    proxy_pass http://php:80;
}
```

### Resource Limits

| Component | CPU Request | CPU Limit | Memory Request | Memory Limit |
|-----------|-------------|-----------|----------------|--------------|
| PHP | 100m | 500m | 128Mi | 256Mi |
| Nginx | 50m | 200m | 64Mi | 128Mi |
| Cloudflared | 50m | 200m | 64Mi | 128Mi |

## Differences from Dev Environment

| Aspect | Dev (Docker Compose) | Stage (KIND/K8s) |
|--------|---------------------|------------------|
| Port | 10080 | 10080 |
| Path | `/dev` | `/stage` |
| Orchestration | Docker Compose | Kubernetes |
| Secrets | .env file | K8s Secret |
| Scaling | Manual | Declarative replicas |
| Health checks | None | Liveness/Readiness probes |

## Troubleshooting

### Cluster won't start

```bash
# Check Docker is running
docker ps

# Check KIND clusters
kind get clusters

# Delete and recreate
kind delete cluster --name hatshop-stage
make cluster-create
```

### Pods not starting

```bash
# Check pod status
kubectl -n hatshop-stage get pods
kubectl -n hatshop-stage describe pod <pod-name>

# Check events
kubectl -n hatshop-stage get events --sort-by='.lastTimestamp'
```

### Image pull errors

```bash
# Check if image exists
docker pull ghcr.io/wilsonify/c02-laying-out-the-foundations:latest

# Load local image into KIND
docker build -t hatshop-php:local "../../../src/c02 - Laying Out the Foundations"
kind load docker-image hatshop-php:local --name hatshop-stage
```

### Cloudflared not connecting

```bash
# Check logs
make logs-cloudflared

# Verify secret
kubectl -n hatshop-stage get secret cloudflared-secret -o yaml

# Recreate secret
make secret
kubectl -n hatshop-stage rollout restart deployment/cloudflared
```

### 502 Bad Gateway from Nginx

```bash
# Check PHP pod is running
kubectl -n hatshop-stage get pods -l app.kubernetes.io/component=php

# Check PHP service
kubectl -n hatshop-stage get svc php

# Check nginx can reach PHP
kubectl -n hatshop-stage exec -it deploy/nginx -- wget -qO- http://php:80/
```

## Cleanup

```bash
# Remove deployment only (keep cluster)
make undeploy

# Remove everything
make clean
```

## Related Documentation

- [Dev Environment](../01_dev/hatshop/README.md)
- [Cloudflare Tunnel Config](../01_dev/cloudflare/config.yaml)
- [Admin Guide](../../docs/content/docs/admins/)
