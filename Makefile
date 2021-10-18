## ----------------------------
##
## BileMo

##
## -------
## Project
##
##

DOCKER_COMPOSE  = docker-compose

EXEC_PHP        = $(DOCKER_COMPOSE) exec -T php /entrypoint

SYMFONY         = $(EXEC_PHP) bin/console
COMPOSER        = $(EXEC_PHP) composer

build:
	$(DOCKER_COMPOSE) pull --parallel --quiet --ignore-pull-failures 2> /dev/null
	$(DOCKER_COMPOSE) build --pull

kill:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

install: ## Install and start the project
install: .env.local build start db

reset: ## Stop and start a fresh install of the project
reset: kill install

start: ## Start the project
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

stop: ## Stop the project
	$(DOCKER_COMPOSE) stop

clean: ## Stop the project and remove generated files
clean: kill
	rm -rf vendor

no-docker:
	$(eval DOCKER_COMPOSE := \#)
	$(eval EXEC_PHP := )

.PHONY: build kill install reset start stop clean no-docker

##
## -----
## Utils
## 
##

db: ## Setup local database and load fake data
db: .env.local vendor
	-$(SYMFONY) doctrine:database:drop --if-exists --force
	-$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) d:m:m --no-interaction --allow-no-migration
	$(SYMFONY) d:f:l --no-interaction --purge-with-truncate

migration: ## Create a new doctrine migration
migration: vendor
	$(SYMFONY) doctrine:migrations:diff

db-validate-schema: ## Validate the database schema
db-validate-schema: .env.local vendor
	$(SYMFONY) doctrine:schema:validate

composer.lock: composer.json
	$(COMPOSER) update

vendor: composer.lock
	$(COMPOSER) install

.env.local: .env
	@if [ -f .env.local ]; \
	then\
		echo '\033[1;41m/!\The .env file has changed. Please check your .env.local file (this message will not be displayed again)';\
		touch .env.local;\
		exit 1;\
	else\
		echo cp .env .env.local;\
		cp .env .env.local;\
	fi

.PHONY: db migration db-validate-schema

## 
## -----
## Tests
## 
## 

test: ## Run all tests in the tests/ folder
test:
	$(EXEC_PHP) bin/phpunit

.PHONY: test


.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## ----------------------------

.PHONY: help