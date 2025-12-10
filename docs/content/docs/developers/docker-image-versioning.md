---
title: "Docker Image Versioning"
weight: 5
bookToc: true
---

# Docker Image Versioning

This guide explains the versioning strategy for HatShop Docker images published to the GitHub Container Registry (ghcr.io).

## Overview

Every Docker image build generates multiple tags to support different use cases. This allows developers to choose the appropriate level of stability and traceability for their environment.

## Available Tags

| Tag Format | Example | Description | Recommended For |
|------------|---------|-------------|-----------------|
| `X.Y.Z` | `0.1.5` | Full semantic version | Production deployments |
| `X.Y` | `0.1` | Major.minor version | Staging environments (auto-receives patches) |
| `sha-XXXXXXX` | `sha-abc1234` | Git commit SHA (7 chars) | Debugging, exact reproducibility |
| `YYYYMMDD` | `20251210` | Build date | Time-based rollbacks |
| `branch` | `main`, `feature-auth` | Branch name | Development environments |
| `latest` | `latest` | Most recent build | Quick testing only |

## Semantic Versioning

Images follow [Semantic Versioning](https://semver.org/) (SemVer):

- **MAJOR** (X): Breaking changes that require migration
- **MINOR** (Y): New features, backward compatible
- **PATCH** (Z): Bug fixes, backward compatible

The patch version auto-increments with each build. Manual intervention is required to bump major or minor versions.

## Pulling Images

### Production (pinned version)

```bash
docker pull ghcr.io/wilsonify/hatshop:0.1.5
```

### Staging (minor version, receives patches)

```bash
docker pull ghcr.io/wilsonify/hatshop:0.1
```

### Development (branch tracking)

```bash
docker pull ghcr.io/wilsonify/hatshop:main
```

### Debugging (exact commit)

```bash
docker pull ghcr.io/wilsonify/hatshop:sha-abc1234
```

## Image Labels

Each image includes OCI-compliant labels for traceability:

| Label | Description |
|-------|-------------|
| `org.opencontainers.image.version` | Semantic version |
| `org.opencontainers.image.revision` | Full git commit SHA |
| `org.opencontainers.image.created` | ISO 8601 build timestamp |
| `org.opencontainers.image.source` | Repository URL |
| `org.opencontainers.image.description` | Image description |

### Inspecting Labels

```bash
docker inspect ghcr.io/wilsonify/hatshop:0.1.5 \
  --format '{{json .Config.Labels}}' | jq
```

## Available Images

The following images are published:

| Image | Purpose |
|-------|---------|
| `ghcr.io/wilsonify/hatshop` | Main PHP application |
| `ghcr.io/wilsonify/c00-postgresql-image` | PostgreSQL database |
| `ghcr.io/wilsonify/c00-nginx-image` | Nginx reverse proxy |
| `ghcr.io/wilsonify/c00-apache-image` | Apache web server |
| `ghcr.io/wilsonify/c01-php-image` | Base PHP image |
| `ghcr.io/wilsonify/c18-kubernetes-in-docker` | KinD setup |
| `ghcr.io/wilsonify/c19-identity-provider` | Identity provider |
| `ghcr.io/wilsonify/c20-oauth2-proxy` | OAuth2 proxy |
| `ghcr.io/wilsonify/c21-selenium` | Selenium testing |
| `ghcr.io/wilsonify/c22-zero-trust` | Zero trust networking |

## Best Practices

### For Production

1. Always use full semantic versions (`X.Y.Z`)
2. Test upgrades in staging before production
3. Document the version in your deployment manifests

### For Staging

1. Use minor versions (`X.Y`) to automatically receive patches
2. Pin to specific versions when debugging issues

### For Development

1. Use branch tags for feature development
2. Use `sha-` tags to reproduce exact builds
3. Avoid `latest` except for quick local testing

## Workflow

The versioning is handled automatically by the CI/CD pipeline:

1. **Trigger**: Manual workflow dispatch via GitHub Actions
2. **Version Detection**: Queries existing tags from the registry
3. **Increment**: Bumps patch version automatically
4. **Tag**: Applies all tag formats
5. **Push**: Publishes to ghcr.io

See [`.github/workflows/docker-all.yml`](https://github.com/wilsonify/hatshop/blob/master/.github/workflows/docker-all.yml) for implementation details.

## Troubleshooting

### Finding the exact version of a running container

```bash
docker inspect <container_id> \
  --format '{{index .Config.Labels "org.opencontainers.image.version"}}'
```

### Finding which commit a version corresponds to

```bash
docker inspect ghcr.io/wilsonify/hatshop:0.1.5 \
  --format '{{index .Config.Labels "org.opencontainers.image.revision"}}'
```

### Rolling back to a previous version

```bash
# In docker-compose.yaml, change:
image: ghcr.io/wilsonify/hatshop:0.1.5
# To a previous version:
image: ghcr.io/wilsonify/hatshop:0.1.4

# Then redeploy
docker compose up -d
```
