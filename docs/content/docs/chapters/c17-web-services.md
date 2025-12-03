---
title: "c17 - Web Services"
weight: 17
---

# Chapter 17: Connecting to Web Services

Integrate external APIs and expose your own web services.

## Overview

- **REST APIs** - Consume external services
- **XML/JSON** - Data format handling
- **API Endpoints** - Expose HatShop data

## Getting Started

```bash
cd "c17 - Connecting to Web Services - Authorize.net"
# or
cd "c17 - Connecting to Web Services - Datacash.com"
docker-compose up -d
```

## External Integrations

### Shipping APIs

Calculate shipping rates:
```php
$shipping = ShippingApi::getRate($weight, $destination);
```

### Tax Calculation

External tax service integration:
```php
$tax = TaxApi::calculate($amount, $state);
```

## HatShop API

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | List products |
| GET | `/api/products/{id}` | Product details |
| GET | `/api/categories` | List categories |

## Next Steps

Continue to [Chapter 18: Kubernetes]({{< relref "/docs/chapters/c18-kubernetes" >}}).
