.phone: all clean build run

all: build run


clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c17-connecting-to-web-services:latest .

run:
	docker run --rm --name hatshop --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c17-connecting-to-web-services:latest





