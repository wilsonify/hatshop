---
title: "PostgreSQL Database"
weight: 10
bookToc: true
---

# PostgreSQL Database Deployment and Administration

This guide covers deploying, configuring, and troubleshooting the PostgreSQL database for HatShop.

## Overview

HatShop uses PostgreSQL as its primary database for storing products, categories, customers, orders, and shopping cart data. The database is deployed as a Docker container alongside the application services.

### Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Network                        │
│                                                         │
│  ┌──────────────┐         ┌──────────────────────────┐ │
│  │   PHP App    │────────►│      PostgreSQL          │ │
│  │  (hatshop)   │  :5432  │   (hatshop-postgres)     │ │
│  └──────────────┘         │                          │ │
│                           │  Database: hatshop       │ │
│                           │  User: hatshop           │ │
│                           │  Volume: postgres-data   │ │
│                           └──────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Connection Details

| Parameter | Value |
|-----------|-------|
| Host | `postgres` (Docker service name) |
| Port | `5432` |
| Database | `hatshop` |
| Username | `hatshop` |
| Password | Configured in `.env` |

## Deployment

### Docker Compose Configuration

The PostgreSQL service is defined in `deploy/01_dev/hatshop/docker-compose.yaml`:

```yaml
services:
  postgres:
    build:
      context: "../../../src/c00 - PostgreSQL Image"
    restart: unless-stopped
    environment:
      POSTGRES_USER: hatshop
      POSTGRES_PASSWORD: changeme
      POSTGRES_DB: hatshop
    volumes:
      - postgres-data:/var/lib/postgresql
      - ../../../Database Complete/split_sql_files:/docker-entrypoint-initdb.d:ro
    expose:
      - "5432"
    networks:
      - hatshop-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U hatshop -d hatshop"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  postgres-data:
```

### Environment Variables

Configure the PHP application to connect to PostgreSQL in `.env`:

```bash
# Database Configuration
HATSHOP_DB_SERVER=postgres
HATSHOP_DB_USERNAME=hatshop
HATSHOP_DB_PASSWORD=changeme
HATSHOP_DB_DATABASE=hatshop
```

### Initial Deployment

```bash
cd deploy/01_dev/hatshop

# Start PostgreSQL first (it initializes the database)
docker compose up -d postgres

# Wait for health check to pass
docker compose ps

# Start remaining services
docker compose up -d
```

### Database Initialization

On first startup, PostgreSQL automatically executes SQL scripts from the `docker-entrypoint-initdb.d` directory. The scripts in `Database Complete/split_sql_files/` create:

1. **Tables** (01-15): Core database schema
2. **Types** (16-35): Custom PostgreSQL types
3. **Functions** (36-99): Stored procedures for catalog, cart, orders
4. **Additional Functions** (9100-9108): Customer and review functions

Scripts are executed in alphabetical order by filename.

## Database Schema

### Core Tables

| Table | Description |
|-------|-------------|
| `department` | Product departments (Holiday, Caps, etc.) |
| `category` | Product categories within departments |
| `product` | Product catalog with descriptions and prices |
| `product_category` | Many-to-many product-category relationships |
| `shopping_cart` | Active shopping cart items |
| `customer` | Customer accounts and profiles |
| `orders` | Order headers |
| `order_detail` | Order line items |
| `shipping_region` | Shipping destination regions |
| `shipping` | Shipping options and rates |
| `tax` | Tax configuration by region |
| `audit` | Order audit trail |
| `review` | Product reviews |

### Key Functions

| Function | Description |
|----------|-------------|
| `catalog_get_departments_list()` | List all departments |
| `catalog_get_products_in_category()` | Get products for a category |
| `shopping_cart_add_product()` | Add item to cart |
| `shopping_cart_create_order()` | Convert cart to order |
| `customer_add()` | Register new customer |

## Operations

### Connecting to PostgreSQL

```bash
# Interactive psql shell
docker exec -it hatshop-postgres-1 psql -U hatshop -d hatshop

# Run a single query
docker exec hatshop-postgres-1 psql -U hatshop -d hatshop -c "SELECT * FROM department;"
```

### Common Queries

```sql
-- List all departments
SELECT * FROM department;

-- Count products by category
SELECT c.name, COUNT(pc.product_id) 
FROM category c 
LEFT JOIN product_category pc ON c.category_id = pc.category_id 
GROUP BY c.name;

-- Check shopping cart contents
SELECT * FROM shopping_cart WHERE cart_id = 'your-cart-id';

-- List recent orders
SELECT * FROM orders ORDER BY created_on DESC LIMIT 10;
```

### Backup and Restore

#### Create Backup

```bash
# Backup to SQL file
docker exec hatshop-postgres-1 pg_dump -U hatshop hatshop > backup.sql

# Backup with compression
docker exec hatshop-postgres-1 pg_dump -U hatshop hatshop | gzip > backup.sql.gz
```

#### Restore from Backup

```bash
# Restore from SQL file
cat backup.sql | docker exec -i hatshop-postgres-1 psql -U hatshop -d hatshop

# Restore from compressed backup
gunzip -c backup.sql.gz | docker exec -i hatshop-postgres-1 psql -U hatshop -d hatshop
```

### View Logs

```bash
# Recent logs
docker logs hatshop-postgres-1

# Follow logs in real-time
docker logs -f hatshop-postgres-1

# Last 50 lines
docker logs --tail=50 hatshop-postgres-1
```

### Check Health Status

```bash
# Container health
docker inspect --format='{{.State.Health.Status}}' hatshop-postgres-1

# Manual health check
docker exec hatshop-postgres-1 pg_isready -U hatshop -d hatshop
```

## Troubleshooting

### Connection Refused

**Symptom**: PHP application shows "connection refused" error

```
SQLSTATE[08006] [7] connection to server at "postgres" (172.x.x.x), port 5432 failed: Connection refused
```

**Diagnosis**:

```bash
# Check if postgres container is running
docker compose ps postgres

# Check postgres logs
docker logs hatshop-postgres-1

# Test connectivity from PHP container
docker exec hatshop-php-1 sh -c "nc -zv postgres 5432"
```

**Solutions**:

1. **Container not running**: Start the container
   ```bash
   docker compose up -d postgres
   ```

2. **Container unhealthy**: Check logs and restart
   ```bash
   docker logs hatshop-postgres-1
   docker compose restart postgres
   ```

3. **Network issue**: Verify containers are on same network
   ```bash
   docker network inspect hatshop_hatshop-network
   ```

### Authentication Failed

**Symptom**: "password authentication failed for user"

```
FATAL: password authentication failed for user "hatshop"
```

**Diagnosis**:

```bash
# Check environment variables in PHP container
docker exec hatshop-php-1 env | grep HATSHOP_DB

# Check postgres user exists
docker exec hatshop-postgres-1 psql -U hatshop -c "\du"
```

**Solutions**:

1. **Wrong password in .env**: Verify `HATSHOP_DB_PASSWORD` matches `POSTGRES_PASSWORD`

2. **Shell environment override**: Check if shell has conflicting variables
   ```bash
   env | grep HATSHOP_DB
   ```
   If so, use explicit values in docker-compose.yaml

3. **Database not initialized**: Remove volume and recreate
   ```bash
   docker compose down -v
   docker compose up -d
   ```

### Database Not Found

**Symptom**: "database does not exist"

**Solutions**:

1. Check database was created:
   ```bash
   docker exec hatshop-postgres-1 psql -U hatshop -l
   ```

2. If missing, recreate:
   ```bash
   docker compose down -v
   docker compose up -d postgres
   ```

### Init Script Errors

**Symptom**: Database starts but tables/functions are missing

**Diagnosis**:

```bash
# Check init script execution
docker logs hatshop-postgres-1 | grep -i error

# Verify tables exist
docker exec hatshop-postgres-1 psql -U hatshop -d hatshop -c "\dt"
```

**Common Causes**:

1. **Script ordering issue**: Files are executed alphabetically. Ensure dependencies are created first.

2. **Syntax error in SQL**: Check the specific script mentioned in logs

3. **Partial initialization**: If postgres was stopped mid-init, remove volume and restart:
   ```bash
   docker compose down -v
   docker compose up -d
   ```

### PostgreSQL 18+ Volume Layout

**Symptom**: Error about data in wrong directory

```
Error: in 18+, these Docker images are configured to store database data...
Counter to that, there appears to be PostgreSQL data in:
  /var/lib/postgresql/data (unused mount/volume)
```

**Solution**: PostgreSQL 18+ changed the data directory layout. Mount at `/var/lib/postgresql` instead of `/var/lib/postgresql/data`:

```yaml
volumes:
  - postgres-data:/var/lib/postgresql  # Correct for PostgreSQL 18+
```

If upgrading from older version, backup data first, then:
```bash
docker compose down -v
docker compose up -d
```

### Slow Queries

**Diagnosis**:

```sql
-- Enable query logging (connect to postgres)
ALTER SYSTEM SET log_statement = 'all';
SELECT pg_reload_conf();

-- Check slow queries
SELECT * FROM pg_stat_activity WHERE state = 'active';
```

**Solutions**:

1. Add missing indexes
2. Analyze tables: `ANALYZE;`
3. Check for sequential scans: `EXPLAIN ANALYZE your_query;`

### Disk Space Issues

**Diagnosis**:

```bash
# Check volume size
docker system df -v | grep postgres

# Check database size
docker exec hatshop-postgres-1 psql -U hatshop -d hatshop \
  -c "SELECT pg_size_pretty(pg_database_size('hatshop'));"
```

**Solutions**:

1. Clean old shopping carts:
   ```sql
   SELECT shopping_cart_delete_old_carts(7);  -- Delete carts older than 7 days
   ```

2. Vacuum database:
   ```bash
   docker exec hatshop-postgres-1 vacuumdb -U hatshop -d hatshop --full
   ```

## Data Reset

### Reset Database (Development Only)

To completely reset the database to initial state:

```bash
# Stop all services
docker compose down

# Remove the postgres volume
docker volume rm hatshop_postgres-data

# Restart services (will re-run init scripts)
docker compose up -d
```

### Clear Shopping Carts

```sql
-- Delete all shopping cart items
TRUNCATE shopping_cart;

-- Or use the built-in function (keeps recent carts)
SELECT shopping_cart_delete_old_carts(0);
```

## Security Considerations

1. **Never use default passwords in production**
2. **Restrict network access** - postgres should only be accessible from the application containers
3. **Regular backups** - especially before deployments
4. **Monitor for unusual activity** - check logs regularly
5. **Keep PostgreSQL updated** - use latest stable images

## Related Documentation

- [Deploy to Dev Environment]({{< relref "/docs/admins/deploy-dev" >}})
- [Feature Flags]({{< relref "/docs/admins/feature-flags" >}})
- [Database Complete Scripts](https://github.com/wilsonify/hatshop/tree/master/Database%20Complete)
