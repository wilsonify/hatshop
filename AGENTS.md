# Working in this repository

## Specs first

Behaviour is specified with OpenSpec under `openspec/`. Before changing what the
system does, read the relevant `openspec/specs/<capability>/spec.md`.

- A behaviour change goes through a change: `openspec new change <name>`, fill in
  the proposal and the delta specs, then `openspec validate --all --strict`.
- Implement only what the change describes; archive it with `openspec archive`
  once it is applied and reviewed.
- Pure refactors, tooling and documentation work set `skip_specs: true` instead of
  inventing requirements.

## Do not restate what can be discovered

Behaviour belongs in specs. Prose — READMEs, `src/web/docs`, the Hugo site — keeps
only decisions and their rationale, measurements that cannot be recomputed, and
workflow instructions. Do not add route lists, table or function inventories, file
trees, or commands that a script, `package.json` or a `makefile` already exposes.
Generated documents are rebuilt, never hand-edited.

## Approval gates

Database migrations, `wrangler deploy`, secret changes and infrastructure applies
need the owner's explicit approval. `src/web/migrations/` is reviewed but unapplied.

## Secrets

Never commit, print or paste secret values. Local secrets live in git-ignored files
or in the platform's secret store.
