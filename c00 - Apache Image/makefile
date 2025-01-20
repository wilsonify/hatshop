.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c00-apache-image:latest .

run:
	docker run --rm --name hatshop -p 8080:80 ghcr.io/wilsonify/c00-apache-image:latest

