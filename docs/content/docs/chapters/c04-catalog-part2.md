---
title: "c04 - Product Catalog Part II"
weight: 4
---

# Chapter 04: Creating the Product Catalog Part II

This chapter extends the catalog with product details, categories, and image handling.

## Overview

Key features introduced:
- **Categories** - Organize products within departments
- **Product Details** - Full product information pages
- **Thumbnails** - Product image thumbnails
- **Pagination** - Browse large product lists

## Getting Started

```bash
cd "c04 - Creating the Product Catalog Part II"
docker-compose up -d
```

## New Components

### Categories Table

```sql
CREATE TABLE category (
    category_id SERIAL PRIMARY KEY,
    department_id INTEGER REFERENCES department(department_id),
    name VARCHAR(100) NOT NULL,
    description VARCHAR(1000)
);
```

### Products Table

```sql
CREATE TABLE product (
    product_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(1000),
    price DECIMAL(10,2) NOT NULL,
    thumbnail VARCHAR(150),
    image VARCHAR(150),
    display SMALLINT DEFAULT 0
);
```

### Catalog Methods

```php
class Catalog {
    public static function getCategories(int $departmentId): array;
    public static function getProducts(int $categoryId): array;
    public static function getProduct(int $productId): array;
}
```

## What You'll Learn

1. Relational database design
2. Product image management
3. Multi-level navigation
4. Pagination techniques

## Next Steps

Continue to [Chapter 05: Searching the Catalog]({{< relref "/docs/chapters/c05-search" >}}).
