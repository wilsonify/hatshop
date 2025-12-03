---
title: "c08 - Shopping Cart"
weight: 8
---

# Chapter 08: The Shopping Cart

Implement shopping cart functionality with session and database persistence.

## Overview

- **Cart Storage** - Database-backed cart
- **Add/Remove Items** - Cart operations
- **Quantity Updates** - Modify item quantities
- **Cart Summary** - Totals and item count

## Getting Started

```bash
cd "c08 - The Shopping Cart"
docker-compose up -d
```

## Database Schema

```sql
CREATE TABLE shopping_cart (
    cart_id VARCHAR(32) NOT NULL,
    product_id INTEGER REFERENCES product(product_id),
    quantity INTEGER NOT NULL DEFAULT 1,
    added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cart_id, product_id)
);
```

## Cart Operations

```php
class ShoppingCart {
    public static function addProduct(string $cartId, int $productId): void;
    public static function updateQuantity(string $cartId, int $productId, int $qty): void;
    public static function removeProduct(string $cartId, int $productId): void;
    public static function getCart(string $cartId): array;
    public static function getTotal(string $cartId): float;
}
```

## Next Steps

Continue to [Chapter 09: Dealing with Customer Orders]({{< relref "/docs/chapters/c09-orders" >}}).
