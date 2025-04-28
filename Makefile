.PHONY: start stop install db-create-test db-drop-test test

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

rebuild: down build up

stop:
	docker compose down

install:
	docker compose run --rm app composer install

db-create-test:
	docker compose run --rm app php bin/console doctrine:schema:create --env=test

db-drop-test:
	docker compose run --rm app php bin/console doctrine:schema:drop --force --env=test

test: db-drop-test db-create-test
	docker compose run --rm app php bin/phpunit
