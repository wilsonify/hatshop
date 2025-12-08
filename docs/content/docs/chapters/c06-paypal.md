---
title: "c06 - PayPal Payments"
weight: 6
---

# Chapter 06: Receiving Payments Using PayPal

Integrate PayPal for payment processing using the unified HatShop application with feature flags.

## Overview

- **PayPal Standard** - Basic "Add to Cart" and checkout integration
- **Shopping Cart Window** - PayPal-hosted cart management
- **Sandbox Testing** - Development-friendly PayPal sandbox environment

## Getting Started

### Using the Unified App

The PayPal feature is integrated into the unified application. Enable it by setting the chapter level to 6:

```bash
# In your .env file
HATSHOP_CHAPTER_LEVEL=6
```

Or enable PayPal specifically:

```bash
HATSHOP_FEATURE_PAYPAL=true
```

### Running with Docker Compose

```bash
cd deploy/01_dev/hatshop
docker-compose up -d
```

## PayPal Configuration

Configure PayPal settings via environment variables:

```bash
# PayPal Settings
HATSHOP_PAYPAL_URL=https://www.sandbox.paypal.com/cgi-bin/webscr
HATSHOP_PAYPAL_EMAIL=your-sandbox-seller@example.com
HATSHOP_PAYPAL_RETURN_URL=http://localhost/paypal_return.php
HATSHOP_PAYPAL_CANCEL_URL=http://localhost/paypal_cancel.php
HATSHOP_PAYPAL_IPN_URL=http://localhost/paypal_ipn.php
HATSHOP_PAYPAL_CURRENCY_CODE=USD
```

### Configuration Options

| Variable | Description | Default |
|----------|-------------|---------|
| `HATSHOP_PAYPAL_URL` | PayPal checkout URL | Sandbox URL |
| `HATSHOP_PAYPAL_EMAIL` | Your PayPal merchant email | (required) |
| `HATSHOP_PAYPAL_RETURN_URL` | URL after successful payment | (required) |
| `HATSHOP_PAYPAL_CANCEL_URL` | URL if customer cancels | (required) |
| `HATSHOP_PAYPAL_IPN_URL` | Instant Payment Notification URL | (optional) |
| `HATSHOP_PAYPAL_CURRENCY_CODE` | Currency code | `USD` |

## Features Enabled

When PayPal is enabled, the following UI elements appear:

1. **View Cart Button** - Opens PayPal shopping cart in a popup window
2. **Add to Cart Buttons** - On product listings and product detail pages
3. **JavaScript Cart Manager** - Handles PayPal popup window interactions

## Payment Flow

1. Customer browses products on your site
2. Customer clicks "Add to Cart" on products they want
3. Items are added to the PayPal-hosted shopping cart
4. Customer clicks "View Cart" to see their cart
5. Customer completes checkout on PayPal
6. PayPal redirects back to your return URL
7. (Optional) PayPal sends IPN for payment verification

## Using in Templates

The PayPal feature is automatically available in Smarty templates:

```smarty
{* Check if PayPal is enabled *}
{if $features.paypal}
    {* PayPal UI elements will be displayed *}
{/if}
```

PayPal configuration variables are available globally:
- `{$paypal_url}` - PayPal checkout URL
- `{$paypal_email}` - Merchant email
- `{$paypal_return_url}` - Return URL
- `{$paypal_cancel_url}` - Cancel URL
- `{$paypal_currency_code}` - Currency code

## Sandbox Testing

1. Create a PayPal Developer account at https://developer.paypal.com
2. Create sandbox seller and buyer accounts
3. Use sandbox seller email in `HATSHOP_PAYPAL_EMAIL`
4. Test purchases with sandbox buyer account

### Sandbox vs Production

| Environment | PayPal URL |
|-------------|------------|
| Sandbox | `https://www.sandbox.paypal.com/cgi-bin/webscr` |
| Production | `https://www.paypal.com/cgi-bin/webscr` |

## Next Steps

Continue to [Chapter 07: Catalog Administration]({{< relref "/docs/chapters/c07-admin" >}}).
