.phony: all clean build run

all: build run


clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c15-credit-card-transactions-authorize-net:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 8080:80 ghcr.io/wilsonify/c15-credit-card-transactions-authorize-net:latest

test: build
	docker run --rm --name hatshop-test --env-file .env ghcr.io/wilsonify/c15-credit-card-transactions-authorize-net:latest ./vendor/phpunit/phpunit/phpunit tests/unit

bootstrap-database:
	docker run --rm --name hatshop-db-client --env-file .env \
-v ./Database/:/Database/ \
-w /Database \
postgres:17 \
/Database/execute_sql_scripts.sh