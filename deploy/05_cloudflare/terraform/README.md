# Cloudflare infrastructure for hatshop moved

The Hyperdrive configuration for this Worker is managed by Terraform in the
FreshInstall infrastructure repository:

```
wilsonify/FreshInstall/cloudflare/hatshop
```

That module owns `cloudflare_hyperdrive_config.hatshop` (pooling and TLS in front
of the existing Neon database, query caching disabled) and outputs the id used by
the `HYPERDRIVE` binding.

Worker code, static assets, bindings and secrets stay with Wrangler, configured by
`src/web/wrangler.jsonc` and deployed from `src/web` with `npx wrangler deploy`.

This directory previously held an incomplete second copy of the Terraform module
(no `variables.tf`, provider v5, never applied). It was removed so there is a
single source of truth; see the module README for the full workflow.
