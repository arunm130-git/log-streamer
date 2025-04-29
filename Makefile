.PHONY: start stop install db-create-test db-drop-test test

LOG_PATH=logs/logs.log

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

test:
	docker compose run --rm app php bin/console doctrine:database:create --env=test --if-not-exists
	docker compose run --rm app php bin/console doctrine:schema:update --force --env=test
	docker compose run --rm app php bin/phpunit

shell:
	docker compose exec app bash

db:
	docker compose exec db mysql -uroot -proot

log-import:
	docker compose exec app php bin/console log:import $(LOG_PATH)
