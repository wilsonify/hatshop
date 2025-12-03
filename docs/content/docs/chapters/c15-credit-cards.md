---
title: "c15 - Credit Card Transactions"
weight: 15
---

# Chapter 15: Credit Card Transactions

Integrate credit card payment processing with Authorize.net or Datacash.

## Overview

Two implementations available:
- **Authorize.net** - US payment processor
- **Datacash.com** - European payment processor

## Getting Started

### Authorize.net Version
```bash
cd "c15 - Credit Card Transactions - Authorize.net"
docker-compose up -d
```

### Datacash Version
```bash
cd "c15 - Credit Card Transactions - Datacash.com"
docker-compose up -d
```

## Configuration

### Authorize.net

```php
define('AUTHORIZENET_API_LOGIN_ID', 'your_login_id');
define('AUTHORIZENET_TRANSACTION_KEY', 'your_transaction_key');
define('AUTHORIZENET_SANDBOX', true);
```

### Datacash

```php
define('DATACASH_CLIENT', 'your_client_id');
define('DATACASH_PASSWORD', 'your_password');
define('DATACASH_URL', 'https://testserver.datacash.com/Transaction');
```

## Payment Flow

1. Collect card details on secure form
2. Submit to payment gateway
3. Handle response (approved/declined)
4. Update order status

## Security Considerations

- Never store full card numbers
- Use HTTPS for all payment pages
- PCI DSS compliance required for production

## Next Steps

Continue to [Chapter 16: Product Reviews]({{< relref "/docs/chapters/c16-reviews" >}}).
