.phone: all clean build run

all: build run


clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c14-implementing-the-order-pipeline-part-ii:latest .

run:
	docker run --rm --name hatshop --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c14-implementing-the-order-pipeline-part-ii:latest

test: build
	docker run --rm --name hatshop-test --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c14-implementing-the-order-pipeline-part-ii:latest \
./vendor/phpunit/phpunit/phpunit tests