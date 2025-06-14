terraform {
  backend "s3" {
    bucket         = "${var.aws_account_number}-terraform-state"
    key            = "hatshop-rds/terraform.tfstate"
    region         = "${var.aws_region}"
    dynamodb_table = "${var.aws_account_number}-lock-table"
    profile        = "${var.aws_profile}"
  }
  required_providers {
    aws = {
      version = "~> 5.100.0"
    }
  }
}
