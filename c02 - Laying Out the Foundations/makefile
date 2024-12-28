.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c02-laying-out-the-foundations:latest .

run:
	docker run --rm --name hatshop \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c02-laying-out-the-foundations:latest

test:
	cd Code &&\
	composer install phpunit/phpunit &&\
	php -r 'echo "PHP version " . phpversion() . ". \n";' &&\
	php ./vendor/phpunit/phpunit/phpunit \
	cd ..

redeploy:
	docker compose down && \
	git pull && \
	docker compose pull && \
	docker compose up -d
