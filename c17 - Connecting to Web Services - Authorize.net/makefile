.phony: all clean build run

all: build run


clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c17-connecting-to-web-services-authorize-net:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 8080:80 ghcr.io/wilsonify/c17-connecting-to-web-services-authorize-net:latest

test: build
	docker run --rm --name hatshop-test --env-file .env \
ghcr.io/wilsonify/c17-connecting-to-web-services-authorize-net:latest \
./vendor/phpunit/phpunit/phpunit tests
