# AWS Provider for fetching RDS endpoint
provider "aws" {
  region  = var.aws_region
  profile = var.aws_profile
}

# PostgreSQL Provider - connects to the RDS instance
provider "postgresql" {
  host             = var.db_host
  port             = var.db_port
  database         = var.db_name
  username         = var.db_username
  password         = var.db_password
  sslmode          = var.db_sslmode
  connect_timeout  = 15
  superuser        = false
  expected_version = "15.4"
}
