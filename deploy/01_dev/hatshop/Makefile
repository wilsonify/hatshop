.PHONY: up down logs status promote-stage promote-prod

# Docker Compose shortcuts
up:
	@echo "Starting dev environment..."
	docker compose up -d

down:
	@echo "Stopping dev environment..."
	docker compose down

logs:
	@echo "Showing logs..."
	docker compose logs -f

status:
	@echo "Container status:"
	docker compose ps

# Promote .env to stage environment
promote-stage:
	@echo "Promoting .env to stage environment..."
	@if [ -f .env ]; then \
		cp .env ../../02_stage/.env; \
		echo "SUCCESS: .env copied to deploy/02_stage/.env"; \
	else \
		echo "ERROR: .env file not found in dev environment"; \
		exit 1; \
	fi

# Promote .env to prod environment
promote-prod:
	@echo "Promoting .env to prod environment..."
	@if [ -f .env ]; then \
		cp .env ../../03_prod/.env; \
		echo "SUCCESS: .env copied to deploy/03_prod/.env"; \
	else \
		echo "ERROR: .env file not found in dev environment"; \
		exit 1; \
	fi

# Promote to all environments
promote-all: promote-stage promote-prod
	@echo "Promoted .env to all environments"
