data "terraform_remote_state" "vpc" {
  backend = "s3"
  config = {
    bucket         = "${var.aws_account_number}-terraform-state"
    key            = "02-vpc/terraform.tfstate"
    region         = "${var.aws_region}"
    dynamodb_table = "${var.aws_account_number}-lock-table"
    profile        = "${var.aws_profile}"
  }
}

# Subnet group for RDS (use both public and private for redundancy)
resource "aws_db_subnet_group" "default" {
  name = "rds-subnet-group"
  subnet_ids = [data.terraform_remote_state.vpc.outputs.private_1a.id]

  tags = {
    Name = "rds-subnet-group"
  }
}

# Security Group allowing inbound PostgreSQL traffic (from anywhere for test)
resource "aws_security_group" "rds_sg" {
  name        = "rds_sg"
  description = "Allow PostgreSQL inbound"
  vpc_id      = data.terraform_remote_state.vpc.outputs.vpc_id

  ingress {
    from_port = 5432
    to_port   = 5432
    protocol  = "tcp"
    cidr_blocks = ["0.0.0.0/0"]  # ⚠️ Test only! Restrict in prod
  }

  egress {
    from_port = 0
    to_port   = 0
    protocol  = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# RDS Instance
resource "aws_db_instance" "postgres" {
  identifier           = "test-postgres-db"
  engine               = "postgres"
  engine_version       = "15.4"
  instance_class       = "db.t3.micro"
  allocated_storage    = 20
  storage_type         = "gp3"
  storage_encrypted    = true
  username             = "pgadmin"
  password             = "pgadmin123"
  multi_az             = false
  db_name              = "testdb"
  port                 = 5432
  publicly_accessible  = true
  vpc_security_group_ids = [aws_security_group.rds_sg.id]
  db_subnet_group_name = aws_db_subnet_group.default.name
  skip_final_snapshot  = true
  deletion_protection  = false
  backup_retention_period = 7 # Set a reasonable backup retention period to avoid SonarQube warning.  # 7 days is adequate for testing; increase for production as needed.

  tags = {
    Name = "PostgreSQL Test DB"
  }
}

# Output the RDS endpoint
output "rds_endpoint" {
  value = aws_db_instance.postgres.endpoint
}
