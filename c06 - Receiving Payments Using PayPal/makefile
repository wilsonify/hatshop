all: build-docker run-docker
local: clean-local deploy-local

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t ghcr.io/wilsonify/c06-receiving-payments-using-paypal:latest .

run-docker:
		docker run --rm --name hatshop --env-file .env \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c06-receiving-payments-using-paypal:latest


clean-local:
	rm -rf /var/www/html/
	mkdir -p /var/www/html

deploy-local:
	cp -r ./Code/* /var/www/html/
	cd /var/www/html/ && composer require smarty/smarty
	chmod -R 775 /var/www/html


