all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c08-shopping-cart .

run:
	docker run --rm --name c08-shopping-cart -p 32775:80 -p 32776:443 --env-file ./.env c08-shopping-cart