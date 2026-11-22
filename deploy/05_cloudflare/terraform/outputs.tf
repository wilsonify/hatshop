output "hyperdrive_id" {
  description = "Value for the HYPERDRIVE binding `id` in src/web/wrangler.jsonc."
  value       = cloudflare_hyperdrive_config.hatshop.id
}

output "hyperdrive_name" {
  description = "Configuration name, used by `wrangler dev --remote`."
  value       = cloudflare_hyperdrive_config.hatshop.name
}
