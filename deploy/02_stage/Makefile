.PHONY: cluster-create cluster-delete deploy undeploy logs status secret port-forward clean all sops-operator sops-age-secret load-image

CLUSTER_NAME := hatshop-stage
NAMESPACE := hatshop-stage
CONTEXT := kind-$(CLUSTER_NAME)
AGE_PUBLIC_KEY := age1mg4zlx7p736nnrp7glt7gyd96s33kmy8wlck903m0srkkndeaawqrqfzek
HATSHOP_IMAGE := ghcr.io/wilsonify/hatshop:latest

# Default target
all: cluster-create load-image sops-operator sops-age-secret secret deploy status

# Create KIND cluster
cluster-create:
	@echo "Creating KIND cluster: $(CLUSTER_NAME)"
	@kind get clusters | grep -q $(CLUSTER_NAME) || kind create cluster --config kind-config.yaml
	@kubectl config use-context $(CONTEXT)
	@echo "Cluster $(CLUSTER_NAME) is ready"

# Load Docker image into KIND cluster
load-image:
	@echo "Loading $(HATSHOP_IMAGE) into KIND cluster..."
	@if docker image inspect $(HATSHOP_IMAGE) >/dev/null 2>&1; then \
		kind load docker-image $(HATSHOP_IMAGE) --name $(CLUSTER_NAME); \
	else \
		echo "WARNING: Image $(HATSHOP_IMAGE) not found locally. Build it first with:"; \
		echo "  cd ../../src/hatshop && docker build -t $(HATSHOP_IMAGE) ."; \
	fi

# Install SOPS Secrets Operator
sops-operator:
	@echo "Installing SOPS Secrets Operator..."
	@kubectl --context $(CONTEXT) apply -f https://raw.githubusercontent.com/isindir/sops-secrets-operator/master/config/crd/bases/isindir.github.com_sopssecrets.yaml || true
	@helm repo add sops-secrets-operator https://isindir.github.io/sops-secrets-operator/ 2>/dev/null || true
	@helm repo update
	@helm upgrade --install sops-secrets-operator sops-secrets-operator/sops-secrets-operator \
		--namespace sops --create-namespace \
		--set secretsAsFiles[0].name=sops-age \
		--set secretsAsFiles[0].mountPath=/etc/sops-age \
		--set secretsAsFiles[0].secretName=sops-age \
		--set envVars[0].name=SOPS_AGE_KEY_FILE \
		--set envVars[0].value=/etc/sops-age/keys.txt \
		--wait --timeout=120s || echo "Helm not available, install operator manually"

# Create age key secret for SOPS operator (requires AGE_SECRET_KEY env var)
sops-age-secret:
	@echo "Creating SOPS age key secret..."
	@if [ -n "$$AGE_SECRET_KEY" ]; then \
		kubectl --context $(CONTEXT) create namespace sops --dry-run=client -o yaml | kubectl apply -f -; \
		kubectl --context $(CONTEXT) -n sops delete secret sops-age --ignore-not-found; \
		echo "$$AGE_SECRET_KEY" | kubectl --context $(CONTEXT) -n sops create secret generic sops-age \
			--from-file=keys.txt=/dev/stdin; \
		echo "SOPS age secret created"; \
	else \
		echo "WARNING: AGE_SECRET_KEY not set. Set it with: export AGE_SECRET_KEY=\$$(cat ~/.sops/age/keys.txt)"; \
	fi

# Delete KIND cluster
cluster-delete:
	@echo "Deleting KIND cluster: $(CLUSTER_NAME)"
	@kind delete cluster --name $(CLUSTER_NAME) 2>/dev/null || true

# Create secret from .env file (run this before deploy)
secret:
	@echo "Creating cloudflared secret..."
	@if [ -f .env ]; then \
		kubectl --context $(CONTEXT) create namespace $(NAMESPACE) --dry-run=client -o yaml | kubectl apply -f -; \
		kubectl --context $(CONTEXT) -n $(NAMESPACE) delete secret cloudflared-secret --ignore-not-found; \
		kubectl --context $(CONTEXT) -n $(NAMESPACE) create secret generic cloudflared-secret \
			--from-env-file=.env; \
	else \
		echo "ERROR: .env file not found. Run 'make promote' from deploy/01_dev/hatshop first."; \
		exit 1; \
	fi

# Deploy using kustomize
deploy:
	@echo "Deploying to $(NAMESPACE)..."
	@kubectl --context $(CONTEXT) apply -k base/
	@echo "Waiting for deployments to be ready..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/php --timeout=120s
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/nginx --timeout=60s
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout status deployment/cloudflared --timeout=60s

# Remove deployment
undeploy:
	@echo "Removing deployment from $(NAMESPACE)..."
	@kubectl --context $(CONTEXT) delete -k base/ --ignore-not-found 2>/dev/null || true

# View logs
logs:
	@echo "=== PHP Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=php --tail=20
	@echo "\n=== Nginx Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=nginx --tail=20
	@echo "\n=== Cloudflared Logs ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -l app.kubernetes.io/component=cloudflared --tail=20

logs-php:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=php

logs-nginx:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=nginx

logs-cloudflared:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) logs -f -l app.kubernetes.io/component=cloudflared

# Check status
status:
	@echo "=== Cluster Info ==="
	@kubectl --context $(CONTEXT) cluster-info
	@echo "\n=== Namespace: $(NAMESPACE) ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get all
	@echo "\n=== Pod Status ==="
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) get pods -o wide

# Port forward for local testing (bypasses cloudflared)
port-forward:
	@echo "Forwarding localhost:8080 to nginx service..."
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) port-forward svc/nginx 8080:80

# Test endpoints
test:
	@echo "Testing local endpoint..."
	@curl -s -o /dev/null -w "Local (10080): %{http_code}\n" http://localhost:10080/stage || echo "Local test failed"
	@echo "Testing health endpoint..."
	@curl -s -o /dev/null -w "Health: %{http_code}\n" http://localhost:10080/health || echo "Health test failed"

# Restart all deployments
restart:
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/php
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/nginx
	@kubectl --context $(CONTEXT) -n $(NAMESPACE) rollout restart deployment/cloudflared

# Clean up everything
clean: cluster-delete
	@echo "Cleanup complete"

# Help
help:
	@echo "HatShop Stage Deployment - KIND/Kubernetes"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo "  all              - Create cluster, load image, SOPS operator, secrets, and deploy (default)"
	@echo "  cluster-create   - Create KIND cluster"
	@echo "  cluster-delete   - Delete KIND cluster"
	@echo "  load-image       - Load Docker image into KIND cluster"
	@echo "  sops-operator    - Install SOPS Secrets Operator"
	@echo "  sops-age-secret  - Create age key secret (requires AGE_SECRET_KEY env)"
	@echo "  secret           - Create cloudflared secret from .env"
	@echo "  deploy           - Deploy application with kustomize"
	@echo "  undeploy         - Remove application"
	@echo "  logs             - View recent logs from all pods"
	@echo "  logs-php         - Follow PHP logs"
	@echo "  logs-nginx       - Follow Nginx logs"
	@echo "  logs-cloudflared - Follow Cloudflared logs"
	@echo "  status           - Show cluster and pod status"
	@echo "  port-forward     - Forward localhost:8080 to nginx"
	@echo "  test             - Test endpoints"
	@echo "  restart          - Restart all deployments"
	@echo "  clean            - Delete KIND cluster"
	@echo "  help             - Show this help"
	@echo ""
	@echo "Prerequisites:"
	@echo "  1. Build the image: cd ../../src/hatshop && docker build -t $(HATSHOP_IMAGE) ."
	@echo "  2. Set AGE_SECRET_KEY: export AGE_SECRET_KEY=\$$(cat ~/.config/sops/age/keys.txt)"
	@echo ""
	@echo "SOPS Secrets:"
	@echo "  Secrets are encrypted with age key: $(AGE_PUBLIC_KEY)"
	@echo "  To decrypt: sops decrypt base/hatshop-secrets.enc.yaml"
	@echo "  To edit:    sops base/hatshop-secrets.enc.yaml"
