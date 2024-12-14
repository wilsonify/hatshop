all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c15-credit-card-transactions .

run:
	docker run --rm --name c15-credit-card-transactions -p 32775:80 -p 32776:443 --env-file ./.env c15-credit-card-transactions