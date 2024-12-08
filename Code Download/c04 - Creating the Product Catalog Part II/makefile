all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c04-catalog-part-ii .

run:
	docker run --rm --name c04-catalog-part-ii -p 32775:80 -p 32776:443 --env-file ./.env c04-catalog-part-ii