all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c05-searching-catalog .

run:
	docker run --rm --name c05-searching-catalog -p 32775:80 -p 32776:443 --env-file ./.env c05-searching-catalog