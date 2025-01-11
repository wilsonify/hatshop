.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c04-creating-the-product-catalog-part-ii:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 9000:9000 ghcr.io/wilsonify/c04-creating-the-product-catalog-part-ii:latest

test: build
	docker run --rm --name hatshop-test --env-file .env ghcr.io/wilsonify/c04-creating-the-product-catalog-part-ii:latest ./vendor/phpunit/phpunit/phpunit tests/unit

bootstrap-database:
	docker run --rm --name hatshop-db-client --env-file .env \
-v ./Database/:/Database/ \
-w /Database \
postgres:17 \
/Database/execute_sql_scripts.sh