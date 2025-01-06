.phone: all build run clean

all: build test run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c03-creating-the-product-catalog-part-i:latest .

run:
	docker run --rm --name hatshop --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c03-creating-the-product-catalog-part-i:latest

test: build
	docker run --rm --name hatshop-test --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
ghcr.io/wilsonify/c03-creating-the-product-catalog-part-i:latest \
./vendor/phpunit/phpunit/phpunit tests

