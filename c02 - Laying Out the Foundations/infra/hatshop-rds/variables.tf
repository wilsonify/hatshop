variable "aws_region" {
  default = "us-east-1"
}

variable "aws_profile" {
  type        = string
}


variable "aws_account_number" {
  description = "AWS Account Number"
  type        = string
}

variable "bucket" {
  description = "S3 bucket for backend"
  type        = string
}

variable "key" {
  description = "S3 key path for backend"
  type        = string
}

variable "region" {
  description = "AWS region"
  type        = string
}

variable "dynamodb_table" {
  description = "DynamoDB table for state locking"
  type        = string
}

