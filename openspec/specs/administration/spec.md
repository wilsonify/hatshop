# Administration

## Purpose

The back office: who may enter it and what catalog, customer and order management
they can perform.

## Requirements

### Requirement: Every administrative route requires an admin session

Every `/admin` page and action SHALL require an admin session; an unauthenticated request SHALL be redirected to the admin login with no page content, and a POST without CSRF SHALL answer 403. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: Signed-out access redirects
- **WHEN** a visitor without an admin session requests an admin page
- **THEN** the response is 303 to `/admin/login` with an empty body

#### Scenario: A POST without CSRF is refused
- **WHEN** an authenticated admin posts an action without the CSRF field
- **THEN** the response is 403 and nothing changes

### Requirement: Administration is disabled when no credentials are configured

Without `ADMIN_USERNAME` and `ADMIN_PASSWORD_HASH`, the admin login SHALL answer 503 and the admin area SHALL report that it is not configured, rather than accepting a default password. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: Refusal instead of a default login
- **WHEN** an administrator submits the login form while the admin secrets are unset
- **THEN** the response is 503 and no admin session is issued

### Requirement: Admin credentials are compared against a digest

The admin password SHALL be stored as a SHA-256 digest in a secret and compared in constant time; the plaintext password SHALL NOT appear in code, configuration or responses. Verified by `tests/routes.test.ts`.

#### Scenario: A wrong password is refused
- **WHEN** the admin login is submitted with a password that does not match the digest
- **THEN** no session is issued and the response contains no hint of the expected value

### Requirement: Catalog, customer and order management reuse the database functions

Admin actions SHALL call the existing catalog, customer and order functions rather than issuing ad-hoc SQL, and every order status change SHALL write an audit row. Verified by `tests/routes.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: A status change is auditable
- **WHEN** an administrator changes an order's status
- **THEN** the order is updated through the order function and an audit row records the change

### Requirement: Product images are referenced, not uploaded

Catalog administration SHALL accept an image reference only; the application SHALL NOT accept file uploads, because the runtime has no writable filesystem and object storage is a separate approval gate. Verified by `tests/routes.test.ts`.

#### Scenario: No upload path exists
- **WHEN** an administrator edits a product
- **THEN** the form offers an image reference field and no file input
