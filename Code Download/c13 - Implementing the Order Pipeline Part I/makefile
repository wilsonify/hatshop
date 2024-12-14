all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c13-order-pipeline-part-i .

run:
	docker run --rm --name c13-order-pipeline-part-i -p 32775:80 -p 32776:443 --env-file ./.env c13-order-pipeline-part-i