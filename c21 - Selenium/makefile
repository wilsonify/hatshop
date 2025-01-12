.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c21-selenium:latest .

run:
	docker run --rm --name oauth2proxy -p 4444:4444 ghcr.io/wilsonify/c20-oauth2-proxy:latest
