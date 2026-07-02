APP_PORT ?= 8000
VITE_PORT ?= 5173
DB_PORT_HOST ?= 3306

.PHONY: up down build logs migrate shell frontend-shell

up:
	APP_PORT=$(APP_PORT) VITE_PORT=$(VITE_PORT) DB_PORT_HOST=$(DB_PORT_HOST) docker compose up --build

down:
	docker compose down

build:
	APP_PORT=$(APP_PORT) VITE_PORT=$(VITE_PORT) DB_PORT_HOST=$(DB_PORT_HOST) docker compose build

logs:
	docker compose logs -f

migrate:
	docker compose exec app php artisan migrate --force

shell:
	docker compose exec app sh

frontend-shell:
	docker compose exec frontend sh