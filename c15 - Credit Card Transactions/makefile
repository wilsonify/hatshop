all: build-docker run-docker
local: clean-local deploy-local

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t ghcr.io/wilsonify/c15-credit-card-transactions:latest .

run-docker:
		docker run --rm --name hatshop --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c15-credit-card-transactions:latest


clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

deploy-local:
	cp -r ./Code/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	chmod -R 775 /var/www/hatshop


