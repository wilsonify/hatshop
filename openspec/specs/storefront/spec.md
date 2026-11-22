# Storefront

## Purpose

The public shop: catalog browsing, product pages, search and the assets that
style them.

## Requirements

### Requirement: Catalog pages render from the database

The Worker SHALL render the home, department, category, search and product pages from the existing catalog functions, including pagination and full-text search, and SHALL NOT keep a second catalogue in the isolate. Verified by `tests/routes.test.ts`.

#### Scenario: The home page lists the catalog
- **WHEN** a visitor requests `/`
- **THEN** the response is 200 and lists the products the database returns for the first catalog page, with prices and department navigation

#### Scenario: A product page shows one product
- **WHEN** a visitor requests `/products/:id` for a product that exists
- **THEN** the response is 200 with that product's details, reviews and recommendations

#### Scenario: An unknown identifier is a 404
- **WHEN** a visitor requests a product, department or category id that does not exist
- **THEN** the response is 404 with the site's error page, not a 500

### Requirement: Pagination is clamped to the available range

Pagination SHALL derive the page count from the database total, clamp the requested page into the range that exists, and take the page size from configuration. Verified by `tests/routes.test.ts`.

#### Scenario: Out-of-range pages resolve to a real page
- **WHEN** a visitor requests page zero, a negative page, or a page past the last one
- **THEN** the first or last page is rendered and the pager marks that page as current

#### Scenario: A single page hides the pager
- **WHEN** a listing fits on one page
- **THEN** no pagination controls are rendered

### Requirement: Every interpolated value is escaped

Every dynamic value written into HTML SHALL pass through the escape helper; no view concatenates request or database text directly, and the rendered page is served under a Content-Security-Policy that forbids inline script and style. Verified by `tests/template-contract.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: Stored text cannot inject markup
- **WHEN** a product name or review body contains HTML characters
- **THEN** the page shows them as text and the response still passes the security-header checks

### Requirement: The served HTML keeps its documented structure

Pages SHALL keep the ids, classes, form fields, action URLs and links that the markup contract asserts, so the stylesheet and the single ES module keep working. Verified by `tests/template-contract.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: The catalog page keeps its product cards
- **WHEN** any catalog page is rendered with products
- **THEN** each product appears with the contract's card class and a link to its product page

### Requirement: Search supports all-words mode and paging

The search page SHALL pass the query, the all-words flag and the page to the search function, and SHALL render an empty-result state instead of an error when nothing matches. Verified by `tests/routes.test.ts`.

#### Scenario: All-words mode is forwarded
- **WHEN** a visitor searches with the all-words flag set
- **THEN** the flag reaches the search call and the pager keeps it in its links

#### Scenario: No matches is an empty state
- **WHEN** a search matches nothing
- **THEN** the page is 200 and says so, with no pagination

### Requirement: Static assets bypass the Worker

Files under `public/` SHALL be served by the asset binding, so stylesheets, the ES module and product images cost no Worker CPU. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: A stylesheet is served as CSS
- **WHEN** a visitor requests `/css/site.css`
- **THEN** the response is 200 with `content-type: text/css`

#### Scenario: Files that are not assets are not served
- **WHEN** a visitor requests a path that has no asset behind it
- **THEN** the response is 404
