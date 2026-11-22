---
title: "OpenSpec workflow"
weight: 10
---

# OpenSpec workflow

Behaviour is specified with [OpenSpec](https://openspec.dev) before it is built.
The CLI is required at version 2.x; the 1.x line has a different layout and
different flags.

```
npx @fission-ai/openspec@2.1.1 list
```

## Why a spec at all

The Worker is a rewrite of a PHP application whose rules live in the database.
Most disagreements here are not about code but about what the system is supposed
to do — which statuses an order may pass through, what a decline leaves behind,
whether a cart survives a tampered cookie. Those answers belong in one place that
is reviewed before the code changes, not in a commit message.

## The loop

| Step | Command | What it gives you |
| --- | --- | --- |
| See what exists | `openspec list --specs` | the capabilities under `openspec/specs/` |
| Read one | `openspec show <capability>` | its requirements and scenarios |
| Start a change | `openspec new change <name>` | a change folder with proposal, deltas, design, tasks |
| Check progress | `openspec status --change <name>` | which artifacts exist and what is next |
| Check structure | `openspec validate --all --strict` | missing scenarios, long requirements, malformed deltas |
| Read a delta | `openspec show <name> --json --deltas-only` | exactly what the change adds or alters |
| Finish | `openspec archive <name>` | merges the deltas into `specs/` and files the change under `changes/archive/` |

Artifacts are created with `openspec instructions`; the CLI tells an agent what
to write next, so the folder layout does not need to be memorised.

No tool-specific skill files are committed. To install OpenSpec's skills for the
agent you use, run `openspec init --scope project --tools <id>` (for example
`claude`, `codex`, `cursor`); `openspec update` refreshes them later.

## What belongs in a spec

- One requirement per observable behaviour, written with **SHALL**.
- At least one scenario per requirement, in **WHEN** / **THEN** form, describing
  something a test or verification script can observe.
- The name of the test or script that proves it, inside the requirement text.

## What does not belong in a spec — or anywhere else in the docs

Routes, table and function inventories, file trees, and commands. All of those are
discovered by reading the code or running the tooling. Prose in this repository
keeps only three things:

1. decisions and their rationale,
2. measurements that cannot be recomputed from the code,
3. how to work on the project.

If a sentence could be replaced by "read `worker/router.ts`" or "run `npm test`",
it should be.

## Approval gates

Some changes are specified but not applied without the owner's consent: database
migrations, `wrangler deploy`, secret changes, and infrastructure applies. A
proposal names the gate it needs; tasks keep gated steps separate from safe ones.
