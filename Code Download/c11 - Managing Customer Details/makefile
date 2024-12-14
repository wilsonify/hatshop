all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c11-managing-customer-details .

run:
	docker run --rm --name c11-managing-customer-details -p 32775:80 -p 32776:443 --env-file ./.env c11-managing-customer-details