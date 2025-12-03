---
title: "c05 - Searching the Catalog"
weight: 5
---

# Chapter 05: Searching the Catalog

Implement full-text search functionality using PostgreSQL's built-in search capabilities.

## Overview

- **Full-Text Search** - PostgreSQL `tsvector` and `tsquery`
- **Search Results** - Ranked results display
- **Search Box** - User interface component

## Getting Started

```bash
cd "c05 - Searching the Catalog"
docker-compose up -d
```

## Database Changes

```sql
-- Add search vectors
ALTER TABLE product ADD COLUMN search_vector tsvector;

-- Create search index
CREATE INDEX idx_product_search ON product USING gin(search_vector);

-- Update trigger
CREATE TRIGGER product_search_update
BEFORE INSERT OR UPDATE ON product
FOR EACH ROW EXECUTE FUNCTION
tsvector_update_trigger(search_vector, 'pg_catalog.english', name, description);
```

## Search Implementation

```php
class Catalog {
    public static function search(string $query, int $page = 1): array {
        $sql = "SELECT product_id, name, description, price, thumbnail
                FROM product
                WHERE search_vector @@ plainto_tsquery(:query)
                ORDER BY ts_rank(search_vector, plainto_tsquery(:query)) DESC
                LIMIT :limit OFFSET :offset";
        // ...
    }
}
```

## Next Steps

Continue to [Chapter 06: Receiving Payments Using PayPal]({{< relref "/docs/chapters/c06-paypal" >}}).
