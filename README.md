# Sistema de Agendamentos

Aplicação web para gerenciar usuários, disponibilidade de atendentes e agendamentos.

**Stack:** PHP 8.2 + Laravel 11 · Vue 3 + Vite · MySQL 8 · Docker

---

## Pré-requisitos

Instale antes de começar:

- [Git](https://git-scm.com/downloads)
- [Docker Desktop](https://www.docker.com/products/docker-desktop)

> **Windows:** após instalar o Docker Desktop, abra o PowerShell como administrador e execute `wsl --install`. Reinicie o computador e abra o Docker Desktop novamente.

---

## Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/SEU_USUARIO/sistema-agendamentos.git
cd sistema-agendamentos

# 2. Crie o arquivo de ambiente do backend
cp backend/.env.example backend/.env

# 3. Suba os containers
docker compose up -d
```

> Na primeira execução aguarde cerca de **2 minutos** — o Composer e o npm precisam baixar as dependências. O `APP_KEY` e o `JWT_SECRET` são gerados automaticamente.

```bash
# 4. Crie as tabelas e popule o banco
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

---

## Acessos

| Serviço | URL |
|---|---|
| Frontend | http://localhost:5173 |
| API | http://localhost:8000/api |
| MySQL | localhost:3306 |

**Usuários de teste:**

| E-mail | Senha | Perfil |
|---|---|---|
| admin@admin.com | 12345678 | Admin |
| ana@agendamento.com | 12345678 | Atendente |
| bruno@agendamento.com | 12345678 | Atendente |
| carla@agendamento.com | 12345678 | Atendente |

---

## Estrutura do projeto

```
sistema-agendamentos/
│
├── backend/                          # API REST em Laravel 11
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/          # Recebe a requisição e orquestra a resposta
│   │   │   ├── Requests/             # Valida os campos de entrada (retorna 422 automático)
│   │   │   └── Resources/            # Formata o JSON de saída da API
│   │   ├── Models/                   # Entidades do banco (User, Availability, Appointment)
│   │   ├── Services/                 # Regras de negócio (cálculo de slots, agendamentos)
│   │   ├── Repositories/
│   │   │   ├── Contracts/            # Interfaces que definem o contrato de acesso a dados
│   │   │   └── Eloquent/             # Implementação concreta usando Eloquent
│   │   ├── Policies/                 # Controle de permissão por perfil (admin vs atendente)
│   │   ├── Providers/                # Registra repositórios e policies no container do Laravel
│   │   ├── Enums/                    # Tipos fixos da aplicação (perfis: admin e atendente)
│   │   └── Support/                  # ApiResponse: formata todo JSON de resposta da API
│   ├── config/                       # Configurações do Laravel (auth, jwt, cors, database...)
│   ├── bootstrap/
│   │   └── app.php                   # Inicializa o Laravel + handler global de exceções
│   ├── database/
│   │   ├── migrations/               # Criação das tabelas no banco
│   │   ├── seeders/                  # Dados iniciais (admin, atendentes, disponibilidades)
│   │   └── factories/                # Gera dados falsos para popular os seeders
│   └── routes/
│       └── api.php                   # Define todas as rotas da API
│
├── frontend/                         # SPA em Vue 3 + Vite
│   └── src/
│       ├── views/                    # Páginas (Login, Usuários, Disponibilidade, Agenda)
│       ├── components/               # Componentes reutilizáveis (tabela, modal, inputs, botões)
│       ├── stores/                   # Estado global com Pinia (auth, usuários, agenda, toasts)
│       ├── services/                 # Chamadas à API com Axios (um arquivo por recurso)
│       ├── router/                   # Rotas do Vue + guards de autenticação e perfil
│       ├── assets/                   # Estilos globais da aplicação
│       ├── App.vue                   # Componente raiz com o layout da sidebar
│       └── main.js                   # Inicializa o Vue, Pinia e o Router
│
├── docker/                           # Configurações de infraestrutura
│   ├── php/                          # Dockerfile, entrypoint e OPcache do PHP
│   └── nginx/                        # Configuração do servidor web (proxy para o php-fpm)
│
├── .env.example                      # Modelo das variáveis de ambiente da raiz
├── docker-compose.yml                # Orquestra os 4 containers (php, nginx, mysql, node)
└── Makefile                          # Atalhos para os comandos mais usados
```

---

## Comandos úteis

```bash
# Ver logs em tempo real
docker compose logs -f

# Recriar o banco do zero
docker compose exec php php artisan migrate:fresh --seed --force

# Parar os containers
docker compose down
```

---

## Problemas comuns

**Os containers sobem mas o frontend não conecta na API**
O PHP pode ainda estar instalando as dependências. Aguarde 1-2 minutos e recarregue a página.

**Erro ao rodar o migrate**
O MySQL pode ainda estar iniciando. Aguarde 30 segundos e tente novamente.

**Docker Desktop não abre no Windows**
Verifique se o WSL está instalado (`wsl --install`) e reinicie o computador.
