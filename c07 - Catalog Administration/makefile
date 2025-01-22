.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c07-catalog-administration:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 8080:80 ghcr.io/wilsonify/c07-catalog-administration:latest

test: build
	docker run --rm --name hatshop-test --env-file .env \
ghcr.io/wilsonify/c07-catalog-administration:latest \
./vendor/phpunit/phpunit/phpunit tests

bootstrap-database:
	docker run --rm --name hatshop-db-client --env-file .env \
-v ./Database/:/Database/ \
-w /Database \
postgres:17 \
/Database/execute_sql_scripts.sh