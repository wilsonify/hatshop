all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t c02-foundations .

run:
	docker run --rm --name c02-foundations -p 32775:80 -p 32776:443 c02-foundations