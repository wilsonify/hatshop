all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c03-catalog-part-i .

run:
	docker run --rm --name c03-catalog-part-i -p 32776:443 --env-file ./.env c03-catalog-part-i

clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

local:
	cp -r ./Code/* /var/www/hatshop/
	cp -r ./Images/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	chmod -R 777 /var/www/hatshop