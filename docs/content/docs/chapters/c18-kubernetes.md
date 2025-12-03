---
title: "c18 - Kubernetes in Docker (KIND)"
weight: 18
---

# Chapter 18: Kubernetes in Docker

Deploy HatShop to Kubernetes using KIND (Kubernetes IN Docker).

## Overview

- **KIND Cluster** - Local Kubernetes with Docker
- **Deployments** - Application containers
- **Services** - Network routing
- **ConfigMaps/Secrets** - Configuration management

## Getting Started

### Install KIND

```bash
# Linux
curl -Lo ./kind https://kind.sigs.k8s.io/dl/latest/kind-linux-amd64
chmod +x ./kind
sudo mv ./kind /usr/local/bin/

# Create cluster
kind create cluster --name hatshop
```

### Deploy HatShop

```bash
cd "c18 - Kubernetes In Docker"
kubectl apply -f .
```

## Kubernetes Resources

### Deployment

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: hatshop-app
spec:
  replicas: 3
  selector:
    matchLabels:
      app: hatshop
  template:
    spec:
      containers:
      - name: hatshop
        image: hatshop:latest
        ports:
        - containerPort: 80
```

### Service

```yaml
apiVersion: v1
kind: Service
metadata:
  name: hatshop-service
spec:
  type: LoadBalancer
  ports:
  - port: 80
  selector:
    app: hatshop
```

## Useful Commands

```bash
# View pods
kubectl get pods

# View logs
kubectl logs -f deployment/hatshop-app

# Scale deployment
kubectl scale deployment hatshop-app --replicas=5

# Delete cluster
kind delete cluster --name hatshop
```

## Next Steps

Continue to [Chapter 19: Identity Provider]({{< relref "/docs/chapters/c19-identity" >}}).
