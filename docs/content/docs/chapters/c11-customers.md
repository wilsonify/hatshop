---
title: "c11 - Customer Details"
weight: 11
---

# Chapter 11: Managing Customer Details

Implement customer registration, login, and profile management.

## Overview

- **Registration** - New customer signup
- **Authentication** - Secure login
- **Profile Management** - Update customer info
- **Address Book** - Shipping addresses

## Getting Started

```bash
cd "c11 - Managing Customer Details"
docker-compose up -d
```

## Database Schema

```sql
CREATE TABLE customer (
    customer_id SERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    phone VARCHAR(20),
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customer_address (
    address_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES customer(customer_id),
    address_1 VARCHAR(100),
    address_2 VARCHAR(100),
    city VARCHAR(50),
    region VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    is_default BOOLEAN DEFAULT FALSE
);
```

## Security

- Passwords hashed with `password_hash()`
- CSRF protection on forms
- Session security measures

## Next Steps

Continue to [Chapter 12: Storing Customer Orders]({{< relref "/docs/chapters/c12-order-storage" >}}).
