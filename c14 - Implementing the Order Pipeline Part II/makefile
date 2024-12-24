all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c14-order-pipeline-part-ii .

run:
	docker run --rm --name c14-order-pipeline-part-ii -p 32775:80 -p 32776:443 --env-file ./.env c14-order-pipeline-part-ii

clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

local:
	cp -r ./Code/* /var/www/hatshop/
	cp -r ./Images/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	chmod -R 777 /var/www/hatshop