---
title: "c07 - Catalog Administration"
weight: 7
---

# Chapter 07: Catalog Administration

Build an admin interface for managing products, categories, and departments.

## Overview

- **Admin Panel** - Secure administration area
- **CRUD Operations** - Create, Read, Update, Delete
- **Image Upload** - Product image management

## Getting Started

```bash
cd "c07 - Catalog Administration"
docker-compose up -d
```

## Admin Features

### Department Management
- Add new departments
- Edit department details
- Delete empty departments

### Category Management
- Add categories to departments
- Edit category names and descriptions
- Move categories between departments

### Product Management
- Add new products
- Upload product images
- Set prices and descriptions
- Assign to categories

## Security

⚠️ The admin panel must be protected:
- HTTP Basic Auth
- Session-based authentication
- IP whitelisting

## Next Steps

Continue to [Chapter 08: The Shopping Cart]({{< relref "/docs/chapters/c08-shopping-cart" >}}).
