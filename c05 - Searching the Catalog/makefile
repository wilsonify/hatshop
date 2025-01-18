.phony: clean build run all

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c05-searching-the-catalog:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 9000:9000 ghcr.io/wilsonify/c05-searching-the-catalog:latest

test: build
	docker run --rm --name hatshop-test --env-file .env \
ghcr.io/wilsonify/c05-searching-the-catalog:latest \
./vendor/phpunit/phpunit/phpunit tests

bootstrap-database:
	docker run --rm --name hatshop-db-client --env-file .env \
-v ./Database/:/Database/ \
-w /Database \
postgres:17 \
/Database/execute_sql_scripts.sh