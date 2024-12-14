all: clean-local local
all-docker: build-docker run-docker

clean-docker:
	docker system prune -af

build-docker:
	docker build --progress=plain -t c02-foundations .

run-docker:
	docker run --rm --name c02-foundations -p 32775:80 -p 32776:443 c02-foundations

clean-local:
	rm -rf /var/www/html/
	mkdir -p /var/www/html

local:
	cp -r ./Code/* /var/www/html/
	cp -r ./Images/* /var/www/html/
	cd /var/www/html/ && composer require smarty/smarty
	chmod -R 777 /var/www/html





