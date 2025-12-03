---
title: "c20 - OAuth2 Proxy"
weight: 20
---

# Chapter 20: OAuth2 Proxy

Add authentication layer using OAuth2 Proxy for enterprise SSO.

## Overview

- **OAuth2 Proxy** - Authentication gateway
- **OIDC Integration** - Connect to identity provider
- **Header Forwarding** - Pass user info to app

## Getting Started

```bash
cd "c20 - OAuth2 Proxy"
docker-compose up -d
```

## Configuration

```yaml
services:
  oauth2-proxy:
    image: quay.io/oauth2-proxy/oauth2-proxy
    environment:
      - OAUTH2_PROXY_PROVIDER=oidc
      - OAUTH2_PROXY_OIDC_ISSUER_URL=http://keycloak:8080/realms/hatshop
      - OAUTH2_PROXY_CLIENT_ID=hatshop
      - OAUTH2_PROXY_CLIENT_SECRET=secret
      - OAUTH2_PROXY_COOKIE_SECRET=<32-byte-secret>
      - OAUTH2_PROXY_UPSTREAMS=http://app:80
      - OAUTH2_PROXY_HTTP_ADDRESS=0.0.0.0:4180
    ports:
      - 4180:4180
```

## Authentication Flow

1. User accesses HatShop via OAuth2 Proxy
2. If not authenticated, redirect to IdP
3. User logs in at IdP
4. IdP redirects back with auth code
5. Proxy exchanges code for tokens
6. User info forwarded to app in headers

## Header Information

```php
// Get authenticated user from headers
$email = $_SERVER['HTTP_X_FORWARDED_EMAIL'];
$user = $_SERVER['HTTP_X_FORWARDED_USER'];
$groups = $_SERVER['HTTP_X_FORWARDED_GROUPS'];
```

## Next Steps

Continue to [Chapter 21: Selenium Testing]({{< relref "/docs/chapters/c21-selenium" >}}).
