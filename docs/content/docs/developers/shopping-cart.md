````markdown
---
title: "Shopping Cart (Chapter 8)"
weight: 6
bookToc: true
---

# Shopping Cart (Chapter 8)

This document describes the Shopping Cart integration (Chapter 8) added to the HatShop codebase. It covers how the feature is enabled, where the files live, important constants/APIs, and quick steps to test locally.

## Overview

- The Shopping Cart business logic is implemented as a namespaced PHP class and integrates with the existing PDO-based `DatabaseHandler`.
- The feature is guarded by a feature flag: `HATSHOP_FEATURE_SHOPPING_CART` (defaults off). You can also enable the shopping cart by setting `HATSHOP_CHAPTER_LEVEL=8`.
- The cart uses a session + cookie-based cart ID (SHA-512) stored in the user's session and in a `HATSHOP_CART_ID` cookie (7-day expiry).

## Enabling the Shopping Cart

- Environment variable (recommended):

```bash
# Enable all features through chapter 8
export HATSHOP_CHAPTER_LEVEL=8

# Or enable only the shopping cart
export HATSHOP_FEATURE_SHOPPING_CART=true
```

- In Docker Compose or Kubernetes, set the variables in the service `environment` or ConfigMap.

## Important files and locations

- Core business class: `src/core/ShoppingCart.php` — primary API for cart operations (add, update, remove, save for later, move to cart, list, totals).
- Configuration constants: `src/core/Config.php` — cart action and query constants were added for clarity and backward compatibility.
- Feature flags: `src/core/FeatureFlags.php` — chapter 8 mapping includes `FEATURE_SHOPPING_CART`.

- Presentation (Smarty plugins):
  - `src/app/html/presentation/smarty_plugins/function.load_cart_summary.php` — sidebar summary data
  - `src/app/html/presentation/smarty_plugins/function.load_cart_details.php` — full cart page data & action handling

- Templates:
  - `src/app/html/presentation/templates/cart_summary.tpl` — sidebar/cart summary cell
  - `src/app/html/presentation/templates/cart_details.tpl` — cart details page

- Integration points:
  - `src/app/html/index.php` — routes `CartAction` requests to the cart details template when the feature is enabled
  - Product templates and listing plugins were extended to render "Add to Cart" buttons when the feature is active

## Constants and Parameters

- Cart actions and queries are exposed as constants in `Config.php`. They include (names for reference):
  - `cart_get_products` (get products in cart)
  - `cart_get_saved_products`
  - `cart_action_add`
  - `cart_action_remove`
  - `cart_action_update`
  - `cart_action_save_for_later`
  - `cart_action_move_to_cart`

- The cart action URL and parameter names are constructed centrally in the cart-related plugins (see `function.load_cart_details.php` and `ShoppingCart.php`). These constants avoid duplicated literals and simplify linking.

## Quick test (local)

1. Ensure your environment enables the shopping cart (either `HATSHOP_CHAPTER_LEVEL=8` or `HATSHOP_FEATURE_SHOPPING_CART=true`).
2. Start the appropriate chapter environment (or the unified app) using the chapter's `docker-compose.yml` under `src/`.

```bash
# Example: start a chapter environment that includes the application
cd "src/c03 - Creating the Product Catalog Part I"
docker-compose up -d

# Set feature and open site in browser
export HATSHOP_FEATURE_SHOPPING_CART=true
open http://localhost:8080
```

3. Browse a product and click the "Add to Cart" button (visible only when the shopping cart feature is enabled). Visit the Cart page to confirm items and totals.

## Testing and Code Quality

- The C08 code introduced during the integration was scanned with SonarQube. Duplicate literals were extracted into constants and helper methods added to reduce cognitive complexity. No CRITICAL/BLOCKER issues remain from the C08 integration.

## Developer notes

- If you need to programmatically manipulate the cart in tests, use the `ShoppingCart` class from `src/core/ShoppingCart.php` and the `FeatureFlags` helper to toggle features in test setup and teardown.
- When adding UI elements for cart operations, guard them with feature checks in Smarty templates, e.g. `{if $features.shopping_cart} ... {/if}`.

## Next steps / TODO

- Add PHPUnit tests for the `ShoppingCart` class (unit + integration) to validate DB interactions and edge cases (concurrent updates, empty carts, product deleted but still in cart).

````
