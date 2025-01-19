.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c01-base-image:latest .

run:
	docker run --rm --name hatshop --env-file .env -p 9000:443 \
-v ./ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
ghcr.io/wilsonify/c01-base-image:latest





