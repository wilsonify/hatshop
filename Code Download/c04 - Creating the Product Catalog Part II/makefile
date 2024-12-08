all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c03-catalog-part-i .

run:
	docker run --rm --name c03-catalog-part-i -p 32775:80 -p 32776:443 --env-file ./.env c03-catalog-part-i