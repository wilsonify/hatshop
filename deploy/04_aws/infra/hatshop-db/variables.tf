# AWS Configuration
variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "us-east-1"
}

variable "aws_profile" {
  description = "AWS CLI profile to use"
  type        = string
}

variable "aws_account_number" {
  description = "AWS Account Number"
  type        = string
}

# Database Connection Configuration
variable "db_host" {
  description = "PostgreSQL database host (RDS endpoint)"
  type        = string
}

variable "db_port" {
  description = "PostgreSQL database port"
  type        = number
  default     = 5432
}

variable "db_name" {
  description = "PostgreSQL database name"
  type        = string
  default     = "hatshop"
}

variable "db_username" {
  description = "PostgreSQL database username"
  type        = string
  sensitive   = true
}

variable "db_password" {
  description = "PostgreSQL database password"
  type        = string
  sensitive   = true
}

variable "db_sslmode" {
  description = "PostgreSQL SSL mode"
  type        = string
  default     = "require"
}

# Feature Flags
variable "create_types" {
  description = "Whether to create custom PostgreSQL types"
  type        = bool
  default     = true
}

variable "create_functions" {
  description = "Whether to create PostgreSQL functions"
  type        = bool
  default     = true
}

variable "create_sample_data" {
  description = "Whether to insert sample data into tables"
  type        = bool
  default     = false
}
