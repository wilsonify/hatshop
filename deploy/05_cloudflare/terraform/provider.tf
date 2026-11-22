provider "cloudflare" {
  # Credentials come from the environment, never from this file:
  #   CLOUDFLARE_API_TOKEN  (scoped: Workers Hyperdrive:Edit)
  #   CLOUDFLARE_ACCOUNT_ID (or var.account_id)
}
