.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c20-oauth2-proxy:latest .

run:
	docker run --rm --name oauth2proxy --env-file .env -p 8080:80 ghcr.io/wilsonify/c20-oauth2-proxy:latest
