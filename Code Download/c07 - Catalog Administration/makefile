all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c07-catalog-administration .

run:
	docker run --rm --name c07-catalog-administration -p 32775:80 -p 32776:443 --env-file ./.env c07-catalog-administration