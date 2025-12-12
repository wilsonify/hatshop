# HatShop Database - Terraform PostgreSQL Provider

This Terraform module manages the HatShop PostgreSQL database schema using the [cyrilgdn/postgresql](https://registry.terraform.io/providers/cyrilgdn/postgresql/latest) provider.

## Overview

This module replaces the traditional SQL script-based database initialization with Infrastructure as Code (IaC) using Terraform. It creates and manages:

- **Tables**: All database tables with proper foreign key relationships
- **Custom Types**: PostgreSQL composite types used by stored functions
- **Functions**: PL/pgSQL stored functions for business logic
- **Indexes**: Full-text search index for product search

## Architecture

```
hatshop-db/
├── backend.tf              # S3 backend configuration
├── provider.tf             # AWS and PostgreSQL provider configuration
├── variables.tf            # Input variables
├── outputs.tf              # Output values
├── versions.tf             # Provider version constraints
├── types.tf                # Custom PostgreSQL types
├── tables.tf               # Database tables
├── functions_catalog.tf    # Catalog-related functions
├── functions_cart.tf       # Shopping cart functions
├── functions_orders.tf     # Order management functions
├── functions_customer.tf   # Customer and review functions
├── terraform.tfvars.example # Example variables file
├── makefile                # Make targets for common operations
└── README.md               # This file
```

## Prerequisites

1. **Terraform** >= 1.0.0
2. **AWS CLI** configured with appropriate credentials
3. **PostgreSQL RDS instance** running (created by `hatshop-rds` module)
4. Network connectivity to the RDS instance

## Database Schema

### Tables

| Table | Description |
|-------|-------------|
| `department` | Product departments |
| `category` | Product categories within departments |
| `product` | Product catalog with full-text search |
| `product_category` | Many-to-many relationship |
| `shopping_cart` | Shopping cart items |
| `shipping_region` | Shipping regions |
| `shipping` | Shipping options per region |
| `tax` | Tax rates |
| `customer` | Customer accounts |
| `orders` | Customer orders |
| `order_detail` | Order line items |
| `audit` | Order audit trail |
| `review` | Product reviews |

### Entity Relationship

```
department (1) ─────< (N) category
                           │
                           │
                           ▼
product (M) ─────< (N) product_category
    │
    ├──< shopping_cart
    ├──< order_detail
    └──< review

customer (1) ─────< (N) orders
    │                     │
    └──< review           ├──< order_detail
                          └──< audit

shipping_region (1) ─────< (N) shipping
         │                        │
         └──< customer            └──< orders

tax (1) ─────< (N) orders
```

## Usage

### 1. Configure Backend

Edit `backend-config.tfbackend` with your S3 bucket details:

```hcl
bucket         = "your-bucket-terraform-state"
key            = "hatshop-db/terraform.tfstate"
region         = "us-east-1"
dynamodb_table = "your-lock-table"
profile        = "your-aws-profile"
```

### 2. Configure Variables

Copy the example file and fill in your values:

```bash
cp terraform.tfvars.example terraform.tfvars
```

Edit `terraform.tfvars`:

```hcl
aws_region         = "us-east-1"
aws_profile        = "default"
aws_account_number = "123456789012"

db_host     = "your-rds-endpoint.rds.amazonaws.com"
db_port     = 5432
db_name     = "hatshop"
db_username = "pgadmin"
db_password = "your-secure-password"
db_sslmode  = "require"

create_types     = true
create_functions = true
```

### 3. Initialize and Apply

```bash
# Initialize Terraform
make init

# Preview changes
make plan

# Apply changes
make apply
```

## Feature Flags

Control what gets created:

| Variable | Default | Description |
|----------|---------|-------------|
| `create_types` | `true` | Create custom PostgreSQL types |
| `create_functions` | `true` | Create stored functions |
| `create_sample_data` | `false` | Insert sample data (future) |

## Migration from SQL Scripts

This module replaces the shell script-based database initialization located in:
`src/c00 - PostgreSQL Image/Database Complete/`

### Benefits of Terraform Approach

1. **State Management**: Track what's deployed and detect drift
2. **Idempotency**: Safe to run multiple times
3. **Dependencies**: Automatic ordering of resource creation
4. **Plan Preview**: See changes before applying
5. **Rollback**: Easy destroy and recreate
6. **Version Control**: Database schema as code

### Migration Steps

1. Deploy RDS instance using `hatshop-rds` module
2. Configure this module with RDS endpoint
3. Run `terraform apply` to create schema
4. Verify with `psql` or database client

## Stored Functions

### Catalog Functions

- `catalog_get_departments_list()` - List all departments
- `catalog_get_department_details(department_id)` - Get department details
- `catalog_get_categories_list(department_id)` - List categories in department
- `catalog_get_products_in_category(...)` - Paginated product list
- `catalog_search(words[], all_words, ...)` - Full-text product search
- `catalog_add_department(name, description)` - Create department
- `catalog_update_department(id, name, description)` - Update department
- `catalog_delete_department(id)` - Delete department

### Shopping Cart Functions

- `shopping_cart_add_product(cart_id, product_id)` - Add to cart
- `shopping_cart_update(cart_id, product_id, quantity)` - Update quantity
- `shopping_cart_remove_product(cart_id, product_id)` - Remove from cart
- `shopping_cart_get_products(cart_id)` - Get cart contents
- `shopping_cart_get_total_amount(cart_id)` - Calculate total
- `shopping_cart_create_order(...)` - Convert cart to order

### Order Functions

- `orders_get_most_recent_orders(count)` - Recent orders
- `orders_get_order_info(order_id)` - Full order details
- `orders_get_order_details(order_id)` - Order line items
- `orders_update_status(order_id, status)` - Update status

### Customer Functions

- `customer_get_login_info(email)` - Get login credentials
- `customer_add(name, email, password)` - Register customer
- `customer_get_customer(customer_id)` - Get customer details
- `customer_update_account(...)` - Update profile

## Troubleshooting

### Connection Issues

Ensure your IP is allowed in the RDS security group and the database is publicly accessible (for development).

### Function Dependencies

Functions depend on types and tables. If you get errors about missing types, ensure `create_types = true`.

### State Corruption

If Terraform state gets corrupted:

```bash
make clean
make init
terraform import ...  # Re-import resources as needed
```

## Security Considerations

1. Store `terraform.tfvars` securely (use `.gitignore`)
2. Use AWS Secrets Manager for production passwords
3. Restrict RDS security group to necessary IPs
4. Enable SSL (`db_sslmode = "require"`)
5. Use IAM database authentication for production

## Contributing

1. Make changes to `.tf` files
2. Run `make fmt` to format
3. Run `make validate` to check syntax
4. Run `make plan` to preview
5. Submit PR with plan output
