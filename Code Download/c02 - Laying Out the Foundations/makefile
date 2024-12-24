all: clean-local local
all-docker: build-docker run-docker

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t c02-foundations .

run-docker:
	docker run --rm --name c02-foundations -p 32776:443 c02-foundations

clean-local:
	rm -rf /var/www/hatshop/
	mkdir -p /var/www/hatshop

local:
	cp -r ./Code/* /var/www/hatshop/
	cp -r ./Images/* /var/www/hatshop/
	cd /var/www/hatshop/ && composer require smarty/smarty
	chmod -R 777 /var/www/hatshop





