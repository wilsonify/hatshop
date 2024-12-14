all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c10-product-recommendations .

run:
	docker run --rm --name c10-product-recommendations -p 32775:80 -p 32776:443 --env-file ./.env c10-product-recommendations