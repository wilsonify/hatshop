local: clean-local deploy-local
docker: build-docker run-docker

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t docker ghcr.io/wilsonify/c02-laying-out-the-foundations:latest .

run-docker:
	docker run --rm --name c02-foundations \
-v ./etc/ssl/certs/rr-origin.pem:/etc/ssl/certs/rr-origin.pem \
-v ./etc/ssl/private/rr-private-key.pem:/etc/ssl/private/rr-private-key.pem \
-p 443:443 ghcr.io/wilsonify/c02-laying-out-the-foundations:latest

clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

deploy-local:
	cp -r ./Code/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	mkdir -p /var/www/hatshop/presentation/templates_c
	chmod -R 775 /var/www/hatshop





