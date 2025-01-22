.phony: all clean build run

all: build run

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c22-zero-trust:latest .

run:
	docker run -d ghcr.io/wilsonify/c22-zero-trust:latest tunnel --no-autoupdate run --token $(TOKEN)
