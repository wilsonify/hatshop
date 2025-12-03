---
title: "c09 - Customer Orders"
weight: 9
---

# Chapter 09: Dealing with Customer Orders

Handle customer orders from cart to fulfillment.

## Overview

- **Order Creation** - Convert cart to order
- **Order Status** - Track order progress
- **Order Details** - View order information

## Getting Started

```bash
cd "c09 - Dealing with Customer Orders"
docker-compose up -d
```

## Database Schema

```sql
CREATE TABLE orders (
    order_id SERIAL PRIMARY KEY,
    customer_id INTEGER,
    total_amount DECIMAL(10,2),
    status INTEGER DEFAULT 0,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_detail (
    order_id INTEGER REFERENCES orders(order_id),
    product_id INTEGER REFERENCES product(product_id),
    product_name VARCHAR(100),
    quantity INTEGER,
    unit_price DECIMAL(10,2),
    PRIMARY KEY (order_id, product_id)
);
```

## Order Statuses

| Status | Description |
|--------|-------------|
| 0 | Pending |
| 1 | Confirmed |
| 2 | Processing |
| 3 | Shipped |
| 4 | Delivered |
| 5 | Cancelled |

## Next Steps

Continue to [Chapter 10: Product Recommendations]({{< relref "/docs/chapters/c10-recommendations" >}}).
