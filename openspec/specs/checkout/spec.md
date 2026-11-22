# Checkout

## Purpose

Turning a cart into a paid order through the payment gateway, including what
happens when payment does not succeed.

## Requirements

### Requirement: The amount comes from the database

Checkout SHALL take the amount from the cart total computed by the database and SHALL NOT accept an amount from the request; a gateway echo that differs from that total SHALL count as a failure. Verified by `tests/checkout.test.ts` and `tests/payments.test.ts`.

#### Scenario: A forged amount is ignored
- **WHEN** a checkout form carries an amount that differs from the cart total
- **THEN** the database total is used and the form value has no effect

#### Scenario: A mismatched echo fails
- **WHEN** the gateway approves an amount different from the order total
- **THEN** the payment counts as declined and the order is not marked paid

### Requirement: Approval requires an approval code and a matching amount

A payment SHALL count as approved only when the gateway's response code marks approval and the echoed amount matches; transport failures, timeouts and server errors SHALL be treated as declines, never as approvals. Verified by `tests/payments.test.ts`.

#### Scenario: An approved response with the right amount passes
- **WHEN** the gateway answers with approval and the cart total
- **THEN** the payment is recorded as authorized

#### Scenario: A transport failure is a decline
- **WHEN** the gateway request fails or the connection drops
- **THEN** the order is not marked paid and the visitor sees a decline

### Requirement: An approved payment advances the order through authorization and capture

An approved authorization SHALL store the provider's transaction reference and authorization code and set the order to authorized; a successful capture SHALL then set it to paid, writing exactly that sequence of statuses. Verified by `tests/checkout.test.ts`.

#### Scenario: A successful checkout ends paid
- **WHEN** authorization and capture both approve the database total
- **THEN** the order is written with status 3 and then 5, and the visitor is redirected to the order

### Requirement: A decline is audited and never marks the order paid

A declined authorization SHALL leave the order at its placed status, SHALL write an audit row naming the decline, and SHALL answer 402. Verified by `tests/checkout.test.ts` and `tests/payments.test.ts`.

#### Scenario: A decline keeps the order visible and unpaid
- **WHEN** the gateway declines the authorization
- **THEN** the order stays unpaid, an audit row records the decline, and the response is 402

### Requirement: A capture failure leaves the order authorized

When capture fails after a successful authorization the order SHALL remain authorized, SHALL NOT be marked paid, and SHALL receive an audit row explaining the failure; re-capturing is a manual operation. Verified by `tests/checkout.test.ts`.

#### Scenario: Capture failure stops short of paid
- **WHEN** authorization approves and capture does not
- **THEN** the order stays at authorized with an audit row and the response is 502

### Requirement: A duplicate submission cannot create a second order

Order creation SHALL write the order, its lines and empty the cart in one database call, so a repeated submission finds an empty cart and is redirected to the cart instead of creating another order. Verified by `tests/checkout.test.ts`.

#### Scenario: The second submit finds nothing to buy
- **WHEN** the same checkout is submitted twice
- **THEN** the second attempt reports an empty cart and creates no order

### Requirement: A replayed checkout form is refused

The checkout form SHALL carry a single-use nonce in a signed, short-lived cookie that is cleared on submission, so a stale or replayed form is rejected before any order is created. Verified by `tests/checkout.test.ts`.

#### Scenario: A stale nonce is refused
- **WHEN** a checkout POST arrives with a nonce that was already used or has expired
- **THEN** the response is 400 and no order is written

### Requirement: An unconfigured gateway refuses checkout without writing

With no gateway credentials configured, checkout SHALL answer 503 and SHALL NOT write orders, order lines, cart rows or audit rows. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: Refusal leaves the tables untouched
- **WHEN** an authenticated visitor with a populated cart submits checkout while the gateway is unconfigured
- **THEN** the response is 503 and the row counts of orders, order lines, cart and audit are unchanged

### Requirement: Administrators cannot mark an order paid without an authorization

An order status change to paid or later SHALL be refused when the order has no authorization code and its status is below authorized. Verified by `tests/routes.test.ts`.

#### Scenario: Skipping authorization is refused
- **WHEN** an administrator submits a status of paid for an order that was never authorized
- **THEN** the change is rejected and the status is unchanged
