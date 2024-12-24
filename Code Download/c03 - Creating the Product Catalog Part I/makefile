all: build-docker run-docker
local: clean-local deploy-local

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t c03-catalog-part-i .

run-docker:
	docker run --rm --name c03-catalog-part-i -p 32776:443 --env-file ./.env c03-catalog-part-i

clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

deploy-local:
	cp -r ./Code/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	chmod -R 775 /var/www/hatshop