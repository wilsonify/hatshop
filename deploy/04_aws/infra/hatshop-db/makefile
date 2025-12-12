.PHONY: init plan apply destroy fmt validate clean

# Terraform commands
init:
	terraform init -backend-config=backend-config.tfbackend

plan:
	terraform plan -var-file=terraform.tfvars

apply:
	terraform apply -var-file=terraform.tfvars -auto-approve

destroy:
	terraform destroy -var-file=terraform.tfvars -auto-approve

fmt:
	terraform fmt -recursive

validate:
	terraform validate

# Clean up
clean:
	rm -rf .terraform
	rm -f .terraform.lock.hcl
	rm -f terraform.tfstate*
	rm -f *.tfplan

# Show state
show:
	terraform show

# Refresh state
refresh:
	terraform refresh -var-file=terraform.tfvars

# Import existing resources (if needed)
import-department-table:
	terraform import 'postgresql_script.table_department' department

# Output values
output:
	terraform output
