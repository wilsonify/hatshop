.PHONY: cluster-create cluster-delete deploy undeploy logs status secret port-forward clean all

CLUSTER_NAME := hatshop-stage
NAMESPACE := hatshop-stage
CONTEXT := kind-$(CLUSTER_NAME)

# Default target
all: cluster-create secret deploy status

# Create KIND cluster
cluster-create:
	@echo "Creating KIND cluster: $(CLUSTER_NAME)"
	@kind get clusters | grep -q $(CLUSTER_NAME) || kind create cluster --config kind-config.yaml
	@kubectl config use-context $(CONTEXT)
	@echo "Cluster $(CLUSTER_NAME) is ready"

# Delete KIND cluster
cluster-delete:
	@echo "Deleting KIND cluster: $(CLUSTER_NAME)"
	@kind delete cluster --name $(CLUSTER_NAME)

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
	@kubectl --context $(CONTEXT) delete -k base/ --ignore-not-found

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
clean: undeploy cluster-delete
	@echo "Cleanup complete"

# Help
help:
	@echo "HatShop Stage Deployment - KIND/Kubernetes"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo "  all             - Create cluster, secret, and deploy (default)"
	@echo "  cluster-create  - Create KIND cluster"
	@echo "  cluster-delete  - Delete KIND cluster"
	@echo "  secret          - Create cloudflared secret from .env"
	@echo "  deploy          - Deploy application with kustomize"
	@echo "  undeploy        - Remove application"
	@echo "  logs            - View recent logs from all pods"
	@echo "  logs-php        - Follow PHP logs"
	@echo "  logs-nginx      - Follow Nginx logs"
	@echo "  logs-cloudflared - Follow Cloudflared logs"
	@echo "  status          - Show cluster and pod status"
	@echo "  port-forward    - Forward localhost:8080 to nginx"
	@echo "  test            - Test endpoints"
	@echo "  restart         - Restart all deployments"
	@echo "  clean           - Remove deployment and cluster"
	@echo "  help            - Show this help"
