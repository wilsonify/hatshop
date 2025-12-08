---
title: "c07 - Catalog Administration"
weight: 7
---

# Chapter 07: Catalog Administration

Manage your store's departments, categories, and products through a secure administration interface.

## Overview

- **Department Management** - Create, edit, and delete store departments
- **Category Management** - Organize products into categories within departments
- **Product Management** - Full CRUD operations for products with image uploads
- **Product Assignment** - Assign products to categories and control display options
- **Secure Login** - Password-protected admin area

## Getting Started

### Using the Unified App

The Catalog Admin feature is integrated into the unified application. Enable it by setting the chapter level to 7:

```bash
# In your .env file
HATSHOP_CHAPTER_LEVEL=7
```

Or enable Catalog Admin specifically:

```bash
HATSHOP_FEATURE_CATALOG_ADMIN=true
```

### Running with Docker Compose

```bash
cd deploy/01_dev/hatshop
docker-compose up -d
```

Access the admin interface at: `http://localhost/admin.php`

## Admin Configuration

Configure admin credentials via environment variables:

```bash
# Admin Settings
ADMIN_USERNAME=hatshopadmin
ADMIN_PASSWORD=hatshopadmin
```

### Configuration Options

| Variable | Description | Default |
|----------|-------------|---------|
| `ADMIN_USERNAME` | Admin login username | `hatshopadmin` |
| `ADMIN_PASSWORD` | Admin login password | `hatshopadmin` |

## Features Enabled

When Catalog Admin is enabled, the following functionality becomes available:

### Department Management

- **List Departments** - View all departments with names and descriptions
- **Add Department** - Create new departments
- **Edit Department** - Modify department name and description
- **Delete Department** - Remove empty departments (must have no categories)

### Category Management

- **List Categories** - View categories within a selected department
- **Add Category** - Create new categories in a department
- **Edit Category** - Modify category name and description
- **Delete Category** - Remove empty categories (must have no products)

### Product Management

- **List Products** - View all products in a selected category
- **Add Product** - Create new products with name, description, and price
- **Edit Product** - Modify product details, images, and display options
- **Delete Product** - Remove products from the catalog
- **Product Images** - Upload and manage product images and thumbnails

### Display Options

Control where products appear in the store:

| Option | Display Location |
|--------|------------------|
| Default | Not shown on front page |
| On Catalog | Shown on main catalog page |
| On Department | Shown on department page |
| On Both | Shown on both catalog and department pages |

## Admin Interface Pages

### Login Page

- URL: `/admin.php`
- Requires username and password
- Session-based authentication

### Main Menu

After login, access:

- **Departments** - Manage store departments
- **Categories** - Manage categories (select department first)
- **Products** - Manage products (select category first)
- **Logout** - End admin session

### Department Administration

- URL: `/admin.php?Page=Departments`
- Add, edit, delete departments
- Click department name to manage its categories

### Category Administration

- URL: `/admin.php?Page=Categories&DepartmentId={id}`
- Add, edit, delete categories within a department
- Click category name to manage its products

### Product Administration

- URL: `/admin.php?Page=Products&CategoryId={id}&DepartmentId={id}`
- List products in a category
- Add new products to category
- Edit or delete existing products

### Product Details

- URL: `/admin.php?Page=ProductDetails&ProductId={id}&...`
- Edit product name, description, price
- Set display option
- Upload product image and thumbnail
- Assign product to additional categories
- Move product to different category

## Database Schema

The Catalog Admin feature uses existing database tables:

- `department` - Store departments
- `category` - Product categories
- `product` - Product catalog
- `product_category` - Product-to-category assignments

## Security Considerations

1. **Change Default Credentials** - Always change the default admin username and password in production
2. **Use HTTPS** - Protect admin credentials in transit
3. **Session Security** - Admin sessions use PHP's session management
4. **Input Validation** - All user input is validated and sanitized

## Using in Templates

The Catalog Admin feature is automatically available in Smarty templates:

```smarty
{* Check if Catalog Admin is enabled *}
{if $features.catalog_admin}
    {* Admin link can be displayed *}
    <a href="admin.php">Admin</a>
{/if}
```

## Stored Procedures

The following stored procedures support admin functionality:

### Departments

- `catalog_get_departments_with_descriptions()` - List departments with descriptions
- `catalog_add_department()` - Create department
- `catalog_update_department()` - Update department
- `catalog_delete_department()` - Delete department

### Categories

- `catalog_get_department_categories()` - List categories in department
- `catalog_add_category()` - Create category
- `catalog_update_category()` - Update category
- `catalog_delete_category()` - Delete category

### Products

- `catalog_get_category_products()` - List products in category
- `catalog_add_product_to_category()` - Create product in category
- `catalog_update_product()` - Update product details
- `catalog_delete_product()` - Delete product
- `catalog_remove_product_from_category()` - Remove product-category assignment
- `catalog_get_product_info()` - Get product details
- `catalog_get_categories_for_product()` - Get assigned categories
- `catalog_set_product_display_option()` - Set display option
- `catalog_assign_product_to_category()` - Assign to additional category
- `catalog_move_product_to_category()` - Move to different category
- `catalog_set_image()` - Update product image
- `catalog_set_thumbnail()` - Update product thumbnail
