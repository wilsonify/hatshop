# ---------------------------------------------------------------------------
# Ownership model
#
#   Terraform owns:  the Hyperdrive configuration (this file) and, later, the
#                    custom domain/route once DNS changes are approved.
#   Wrangler owns:   Worker code, static assets, bindings and secrets.
#
# Nothing here creates or modifies the database. DNS and routes are deliberately
# absent: they require explicit approval.
#
# Schema note: cloudflare provider v5 exposes `origin` and `caching` as nested
# attributes of the resource (there is no `config` wrapper).
# ---------------------------------------------------------------------------

resource "cloudflare_hyperdrive_config" "hatshop" {
  account_id = var.account_id
  name       = var.hyperdrive_name

  origin = {
    host     = var.neon_host
    port     = 5432
    scheme   = "postgres"
    database = var.neon_database
    user     = var.neon_user
    password = var.neon_password
  }

  # Query caching would serve stale catalog and cart reads; the application is
  # fast enough without it and correctness matters more.
  caching = {
    disabled = true
  }
}
