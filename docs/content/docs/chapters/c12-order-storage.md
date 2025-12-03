---
title: "c12 - Order Storage"
weight: 12
---

# Chapter 12: Storing Customer Orders

Connect orders to customer accounts for history and tracking.

## Overview

- **Order History** - View past orders
- **Guest Checkout** - Orders without account
- **Order Tracking** - Status updates

## Getting Started

```bash
cd "c12 - Storing Customer Orders"
docker-compose up -d
```

## Features

### Order History

Customers can view all their past orders:
- Order date and total
- Order status
- Items purchased
- Shipping details

### Guest Checkout

Allow purchases without registration:
- Email for order confirmation
- Option to create account after purchase
- Order lookup by email + order number

## Next Steps

Continue to [Chapter 13: Order Pipeline Part I]({{< relref "/docs/chapters/c13-pipeline-part1" >}}).
