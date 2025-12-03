---
title: "c06 - PayPal Payments"
weight: 6
---

# Chapter 06: Receiving Payments Using PayPal

Integrate PayPal for payment processing.

## Overview

- **PayPal Standard** - Basic checkout integration
- **IPN (Instant Payment Notification)** - Payment confirmation
- **Order Completion** - Post-payment workflow

## Getting Started

```bash
cd "c06 - Receiving Payments Using PayPal"
docker-compose up -d
```

## PayPal Configuration

```php
// config.php
define('PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
define('PAYPAL_EMAIL', 'seller@example.com');
define('PAYPAL_RETURN_URL', 'http://localhost/checkout_complete.php');
define('PAYPAL_CANCEL_URL', 'http://localhost/checkout_cancel.php');
define('PAYPAL_IPN_URL', 'http://localhost/paypal_ipn.php');
```

## Payment Flow

1. Customer clicks "Checkout with PayPal"
2. Redirect to PayPal with cart details
3. Customer completes payment on PayPal
4. PayPal sends IPN to your server
5. Verify and complete order

## Next Steps

Continue to [Chapter 07: Catalog Administration]({{< relref "/docs/chapters/c07-admin" >}}).
