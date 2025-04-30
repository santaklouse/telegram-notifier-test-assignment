.PHONY: help build up down restart test logs shell lint cs-fix

help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build containers
	./sail build

up: ## Run containers
	./sail up -d
	@echo "Containers are up and running."

stop: ## Stop containers
	./sail stop

down: ## Stop containers
	./sail down
	@echo "Containers are stopped."

restart: down up ## Restart containers

logs: ## View logs
	./sail logs -f

shell: ## Open a shell in the application container
	./sail exec telegram-notifier-app /bin/bash

test: ## Run tests
	./sail bin phpunit
