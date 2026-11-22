---
title: "Documentation"
weight: 1
bookFlatSection: true
---

# Documentation

This site keeps the parts of the project that cannot be read out of the code: why
the system is built the way it is, what was measured, and how to work on it.

## Guides by role

- **[Users]({{< relref "users" >}})** — how to use the shop as a customer.
- **[Developers]({{< relref "developers" >}})** — the chapter codebase and the
  HTML-native Worker in `src/web`.
- **[Admins]({{< relref "admins" >}})** — deployment and operations.
- **[Chapters]({{< relref "chapters" >}})** — the book's chapter-by-chapter build.

## Working on the Worker

- **[OpenSpec workflow]({{< relref "speckit-workflow" >}})** — how behaviour is
  specified, validated and archived.
- **[Where things live]({{< relref "where-things-live" >}})** — which document owns
  which answer, and the rule against duplicating discoverable facts.

Behaviour itself is specified in `openspec/specs/` in the repository; commands live
in `src/web/README.md`, the `makefile`s and `package.json`.
