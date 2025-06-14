
.PHONY: encrypt decrypt promote create read update delete clean

encrypt:
	sops encrypt --age age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek --input-type dotenv terraform.tfvars.env > terraform.tfvars.enc

decrypt:
	SOPS_AGE_KEY_FILE=/home/thom/.config/sops/age/keys.txt sops decrypt --input-type dotenv --output-type dotenv terraform.tfvars.enc > terraform.tfvars.tmp

promote:
	mv terraform.tfvars.tmp terraform.tfvars

create:
	terraform init -backend-config=terraform.tfvars && \
	terraform plan && \
	terraform apply -auto-approve

read:
	terraform show

update:
	terraform apply -auto-approve

delete:
	terraform destroy -auto-approve