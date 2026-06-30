# ======================================================================
# Atalhos para operar o projeto via Docker.
# Uso: make <alvo>   (no Windows, use "make" via Git Bash/WSL, ou rode
# os comandos equivalentes listados no README.md).
# ======================================================================

# Executa o artisan dentro do container php
ARTISAN = docker compose exec php php artisan

.PHONY: help up down build logs install jwt migrate seed fresh setup shell-php shell-node

help: ## Lista os comandos disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

up: ## Sobe todos os containers (nginx, php, mysql, node)
	docker compose up -d

down: ## Derruba todos os containers
	docker compose down

build: ## (Re)constrói as imagens
	docker compose build

logs: ## Acompanha os logs de todos os serviços
	docker compose logs -f

jwt: ## Gera a chave JWT_SECRET no .env do backend
	$(ARTISAN) jwt:secret --force

migrate: ## Roda as migrations
	$(ARTISAN) migrate --force

seed: ## Roda os seeders (admin, atendentes, disponibilidades, agendamentos)
	$(ARTISAN) db:seed --force

fresh: ## Recria o banco do zero e roda seeders
	$(ARTISAN) migrate:fresh --seed --force

setup: up ## Sobe tudo e prepara o banco (migrate + seed) — primeira execução
	@echo "Aguardando o backend instalar dependências..."
	@sleep 25
	$(ARTISAN) migrate --force
	$(ARTISAN) db:seed --force
	@echo "Pronto! Front: http://localhost:5173  |  API: http://localhost:8000/api"

shell-php: ## Abre um shell no container php
	docker compose exec php sh

shell-node: ## Abre um shell no container node
	docker compose exec node sh
