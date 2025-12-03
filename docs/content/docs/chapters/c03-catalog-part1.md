---
title: "c03 - Creating the Product Catalog Part I"
weight: 3
---

# Chapter 03: Creating the Product Catalog Part I

This chapter introduces the database layer and product catalog with departments and categories.

## Overview

Key features introduced:
- **PostgreSQL Database** - Data persistence
- **Database Handler** - PDO singleton pattern
- **Catalog Class** - Product data access
- **Departments List** - Navigation component

## Getting Started

```bash
cd "src/c03 - Creating the Product Catalog Part I"
docker-compose up -d
```

Visit [http://localhost:8080](http://localhost:8080)

## Architecture

### Directory Structure

```
html/
├── business/
│   ├── database_handler.php  # PDO singleton
│   ├── catalog.php           # Catalog data access
│   └── error_handler.php     # Enhanced error handling
├── presentation/
│   ├── page.php
│   ├── templates/
│   │   └── departments_list.tpl
│   └── smarty_plugins/
│       ├── function.load_departments_list.php
│       └── modifier.prepare_link.php
└── tests/                    # PHPUnit tests
```

### Database Schema

```sql
-- Departments table
CREATE TABLE department (
    department_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(1000)
);

-- Sample data
INSERT INTO department (name, description) VALUES
    ('Regional', 'Hats from different regions'),
    ('Nature', 'Nature-inspired hats'),
    ('Seasonal', 'Seasonal hat collection');
```

### Database Handler

Singleton pattern for database connections:

```php
class DatabaseHandler {
    private static ?PDO $mHandler = null;
    
    public static function getHandler(): PDO {
        if (self::$mHandler === null) {
            $dsn = 'pgsql:host=' . DB_HOST . ';dbname=' . DB_DATABASE;
            self::$mHandler = new PDO($dsn, DB_USER, DB_PASSWORD);
            self::$mHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$mHandler;
    }
}
```

### Catalog Class

Data access for products:

```php
class Catalog {
    public static function getDepartments(): array {
        $sql = 'SELECT department_id, name, description FROM department ORDER BY department_id';
        $stmt = DatabaseHandler::getHandler()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Smarty Plugin

Custom function to load departments:

```php
// function.load_departments_list.php
function smarty_function_load_departments_list($params, $template) {
    $template->assign($params['assign'], Catalog::getDepartments());
}
```

### Template

```smarty
{* departments_list.tpl *}
{load_departments_list assign="departments"}
<ul class="departments">
    {foreach $departments as $dept}
        <li>
            <a href="index.php?DepartmentId={$dept.department_id}">
                {$dept.name}
            </a>
        </li>
    {/foreach}
</ul>
```

## Testing

Comprehensive tests for all components:

```bash
cd html
composer install
./vendor/bin/phpunit
```

### Test Coverage

- `DatabaseHandlerTest` - Connection handling
- `CatalogTest` - Data access methods
- `LinkModifierTest` - URL generation
- `DepartmentsListTest` - Smarty plugin

## Database Setup

The database is automatically initialized by Docker:

```yaml
# docker-compose.yaml
services:
  db:
    image: postgres:15
    environment:
      POSTGRES_DB: hatshop
      POSTGRES_USER: hatshop
      POSTGRES_PASSWORD: hatshop
    volumes:
      - ./database:/docker-entrypoint-initdb.d
```

## Key Files

| File | Purpose |
|------|---------|
| `business/database_handler.php` | Database connection singleton |
| `business/catalog.php` | Catalog data access |
| `database/hatshop.sql` | Database schema |
| `presentation/smarty_plugins/function.load_departments_list.php` | Departments loader |

## What You'll Learn

1. Connecting PHP to PostgreSQL with PDO
2. Implementing the singleton pattern
3. Creating Smarty custom functions
4. Writing unit tests for database code

## Common Issues

### Connection Refused

If you see:
```
SQLSTATE[08006] [7] connection refused
```

Ensure the database container is running:
```bash
docker-compose ps
docker-compose logs db
```

### Smarty Plugin Not Found

Ensure plugins are registered in `page.php`:
```php
$this->registerPlugin('function', 'load_departments_list', 
    'smarty_function_load_departments_list');
```

## Next Steps

Continue to [Chapter 04: Product Catalog Part II]({{< relref "/docs/chapters/c04-catalog-part2" >}}) to add product details and images.
