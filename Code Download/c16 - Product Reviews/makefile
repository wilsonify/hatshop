all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c16--product-reviews .

run:
	docker run --rm --name c16--product-reviews -p 32775:80 -p 32776:443 --env-file ./.env c16--product-reviews