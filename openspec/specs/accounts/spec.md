# Accounts

## Purpose

Customer identity: signing in, the policy for new passwords, and the profile and
address data a customer may change.

## Requirements

### Requirement: Stored password digests verify without a migration

Sign-in SHALL verify the legacy 40-hex SHA-1 digests already stored in `customer.password`, and SHA-256 digests too, so the existing account can sign in; an unknown email and a wrong password SHALL both answer 401 without revealing which failed. Verified by `tests/password.test.ts` and the sign-in checks in `scripts/verify-interactive.mjs`.

#### Scenario: The legacy password signs in
- **WHEN** the existing customer submits the correct password for the stored SHA-1 digest
- **THEN** the response is 303 to `/account` with a signed customer session

#### Scenario: A wrong password is rejected
- **WHEN** sign-in is attempted with a wrong password
- **THEN** the response is 401 and no session is issued

#### Scenario: An unknown email looks like a wrong password
- **WHEN** sign-in is attempted for an address that does not exist
- **THEN** the response is 401 with the same message as a wrong password

### Requirement: New password storage follows an explicit policy

The application SHALL store new passwords only under `PASSWORD_STORAGE_POLICY`: unset refuses registration and password change with an explanation, `sha1-legacy` writes a digest that fits the existing column, and `pbkdf2` becomes available only after migration 002 widens that column. Verified by `tests/password.test.ts`.

#### Scenario: Registration is refused by default
- **WHEN** a visitor submits registration while no password policy is configured
- **THEN** the request is refused with an explanation and no customer row is written

#### Scenario: The legacy policy fits the column
- **WHEN** the policy is `sha1-legacy`
- **THEN** the stored digest is 40 characters, which the existing column accepts

### Requirement: Account changes are scoped to the signed-in customer

Profile, address and password changes SHALL require a valid session and CSRF, SHALL take the customer id from the session rather than the form, and SHALL answer 303 to `/login` when unauthenticated. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: Signed-out access redirects
- **WHEN** a visitor without a session requests `/account`
- **THEN** the response is 303 to `/login`

#### Scenario: A form cannot choose another customer
- **WHEN** an authenticated visitor submits an account form that carries a customer id field
- **THEN** only the session's customer is updated

### Requirement: Card data is never stored or displayed

The application SHALL NOT write card details to the database or render stored card data; card fields travel from the browser to the gateway, and only the transaction reference and authorization code are persisted. Verified by `tests/checkout.test.ts`.

#### Scenario: Order history shows no card data
- **WHEN** an authenticated customer views an order
- **THEN** the page shows the order and its lines, and no card number

#### Scenario: Only provider identifiers are written
- **WHEN** checkout completes against the stub gateway
- **THEN** the order receives the reference and authorization code and nothing else about the card
