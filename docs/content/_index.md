---
title: "HatShop"
type: docs
---

# HatShop

An e-commerce storefront that began as the sample application from *Beginning
PHP and PostgreSQL E-Commerce* and now runs two ways from one repository: the PHP
application chapter by chapter, and a server-rendered Cloudflare Worker
(`src/web`) against the same PostgreSQL schema.

The Worker is deployed at
[hatshop.tom-andrew-wilson.workers.dev](https://hatshop.tom-andrew-wilson.workers.dev).

## Guides

- **[For users]({{< relref "/docs/users" >}})** — browsing, cart and checkout.
- **[For developers]({{< relref "/docs/developers" >}})** — the chapter codebase and the Worker.
- **[For admins]({{< relref "/docs/admins" >}})** — deployment and operations.
- **[Chapter guide]({{< relref "/docs/chapters" >}})** — what each chapter adds.

## Where to look

- **Behaviour** — `openspec/specs/`. Each capability's `spec.md` is the
  description of what the system does. Nothing else restates it.
- **Why it is built this way** — `src/web/docs/architecture.md`: the decisions,
  the measurements behind them, and what was deliberately left out.
- **Evidence** — `src/web/docs/live-verification.md` and
  `src/web/docs/hyperdrive-verification.md`: what was checked against a running
  Worker and the live database.
- **Commands** — `src/web/README.md`, the `makefile`s and `package.json`.
- **Workflow** — [OpenSpec workflow](/hatshop/docs/speckit-workflow/) and
  [where things live](/hatshop/docs/where-things-live/).

## Chapters

The book's chapter directories remain under `src/`, each a self-contained
exercise. They are not the deployment path; `src/web` is.
