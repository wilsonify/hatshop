---
title: "c16 - Product Reviews"
weight: 16
---

# Chapter 16: Product Reviews

Allow customers to leave reviews and ratings on products.

## Overview

- **Star Ratings** - 1-5 star rating system
- **Written Reviews** - Customer comments
- **Moderation** - Admin review approval

## Getting Started

```bash
cd "c16 - Product Reviews - Authorize.net"
# or
cd "c16 - Product Reviews - Datacash.com"
docker-compose up -d
```

## Database Schema

```sql
CREATE TABLE review (
    review_id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES product(product_id),
    customer_id INTEGER REFERENCES customer(customer_id),
    rating SMALLINT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved BOOLEAN DEFAULT FALSE
);
```

## Features

- Display average rating on product listings
- Show individual reviews on product pages
- Require purchase to leave review (optional)
- Email notification for new reviews

## Next Steps

Continue to [Chapter 17: Connecting to Web Services]({{< relref "/docs/chapters/c17-web-services" >}}).
