.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c02-laying-out-the-foundations:latest .

run:
	docker run --rm --name hatshop \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c02-laying-out-the-foundations:latest

test-unit: build
	docker run --rm --name hatshop-test --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
ghcr.io/wilsonify/c02-laying-out-the-foundations:latest \
./vendor/phpunit/phpunit/phpunit tests/unit

test-integration: build
	docker run --rm --name hatshop-test --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
ghcr.io/wilsonify/c02-laying-out-the-foundations:latest \
./vendor/phpunit/phpunit/phpunit tests/integration

test-e2e: build
	docker run --rm --name hatshop-test --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
ghcr.io/wilsonify/c02-laying-out-the-foundations:latest \
./vendor/phpunit/phpunit/phpunit tests/e2e

redeploy:
	docker compose down && \
	git pull && \
	docker compose pull && \
	docker compose up -d

bootstrap-database:
	docker run --rm --name hatshop-db-client --env-file .env \
-v ./Database/:/Database/ \
-w /Database \
postgres:17 \
/Database/execute_sql_scripts.sh
