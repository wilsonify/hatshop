.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c00-nginx-image:latest .

run:
	docker run --rm --name nginx --env-file .env -p 8080:80 ghcr.io/wilsonify/c00-nginx-image:latest

install:
	helm install my-nginx-ingress oci://ghcr.io/nginxinc/charts/nginx-ingress --version 2.0.0



