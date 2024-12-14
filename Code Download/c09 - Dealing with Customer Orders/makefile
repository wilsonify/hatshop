all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c09-customer-orders .

run:
	docker run --rm --name c09-customer-orders -p 32775:80 -p 32776:443 --env-file ./.env c09-customer-orders