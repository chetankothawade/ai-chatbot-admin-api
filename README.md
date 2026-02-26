# AI Chatbot Backend API

Laravel-based backend API for authentication, RBAC, activity logs, and chat features.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Laravel Sanctum (API authentication)

## Requirements

- PHP 8.2+
- Composer
- MySQL/MariaDB

## Project Setup

1. Install dependencies:

```bash
composer install
```

2. Create environment file:

```bash
cp .env.example .env
# AI Chatbot — Backend API

Laravel backend API powering authentication, RBAC, activity logs, and AI-enabled chat features.

**Tech stack:** Laravel 12, PHP 8.2+, MySQL/MariaDB, Laravel Sanctum, OpenAI PHP client (optional)

**This repository:** the API server for the AI Chatbot application.

**Repository layout (high level)**
- `app/Http/Controllers` — HTTP controllers (API and web)
- `app/Services` — Business logic and services (e.g. `app/Services/Chat/ChatService.php`)
- `app/Models` — Eloquent models (User, ChatSession, Message, etc.)
- `app/Http/Requests` — Form request validation
- `app/Http/Resources` — API resource transformers
- `database/migrations` and `database/seeders`
- `postman/` — Postman collections for API testing

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- A local web server (WAMP/XAMPP) or `php artisan serve`

On Windows development machines we recommend using WAMP with PHP 8.2+ (your workspace already shows WAMP).

## Quickstart (local)

1. Clone the repo and install PHP dependencies:

```bash
composer install
```

2. Copy the environment file and configure it:

```bash
cp .env.example .env
```

Update `.env` DB settings and other credentials (Mail, OpenAI key if used). Example DB config:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_chatbot
DB_USERNAME=root
DB_PASSWORD=
```

3. Generate the application key:

```bash
php artisan key:generate
```

4. Run migrations and seeders:

```bash
php artisan migrate --seed
```

5. (Optional) Create storage symlink for public uploads:

```bash
php artisan storage:link
```

6. Start the server:

```bash
php artisan serve
```

Default API base: `http://127.0.0.1:8000/api`

## Environment variables (important)

- `DB_*` — Database connection
- `MAIL_*` — Mail driver (password reset)
- `OPENAI_API_KEY` — If using AI features via OpenAI
- `APP_URL` — Base application URL

## Authentication

This project uses token-based auth via Laravel Sanctum for API routes.

- Login: `POST /api/login`
- Register: `POST /api/register`
- Refresh token: `POST /api/refresh`
- Password reset: `POST /api/forgot-password`, `POST /api/reset-password`

Protected endpoints require the `Authorization: Bearer <token>` header.

## Chat & AI features

- Chat business logic lives in `app/Services/Chat` (see `ChatService.php`).
- Messages, sessions, participants are modeled under `app/Models` (`Message`, `ChatSession`, `ChatParticipant`).
- AI usage logging is in `app/Models/AIUsageLog.php`.
- If AI integrations are enabled, ensure `OPENAI_API_KEY` is set in `.env`.

## Postman & API exploration

Import the Postman collections in `postman/`:

- `postman/ai_chatbot_all_modules_api.postman_collection.json` — full API
- `postman/ai_chatbot_chat_api.postman_collection.json` — chat subset

Set collection variable `baseUrl` to your API base (e.g. `http://127.0.0.1:8000/api`) and use `token` for authenticated requests.

## Useful artisan commands

- Run tests: `php artisan test`
- Migrate & seed fresh: `php artisan migrate:fresh --seed`
- Clear caches: `php artisan optimize:clear`

## Development notes

- Controllers use `App\\Traits\\ApiResponse` for standardized JSON responses.
- Validation is implemented in request classes under `app/Http/Requests`.
- Business logic is implemented in services under `app/Services`.
- Exceptions and JSON rendering are configured in `bootstrap/app.php`.

## Contributing

Please open issues or PRs for bug fixes and improvements. Follow existing coding patterns and add tests where applicable.

## Where to look next

- Chat service: [app/Services/Chat/ChatService.php](app/Services/Chat/ChatService.php)
- API routes: [routes/api.php](routes/api.php)
- Postman collections: [postman/](postman/)

---

If you'd like, I can also:
- run the test suite locally,
- add a small Getting Started script, or
- generate OpenAPI docs from routes.
# ai-chatbot-admin-api
