---
title: "c19 - Identity Provider"
weight: 19
---

# Chapter 19: Identity Provider

Set up a dedicated identity provider for centralized authentication.

## Overview

- **Identity Server** - Centralized auth
- **OIDC/OAuth2** - Standard protocols
- **SSO** - Single Sign-On support

## Getting Started

```bash
cd "c19 - Identity Provider"
docker-compose up -d
```

## Components

### Keycloak (Example)

```yaml
services:
  keycloak:
    image: quay.io/keycloak/keycloak
    environment:
      - KEYCLOAK_ADMIN=admin
      - KEYCLOAK_ADMIN_PASSWORD=admin
    ports:
      - 8180:8080
    command: start-dev
```

### Configuration

1. Create "hatshop" realm
2. Create client application
3. Configure redirect URIs
4. Set up user federation (optional)

## Integration

```php
// Verify JWT token
$token = $_SERVER['HTTP_AUTHORIZATION'];
$payload = JWT::decode($token, $publicKey);
$userId = $payload->sub;
```

## Next Steps

Continue to [Chapter 20: OAuth2 Proxy]({{< relref "/docs/chapters/c20-oauth2" >}}).
