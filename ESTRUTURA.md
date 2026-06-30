# ESTRUTURA DO PROJETO — guia para apresentar na entrevista

Este documento descreve **cada arquivo relevante**: o que faz, quem o chama e como se conecta aos demais.
No fim há o **fluxo completo** de uma requisição (request → controller → validação → service → repository → model → resource → resposta).

---

## Visão geral de pastas

```
Agendamento/
├── docker-compose.yml        # sobe TUDO (nginx, php, mysql, node) com um comando
├── Makefile                  # atalhos: up, migrate, seed, jwt, fresh...
├── README.md                 # como subir, credenciais, rotas, arquitetura
├── ESTRUTURA.md              # este arquivo
├── .env                      # variáveis do docker-compose (nível raiz)
├── docker/                   # imagens e configs de infra
├── backend/                  # API Laravel 11
└── frontend/                 # SPA Vue 3
```

---

## 1. Infra (Docker)

| Arquivo | O que faz | Conexões |
|---|---|---|
| `docker-compose.yml` | Define 4 serviços: **mysql** (banco), **php** (php-fpm/Laravel), **nginx** (expõe a API na :8000), **node** (Vite na :5173). | `php` depende de `mysql`; `nginx` encaminha `.php` para `php`; `node` lê `VITE_API_URL`. |
| `docker/php/Dockerfile` | Imagem PHP 8.2-fpm com extensões (pdo_mysql, mbstring…) + Composer. | Usada pelo serviço `php`. |
| `docker/php/entrypoint.sh` | Na 1ª subida: `composer install`, cria `.env`, gera **APP_KEY** e **JWT_SECRET**. | Executado ao iniciar o container `php`. |
| `docker/nginx/default.conf` | Vhost que serve `backend/public` e faz fastcgi para `php:9000`. | Montado no serviço `nginx`. |
| `Makefile` | Atalhos para os comandos do dia a dia. | Chama `docker compose exec php php artisan ...`. |
| `.env` (raiz) | Credenciais do MySQL e `VITE_API_URL` para o compose. | Lido pelo `docker-compose.yml`. |

---

## 2. Backend (Laravel 11)

### 2.1 Bootstrap e configuração

| Arquivo | O que faz |
|---|---|
| `backend/public/index.php` | Porta de entrada HTTP: recebe a request e entrega ao Laravel. Apontado pelo nginx. |
| `backend/artisan` | Porta de entrada CLI (migrate, seed, jwt:secret…). |
| `backend/bootstrap/app.php` | **Coração da configuração** no Laravel 11: registra as rotas (`routes/api.php` com prefixo `/api`) e o **handler global de exceções** que converte tudo no JSON `{success,message,data}` com o HTTP status correto (RQNF2). |
| `backend/bootstrap/providers.php` | Lista o `AppServiceProvider`. |
| `backend/config/auth.php` | Define o guard **`api` com driver `jwt`** e o provider de usuários (Eloquent). É o que faz o middleware `auth:api` usar JWT. |
| `backend/config/jwt.php` | Config do pacote `jwt-auth` (TTL, secret, blacklist). |
| `backend/config/cors.php` | Libera o front (`http://localhost:5173`) a chamar a API. |
| `backend/config/database.php` | Conexão MySQL (lê `DB_HOST=mysql` etc.). |
| `backend/config/*.php` (app, cache, logging, queue, session, filesystems) | Configs padrão do framework necessárias para o app inicializar. |
| `backend/composer.json` | Dependências: Laravel 11, `php-open-source-saver/jwt-auth`. |

### 2.2 Rotas

| Arquivo | O que faz | Conexões |
|---|---|---|
| `backend/routes/api.php` | Mapeia cada rota para um método de Controller. `/login` é pública; o resto fica no grupo `middleware('auth:api')`. | Ponto de partida de toda requisição → Controllers. |
| `backend/routes/console.php` | Comando artisan de exemplo. | — |

### 2.3 Enum e Models

| Arquivo | O que faz | Conexões |
|---|---|---|
| `app/Enums/UserRole.php` | Enum `admin`/`atendente` + `label()` e `values()`. | Usado no Model User (cast), Policies, Requests, Resources, Seeders. |
| `app/Models/User.php` | Entidade usuário. **Implementa `JWTSubject`** (`getJWTIdentifier()` = id; `getJWTCustomClaims()` = `{role}` no token). Casts: senha `hashed`, role para o enum. SoftDeletes. Helpers `isAdmin()`/`isAtendente()`. | Repositório, AuthService, Policies; relação `hasMany` com Availability/Appointment. |
| `app/Models/Availability.php` | Janela de disponibilidade (user_id, weekday, start/end, active). | AvailabilityRepository, ScheduleService. |
| `app/Models/Appointment.php` | Agendamento (slot ocupado) com dados mock do cliente. | AppointmentRepository, ScheduleService. |

### 2.4 Repositories (acesso a dados)

| Arquivo | O que faz | Conexões |
|---|---|---|
| `app/Repositories/Contracts/*Interface.php` | **Contratos** (UserRepository, AvailabilityRepository, AppointmentRepository). Os Services dependem da interface, não do Eloquent. | Injetados nos Services; ligados às implementações no `AppServiceProvider`. |
| `app/Repositories/Eloquent/UserRepository.php` | CRUD de usuários via Eloquent (inclui `findByEmail`, soft delete). | Implementa `UserRepositoryInterface`. |
| `app/Repositories/Eloquent/AvailabilityRepository.php` | Busca janelas; método-chave `activeForUserAndWeekday()`. | Usado por AvailabilityService e ScheduleService. |
| `app/Repositories/Eloquent/AppointmentRepository.php` | `scheduledForUserAndDate()` (slots ocupados) e `create()`. | Usado por ScheduleService. |
| `app/Providers/AppServiceProvider.php` | **Liga interface → implementação** (bind) e registra as Policies. | Faz a injeção de dependência funcionar. |

### 2.5 Services (regra de negócio)

| Arquivo | O que faz | Conexões |
|---|---|---|
| `app/Services/AuthService.php` | Login/me/logout/refresh encapsulando o guard `api`. Monta token + TTL. | Chamado por `AuthController`. |
| `app/Services/UserService.php` | Criar/editar/excluir usuário. Na edição **ignora e-mail e senha** (não editáveis — RQF1.3). | Chamado por `UserController`; usa `UserRepository`. |
| `app/Services/AvailabilityService.php` | CRUD de disponibilidade. | Chamado por `AvailabilityController`; usa `AvailabilityRepository`. |
| `app/Services/ScheduleService.php` | **Núcleo da agenda.** `availableSlots()`: pega janelas ativas do dia → fatia em slots de **60 min** → remove os ocupados. `book()`: agenda um slot validando que ele está livre (senão 422). | Chamado por `ScheduleController`; usa Availability + Appointment repositories. |

### 2.6 Form Requests (validação de entrada)

| Arquivo | Valida | Disparado por |
|---|---|---|
| `app/Http/Requests/Auth/LoginRequest.php` | e-mail + senha obrigatórios. | `AuthController@login` |
| `app/Http/Requests/User/StoreUserRequest.php` | e-mail **único** + válido, senha **min 8** + **confirmação**, role válido. | `UserController@store` |
| `app/Http/Requests/User/UpdateUserRequest.php` | só `name`/`role` (e-mail e senha fora — não editáveis). | `UserController@update` |
| `app/Http/Requests/Availability/StoreAvailabilityRequest.php` | atendente existe, weekday 0-6, **end_time > start_time**, active boolean. | `AvailabilityController@store` |
| `app/Http/Requests/Availability/UpdateAvailabilityRequest.php` | idem (sem trocar o atendente). | `AvailabilityController@update` |
| `app/Http/Requests/Schedule/AvailableSlotsRequest.php` | `attendant_id` + `date` (YYYY-MM-DD). | `ScheduleController@available` |
| `app/Http/Requests/Appointment/StoreAppointmentRequest.php` | atendente, data, horário, nome do cliente. | `ScheduleController@store` |

> Quando a validação falha, o Laravel lança `ValidationException`, capturada pelo handler global → **422** com `errors` por campo.

### 2.7 Policies (permissões admin × atendente)

| Arquivo | Regras |
|---|---|
| `app/Policies/UserPolicy.php` | `viewAny`: todos. `create`: só admin. `update`: admin **ou** o próprio. `delete`: só admin e não a si mesmo. |
| `app/Policies/AvailabilityPolicy.php` | `viewAny`: todos. `create/update/delete`: só admin. |

> Chamadas via `$this->authorize(...)` no Controller; ao negar, geram **403** padronizado.

### 2.8 Controllers (orquestração)

| Arquivo | Rotas | O que faz |
|---|---|---|
| `app/Http/Controllers/Controller.php` | base | Traz o trait `AuthorizesRequests` (`$this->authorize`). |
| `app/Http/Controllers/AuthController.php` | login/me/logout/refresh | Chama `AuthService`; login inválido → 401. |
| `app/Http/Controllers/UserController.php` | `/users` (CRUD) | `authorize` (Policy) → `UserService` → `UserResource`. |
| `app/Http/Controllers/AvailabilityController.php` | `/availabilities` (CRUD) | `authorize` → `AvailabilityService` → `AvailabilityResource`. |
| `app/Http/Controllers/ScheduleController.php` | `/schedule/available`, `/appointments` | `ScheduleService` → lista slots / cria agendamento. |

### 2.9 Resources e helper de resposta

| Arquivo | O que faz |
|---|---|
| `app/Http/Resources/UserResource.php` | JSON do usuário (id, name, email, role, role_label). **Nunca expõe a senha.** |
| `app/Http/Resources/AvailabilityResource.php` | JSON da janela (+ `weekday_label` e horas em HH:MM). |
| `app/Http/Resources/AppointmentResource.php` | JSON do agendamento. |
| `app/Support/ApiResponse.php` | `success()` / `error()` — **envelope único** `{success,message,data(+errors)}` usado por todos os controllers e pelo handler global. |

### 2.10 Banco (migrations, factories, seeders)

| Arquivo | O que faz |
|---|---|
| `database/migrations/..._create_users_table.php` | Tabela `users` (role enum, soft delete). |
| `database/migrations/..._create_availabilities_table.php` | Tabela `availabilities` (FK user, weekday, horários, active). |
| `database/migrations/..._create_appointments_table.php` | Tabela `appointments` (FK user, data, horários, status; unique por slot). |
| `database/factories/*Factory.php` | Geram dados fake (User com estado `admin()`/`atendente()`). |
| `database/seeders/UserSeeder.php` | admin@admin.com + 3 atendentes (senha 12345678). |
| `database/seeders/AvailabilitySeeder.php` | Seg-sex, manhã (08-12) e tarde (13-17) para cada atendente. |
| `database/seeders/AppointmentSeeder.php` | Ocupa 08:00 e 10:00 da Ana na próxima segunda (mock — RQF2.1). |
| `database/seeders/DatabaseSeeder.php` | Orquestra a ordem dos seeders. |

---

## 3. Frontend (Vue 3)

| Arquivo | O que faz | Conexões |
|---|---|---|
| `frontend/index.html` | HTML raiz com `<div id="app">`. | Monta o `main.js`. |
| `frontend/vite.config.js` | Vite + alias `@` → `src`, server em 0.0.0.0:5173. | — |
| `frontend/.env` | `VITE_API_URL`. | Lido por `http.js`. |
| `src/main.js` | Cria o app, registra **Pinia** e **Router**, importa o CSS. | Ponto de entrada. |
| `src/App.vue` | Layout: navbar (links condicionais por perfil) + `<RouterView>` + toasts. | Usa auth store e router. |
| `src/assets/styles.css` | Estilos globais (cards, tabela, badges, modais, toasts, botões). | — |

### 3.1 services/ (chamadas à API)

| Arquivo | O que faz |
|---|---|
| `src/services/http.js` | Instância **Axios** + interceptors: injeta `Bearer token`; trata erros globais (401 → logout+redirect; 422 → erros por campo; demais → toast). |
| `src/services/authService.js` | `login`, `me`, `logout`, `refresh`. |
| `src/services/userService.js` | CRUD de usuários. |
| `src/services/availabilityService.js` | CRUD de disponibilidade. |
| `src/services/scheduleService.js` | `available()` (consulta slots) e `book()` (agenda). |

### 3.2 stores/ (estado global — Pinia)

| Arquivo | O que faz |
|---|---|
| `src/stores/auth.js` | Token + usuário (perfil) no localStorage. Getters `isAuthenticated`, `isAdmin`. Alimenta guards e botões condicionais. |
| `src/stores/users.js` | Lista e CRUD de usuários. |
| `src/stores/agenda.js` | Disponibilidades e slots (consulta + booking). |
| `src/stores/toast.js` | Notificações (toasts) usadas pelo interceptor e pelas views. |

### 3.3 router/

| Arquivo | O que faz |
|---|---|
| `src/router/index.js` | Rotas + **guards**: `requiresAuth` (sem token → login), `requiresAdmin` (atendente → bloqueado + toast), `guestOnly` (logado não vê login). |

### 3.4 components/ (reutilizáveis)

| Arquivo | O que faz |
|---|---|
| `src/components/BaseButton.vue` | Botão (primary/danger/ghost, small). |
| `src/components/BaseModal.vue` | Modal (usado em confirmação de exclusão e formulários). |
| `src/components/BaseTable.vue` | Tabela genérica com slots por coluna e de ações. |
| `src/components/FormInput.vue` | Input com label, marcação `*` e erro por campo (RQNF4). |
| `src/components/FormSelect.vue` | Select com label, `*` e erro. |
| `src/components/ToastContainer.vue` | Renderiza os toasts da store. |
| `src/components/UserFormModal.vue` | Form de criar/editar usuário (em edição esconde e-mail/senha). |
| `src/components/AvailabilityFormModal.vue` | Form de criar/editar disponibilidade. |

### 3.5 views/ (páginas)

| Arquivo | Rota | O que faz |
|---|---|---|
| `src/views/LoginView.vue` | `/login` | Form de login; trata 422 por campo. |
| `src/views/UsersView.vue` | `/users` | Listagem (igual p/ todos); botão "Novo" e "Excluir" só p/ admin; "Editar" admin ou próprio; modal de confirmação ao excluir. |
| `src/views/AvailabilityView.vue` | `/availabilities` (admin) | CRUD de disponibilidade. |
| `src/views/ScheduleView.vue` | `/schedule` | Seleciona atendente + data → lista slots livres → permite agendar (o slot some da lista). |

---

## 4. Fluxo completo de uma requisição (exemplo: criar usuário)

```
[Frontend]
UsersView → abre UserFormModal → submit()
   └─ usersStore.create(payload)
        └─ userService.create()  → http.post('/users', payload)
             └─ interceptor injeta  Authorization: Bearer <token>

            ───────────── HTTP POST /api/users ─────────────►

[Backend]
routes/api.php  (grupo auth:api valida o token JWT)
   └─ UserController@store
        ├─ $this->authorize('create', User::class)   → UserPolicy (só admin; senão 403)
        ├─ StoreUserRequest                            → valida (e-mail único, senha min 8 + confirmação; senão 422)
        └─ UserService->create($dados)
             └─ UserRepository->create()               → Model User (senha hasheada pelo cast)
        └─ retorna UserResource  envelopado em  ApiResponse::success(..., 201)

            ◄──────── 201 { success:true, message, data:{user} } ────────

[Frontend]
   └─ store atualiza a lista → toast "Usuário criado" → modal fecha
```

**Tratamento de erros (RQNF2/RQNF3):** qualquer exceção no backend passa pelo handler global em
`bootstrap/app.php` e vira o JSON padronizado com o status certo (401/403/404/422/500). No frontend,
o interceptor de `http.js` captura tudo: 401 desloga, 422 preenche os erros por campo no formulário,
e os demais exibem um toast amigável.

---

## 5. Onde cada requisito foi atendido

| Requisito | Onde |
|---|---|
| RQNF2 (status + JSON único) | `ApiResponse` + handler em `bootstrap/app.php` |
| RQNF3 (erros amigáveis no front) | interceptor em `services/http.js` + `stores/toast.js` |
| RQNF4 (campos `*` obrigatórios) | `FormInput/FormSelect` (marcação) + Form Requests (back) |
| RQF1.1 (listagem + permissões) | `UsersView` + `UserPolicy` |
| RQF1.2/1.3 (inserção/edição) | `UserFormModal` + `StoreUserRequest`/`UpdateUserRequest` + `UserService` |
| RQF2.2 (disponibilidade) | `AvailabilityView/FormModal` + `AvailabilityController/Service` |
| RQF2.3 (horários disponíveis) | `ScheduleView` + `ScheduleService::availableSlots()` |
| Auth JWT | `AuthController` + `AuthService` + guard `api` (config/auth.php) |
