# Cart

## Purpose

The shopping cart: its identity, its mutations, and the totals and saved items it
reports.

## Requirements

### Requirement: The cart is identified by a signed cookie

A cart SHALL be identified by a 128-hex-character id carried in a signed cookie; a visitor without one SHALL be issued one, and an unverifiable value SHALL be replaced rather than trusted. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: A first visit receives a cart
- **WHEN** a visitor requests a page without a cart cookie
- **THEN** the response sets a signed cart cookie whose id is 128 hex characters

#### Scenario: A tampered cart cookie is replaced
- **WHEN** a request carries a cart cookie whose signature does not verify
- **THEN** the request is served with a fresh cart id and no trust in the presented value

### Requirement: Each mutation maps to one database call

`POST /cart` SHALL support add, update, remove, save-for-later and move-to-cart, each issuing exactly one cart function; an unknown action or an invalid product id SHALL be rejected as a validation error before any SQL runs. Verified by `tests/cart.test.ts`.

#### Scenario: A supported action reaches its function once
- **WHEN** a visitor submits `action=add` with a valid product id
- **THEN** exactly one cart call is made, with the cart id as its first parameter

#### Scenario: An unsupported action is refused
- **WHEN** a visitor submits an action the application does not implement
- **THEN** the request fails validation and no database call is made

### Requirement: A zero quantity removes the line

A quantity update SHALL treat zero as removal and send it to the remove function, and SHALL reject updates whose product and quantity arrays differ in length. Verified by `tests/cart.test.ts`.

#### Scenario: Quantity zero removes the line
- **WHEN** a visitor updates one product to quantity 0 and another to quantity 2
- **THEN** one removal and one update are issued, and the zero line is gone from the cart

#### Scenario: Mismatched quantity arrays are refused
- **WHEN** a visitor submits two product ids and one quantity
- **THEN** the request fails validation

### Requirement: Totals and counts come from the database

The cart page and its badge SHALL use the total, item count and saved-item list returned by the database; an empty cart SHALL report `0.00` and empty, never null. Verified by `tests/cart.test.ts`.

#### Scenario: A populated cart reports the database total
- **WHEN** the cart holds two of one product and one saved product
- **THEN** the page shows the database total, the item count and the saved list separately

#### Scenario: An empty cart shows zero
- **WHEN** the cart has no lines and the database returns a null total
- **THEN** the page shows `0.00` and the empty state

### Requirement: Cart mutations require CSRF and a same-origin request

Every cart POST SHALL require a token matching its cookie, a same-origin `Origin`, and a non-cross-site `Sec-Fetch-Site`; otherwise it SHALL answer 403 and leave the affected rows unchanged. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: A missing token is refused
- **WHEN** a cart POST arrives without the CSRF field
- **THEN** the response is 403 and no cart row changes

#### Scenario: A foreign origin is refused
- **WHEN** a cart POST arrives with a token but an `Origin` from another site
- **THEN** the response is 403
