.phone: all clean build run

all: build

clean:
	docker system prune -af

build:
	docker build --progress=plain -t ghcr.io/wilsonify/c18-kind:latest .

install:
	sudo kind create cluster --image ghcr.io/wilsonify/c18-kind:latest



