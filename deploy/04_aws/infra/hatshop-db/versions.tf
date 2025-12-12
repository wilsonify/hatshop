terraform {
  required_version = ">= 1.0.0"

  required_providers {
    postgresql = {
      source  = "cyrilgdn/postgresql"
      version = "~> 1.25.0"
    }
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.100.0"
    }
  }
}
