---
title: "Deploy to Dev Environment"
weight: 1
bookToc: true
---

# Deploy HatShop to Dev Environment

This guide covers deploying HatShop to the development environment using Docker Compose with Cloudflare Tunnel for public access.

## Overview

The dev deployment consists of four services:
- **PostgreSQL** - Database server for product catalog, customers, and orders
- **PHP Application** - The HatShop application running on Apache
- **Nginx** - Reverse proxy for path-based routing (`/dev`)
- **Cloudflared** - Cloudflare Tunnel for secure public access

### Architecture

```
Internet
    │
    ▼
Cloudflare Tunnel (cloudflared)
    │
    ▼
Nginx (:10080) ──────► PHP App (:80) ──────► PostgreSQL (:5432)
    │                  (hatshop)              (hatshop database)
    │
    └── /dev path routing
```

### Access URLs

| Environment | URL |
|-------------|-----|
| Public | https://hatshop.renewed-renaissance.com/dev |
| Local | http://localhost:10080/dev |

## Prerequisites

- Docker and Docker Compose installed
- Access to the Cloudflare tunnel token
- SOPS and age for secrets decryption (admin only)

## Directory Structure

```
deploy/01_dev/hatshop/
├── .env              # Environment variables (not in git)
├── .env.enc          # Encrypted environment variables
├── docker-compose.yaml
├── nginx.conf
└── README.md
```

## Deployment Steps

### 1. Navigate to the deployment directory

```bash
cd deploy/01_dev/hatshop
```

### 2. Decrypt the environment file

The `.env.enc` file contains encrypted secrets. Decrypt it using SOPS:

```bash
# Ensure you have the age private key
export SOPS_AGE_KEY_FILE=~/.config/sops/age/keys.txt

# Decrypt to .env
sops decrypt .env.enc > .env
```

If you need to create or update secrets:

```bash
# Encrypt with age public key
sops encrypt \
  --age $AGE_PUBLIC_KEY \
  --encrypted-regex "PASS|MAIL|KEY|TOKEN" \
  --input-type dotenv \
  .env > .env.enc
```

### 3. Build and start the services

```bash
# Build and start in detached mode
docker compose up -d --build

# Or just start if images are already built
docker compose up -d
```

### 4. Verify the deployment

```bash
# Check all services are running
docker compose ps

# Test local endpoint
curl http://localhost:10080/dev

# Test health endpoint
curl http://localhost:10080/health

# Check logs
docker compose logs -f
```

## Configuration Files

### docker-compose.yaml

```yaml
services:
  postgres:
    image: postgres:18.1-trixie
    restart: unless-stopped
    volumes:
      - postgres_data:/var/lib/postgresql
      - ../../../Database Complete/split_sql_files:/docker-entrypoint-initdb.d:ro
    environment:
      POSTGRES_USER: hatshop_admin
      POSTGRES_PASSWORD: ${HATSHOP_DB_PASSWORD:-hatshop_password}
      POSTGRES_DB: hatshop
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U hatshop_admin -d hatshop"]
      interval: 5s
      timeout: 5s
      retries: 5
    networks:
      - hatshop-network

  php:
    build:
      context: "../../../src/hatshop"
      dockerfile: Dockerfile
    user: www-data:www-data
    restart: unless-stopped
    environment:
      - HATSHOP_PATH_PREFIX=dev
      - HATSHOP_DB_SERVER=postgres
      - HATSHOP_DB_USER=hatshop_admin
      - HATSHOP_DB_PASSWORD=${HATSHOP_DB_PASSWORD:-hatshop_password}
      - HATSHOP_DB_NAME=hatshop
    expose:
      - "80"
    depends_on:
      postgres:
        condition: service_healthy
    networks:
      - hatshop-network

  nginx:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "10080:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
    depends_on:
      - php
    networks:
      - hatshop-network

  cloudflared:
    image: cloudflare/cloudflared:latest
    restart: unless-stopped
    env_file: .env
    command: tunnel --no-autoupdate run --token $CLOUD_FLARE_TOKEN
    depends_on:
      - nginx
    networks:
      - hatshop-network

networks:
  hatshop-network:
    driver: bridge

volumes:
  postgres_data:
```

### nginx.conf

The nginx configuration handles path-based routing:

- `/dev` → PHP application root
- `/dev/*` → PHP application with path stripped
- `/health` → Health check endpoint

```nginx
location /dev {
    rewrite ^/dev$ / break;
    rewrite ^/dev/(.*)$ /$1 break;
    proxy_pass http://php:80;
    # ... proxy headers
}
```

### Environment Variables

Required variables in `.env`:

| Variable | Description |
|----------|-------------|
| `CLOUD_FLARE_TOKEN` | Cloudflare tunnel authentication token |
| `HATSHOP_DB_PASSWORD` | PostgreSQL database password |

Database configuration:

| Variable | Description | Default |
|----------|-------------|---------|
| `HATSHOP_DB_SERVER` | Database server hostname | `postgres` |
| `HATSHOP_DB_USER` | Database username | `hatshop_admin` |
| `HATSHOP_DB_PASSWORD` | Database password | `hatshop_password` |
| `HATSHOP_DB_NAME` | Database name | `hatshop` |

Application configuration:

| Variable | Description | Default |
|----------|-------------|---------|
| `HATSHOP_PATH_PREFIX` | URL path prefix for assets | `dev` |
| `HATSHOP_IS_WARNING_FATAL` | Treat warnings as fatal | `true` |
| `HATSHOP_DEBUGGING` | Enable debug mode | `true` |
| `HATSHOP_LOG_ERRORS` | Log errors to file | `false` |
| `HATSHOP_LOG_ERRORS_FILE` | Error log path | `/var/tmp/hatshop_errors.log` |

For comprehensive PostgreSQL documentation, see [PostgreSQL Administration]({{< relref "/docs/admins/postgresql" >}}).

## Cloudflare Tunnel Configuration

The Cloudflare tunnel is pre-configured in the Cloudflare Zero Trust dashboard to route:

- `hatshop.renewed-renaissance.com/dev` → `http://nginx:80`

### Tunnel Setup (if creating new)

1. Log into [Cloudflare Zero Trust](https://one.dash.cloudflare.com/)
2. Navigate to **Access** → **Tunnels**
3. Create a new tunnel or select existing
4. Add a public hostname:
   - **Subdomain**: `hatshop`
   - **Domain**: `renewed-renaissance.com`
   - **Path**: `dev`
   - **Service**: `http://nginx:80` (or your host IP:10080)
5. Copy the tunnel token to `.env`

## Operations

### View Logs

```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f php
docker compose logs -f nginx
docker compose logs -f cloudflared
```

### Restart Services

```bash
# Restart all
docker compose restart

# Restart specific service
docker compose restart php
```

### Stop Deployment

```bash
docker compose down
```

### Rebuild After Code Changes

```bash
docker compose build --no-cache php
docker compose up -d
```

### Check Service Health

```bash
# Container status
docker compose ps

# Health endpoint
curl -s -o /dev/null -w "%{http_code}" http://localhost:10080/health

# Public URL
curl -s -o /dev/null -w "%{http_code}" https://hatshop.renewed-renaissance.com/dev
```

## Troubleshooting

### Cloudflare tunnel not connecting

Check tunnel logs:
```bash
docker compose logs cloudflared
```

Common issues:
- Invalid or expired token
- Network connectivity issues
- Cloudflare service outage

### PHP application errors

Check PHP logs:
```bash
docker compose logs php
```

Common issues:
- Missing dependencies (rebuild image)
- Permission issues (check www-data ownership)
- Database connection errors (see [PostgreSQL Troubleshooting]({{< relref "/docs/admins/postgresql#troubleshooting" >}}))

### Database connection errors

If the PHP application cannot connect to the database:
```bash
# Check PostgreSQL is healthy
docker compose ps postgres

# Verify database connectivity
docker compose exec postgres pg_isready -U hatshop_admin -d hatshop

# Check PostgreSQL logs
docker compose logs postgres
```

For detailed database troubleshooting, see [PostgreSQL Administration]({{< relref "/docs/admins/postgresql" >}}).

### Nginx 502 Bad Gateway

The PHP container may not be ready:
```bash
# Check PHP container is running
docker compose ps php

# Check PHP container logs
docker compose logs php

# Restart PHP container
docker compose restart php
```

### Path routing issues

Verify nginx configuration:
```bash
# Test local routing
curl -v http://localhost:10080/dev

# Check nginx logs
docker compose logs nginx
```

## Security Notes

- Never commit `.env` file to git (it's in `.gitignore`)
- The `.env.enc` file contains encrypted secrets
- Rotate the Cloudflare tunnel token periodically
- Keep `HATSHOP_DEBUGGING` set to `false` in production

## Related Documentation

- [PostgreSQL Administration]({{< relref "/docs/admins/postgresql" >}})
- [Chapter 2: Laying Out the Foundations]({{< relref "/docs/chapters/c02-foundations" >}})
- [Kubernetes Deployment]({{< relref "/docs/admins/deploy-kubernetes" >}})
- [Production Deployment]({{< relref "/docs/admins/deploy-prod" >}})
