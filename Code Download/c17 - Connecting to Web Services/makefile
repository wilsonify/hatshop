all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c17-web-services .

run:
	docker run --rm --name c17-web-services -p 32775:80 -p 32776:443 --env-file ./.env c17-web-services