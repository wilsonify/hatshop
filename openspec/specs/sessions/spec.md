# Sessions

## Purpose

How a signed-in visitor stays signed in, what invalidates that, and what logout
can and cannot do.

## Requirements

### Requirement: Session cookies carry hardening attributes

Customer and admin sessions SHALL be HMAC-SHA256 signed cookies with `HttpOnly`, `SameSite=Lax`, `Path=/`, an eight-hour `Max-Age`, and `Secure` whenever the request is HTTPS. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: HTTPS responses harden the cookie
- **WHEN** sign-in succeeds over HTTPS
- **THEN** the cookie is `HttpOnly`, `Secure`, `SameSite=Lax` and expires in eight hours

#### Scenario: Local HTTP development omits Secure
- **WHEN** the same cookie is issued over plain HTTP in development
- **THEN** it is still `HttpOnly` and `SameSite=Lax` but not `Secure`

### Requirement: Forged, expired and stale sessions are rejected

A token SHALL be rejected when its signature, key, payload or expiry does not verify, when it has expired, or when its fingerprint no longer matches the customer's stored digest; a rejected token SHALL NOT authorize access. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: A tampered token is rejected
- **WHEN** `/account` is requested with a token whose payload or signature was altered
- **THEN** the response is 303 to `/login`

#### Scenario: An expired token is cleared
- **WHEN** the presented token is past its eight-hour expiry
- **THEN** access is refused and the response clears the cookie

### Requirement: Continuous use renews the session

A presented token older than 30 minutes SHALL be exchanged for a new token with a new issued-at and a fresh eight-hour expiry; a recently issued token SHALL NOT be renewed, and an idle token SHALL expire at its own expiry. Verified by `tests/session.test.ts`.

#### Scenario: An aged token is renewed
- **WHEN** a token issued more than 30 minutes ago is presented
- **THEN** the response sets a new token whose expiry is eight hours from now

#### Scenario: A fresh token is left alone
- **WHEN** a token issued seconds ago is presented
- **THEN** no replacement cookie is set

### Requirement: A password change invalidates issued sessions

The token's fingerprint claim SHALL derive from the stored password digest, so changing the password invalidates every previously issued session without server-side storage. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: The old cookie stops working after a password change
- **WHEN** a token issued before a password change is presented afterwards
- **THEN** access is refused because the fingerprint no longer matches

### Requirement: Logout cannot revoke a copied cookie

Logout SHALL clear the cookie and SHALL NOT claim to revoke it: without the reviewed session-epoch column a copied token stays valid until expiry, and that limitation is recorded rather than hidden. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: Logout clears the browser cookie
- **WHEN** an authenticated visitor signs out
- **THEN** the response clears the session cookie and the cleared value no longer authorizes

#### Scenario: A copied cookie still authorizes
- **WHEN** a pre-logout token is replayed after logout
- **THEN** it still returns the account page, which is the documented residual risk

### Requirement: Admin sessions are distinct from customer sessions

An admin session SHALL carry the admin role and no customer id, SHALL be accepted only by admin routes, and SHALL NOT be produced by customer sign-in. Verified by `tests/session.test.ts` and `scripts/verify-interactive.mjs`.

#### Scenario: An admin token carries no customer
- **WHEN** an administrator signs in at `/admin/login`
- **THEN** the token's role is admin and its customer id is zero
