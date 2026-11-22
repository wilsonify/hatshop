# Operations

## Purpose

How the deployment behaves as a running service: health, headers, timeouts,
database access and error containment.

## Requirements

### Requirement: The health endpoint reports database reachability

`GET /health` SHALL answer `200 {"status":"ok","database":"ok"}` when a trivial query succeeds and `503 {"status":"degraded","database":"unavailable"}` when it fails, as JSON with no caching. Verified by `scripts/verify-interactive.mjs` and the deployed check in `docs/hyperdrive-verification.md`.

#### Scenario: A reachable database is healthy
- **WHEN** the database answers `SELECT 1`
- **THEN** the response is 200 with both statuses `ok`

#### Scenario: An unreachable database is degraded
- **WHEN** the connection fails or times out
- **THEN** the response is 503 with `status: degraded` and `database: unavailable`

### Requirement: Every response carries the hardening headers

Every Worker response SHALL be sent through one header step that applies a
Content-Security-Policy without inline script or style, `nosniff`, a denied frame
policy, a referrer policy, a permissions policy and same-origin isolation, plus
HSTS on HTTPS. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: A page is served with the policy
- **WHEN** any HTML page is requested over HTTPS
- **THEN** the response carries the content-security-policy and HSTS headers

#### Scenario: A route cannot drop the headers
- **WHEN** an error page or redirect is returned
- **THEN** it carries the same hardening headers as a successful page

### Requirement: Personalized responses are not cached

HTML, JSON and redirects SHALL be sent with `cache-control: no-store`, so a shared
cache cannot serve one visitor's session, cart or order to another. Verified by
`scripts/verify-interactive.mjs`.

#### Scenario: Account pages are uncached
- **WHEN** an authenticated page is returned
- **THEN** the response carries `cache-control: no-store`

### Requirement: Database work is bounded in time

Connections, queries and statements SHALL carry timeouts, so a hung database turns
into a 503 for the visitor instead of a request that never finishes. Verified by
`scripts/verify-db.mjs`.

#### Scenario: A stalled connection fails the request
- **WHEN** the database does not answer within the configured timeout
- **THEN** the request ends with a degraded response rather than hanging

### Requirement: Hyperdrive is the only database path

The Worker SHALL connect through its Hyperdrive binding, SHALL open one client per
request and close it in a `finally`, and SHALL NOT ship database credentials in the
bundle, assets or variables. Verified by `scripts/verify-db.mjs` and the deployed `/health` check.

#### Scenario: The connection string comes from the binding
- **WHEN** any route queries the database
- **THEN** the client is built from the Hyperdrive binding and closed when the request ends

### Requirement: Failures do not leak internals

Client-facing errors SHALL be generic messages chosen by the application; error
details, SQL and stack traces SHALL go to the Worker log only. Verified by `scripts/verify-interactive.mjs`.

#### Scenario: An unexpected failure stays opaque
- **WHEN** an unhandled error occurs while rendering a page
- **THEN** the visitor sees a generic error page and the log carries the detail
