---
title: "HTML-native Worker"
weight: 1
bookToc: true
---

# HTML-native Worker (Cloudflare)

`src/web` contains a rewrite of HatShop as an HTML-native application on Cloudflare
Workers Free, using the existing Neon PostgreSQL database. The PHP application in
`src/hatshop` is unchanged and remains the reference implementation.

## Why a rewrite was possible without porting business logic

The live database implements the catalog, cart, order and customer behaviour in 75
plpgsql functions. The Worker calls those functions and renders HTML around them. It
does not reimplement pricing, search or order creation.

## Key documents

| Topic | Location |
| --- | --- |
| Architecture, CPU budget, password findings | `src/web/docs/architecture.md` |
| Live schema and function inventory | `src/web/docs/schema-inventory.md` |
| What was ported from PHP, and what was not | `src/web/docs/functional-inventory.md` |
| Checkout and payment state machine | `src/web/docs/payment-states.md` |
| Database/Hyperdrive verification matrix | `src/web/docs/hyperdrive-verification.md` |
| Commands and operations | `src/web/README.md` |

## Status

- Catalog, search, product pages, cart, accounts, order history, checkout and
  administration are implemented and unit tested.
- Registration, password change and checkout are **disabled by default** because
  they depend on things that need approval: the password column is `VARCHAR(50)`
  (`migrations/002`), and no payment gateway credentials are configured.
- Image uploads are not implemented: Workers have no writable filesystem and object
  storage is an approval gate.

## Relationship to the PHP deployment

Nothing in the existing production deployment changes: the Kubernetes manifests,
Cloudflare Tunnel and PHP application are untouched. The Worker is a parallel,
independently deployable implementation.
