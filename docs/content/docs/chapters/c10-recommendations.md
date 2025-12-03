---
title: "c10 - Product Recommendations"
weight: 10
---

# Chapter 10: Product Recommendations

Implement "Customers who bought this also bought" recommendations.

## Overview

- **Recommendation Engine** - Based on order history
- **"Also Bought" Feature** - Product associations
- **Display Integration** - Show on product pages

## Getting Started

```bash
cd "c10 - Product Recommendations"
docker-compose up -d
```

## Algorithm

```sql
-- Find products frequently bought together
SELECT p.product_id, p.name, COUNT(*) as frequency
FROM order_detail od1
JOIN order_detail od2 ON od1.order_id = od2.order_id
JOIN product p ON od2.product_id = p.product_id
WHERE od1.product_id = :current_product
  AND od2.product_id != :current_product
GROUP BY p.product_id, p.name
ORDER BY frequency DESC
LIMIT 5;
```

## Next Steps

Continue to [Chapter 11: Managing Customer Details]({{< relref "/docs/chapters/c11-customers" >}}).
