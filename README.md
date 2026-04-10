# AI Chatbot Backend API

Laravel backend API for authentication, RBAC, activity logs, chat sessions, streaming AI responses, and voice transcription.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Laravel Sanctum
- OpenAI PHP client

## Requirements

- PHP 8.2+
- Composer
- MySQL / MariaDB
- OpenAI API key for AI chat and voice transcription

## Setup

1. Install dependencies:

```bash
composer install
```

2. Create `.env`:

```bash
cp .env.example .env
```

3. Configure environment values:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_chatbot
DB_USERNAME=root
DB_PASSWORD=
OPENAI_API_KEY=your_key_here
OPENAI_MODEL=gpt-4o-mini
OPENAI_TRANSCRIPTION_MODEL=gpt-4o-mini-transcribe
```

4. Generate app key:

```bash
php artisan key:generate
```

5. Run migrations and seeders:

```bash
php artisan migrate --seed
```

6. Start the API:

```bash
php artisan serve
```

Default API base:

```text
http://127.0.0.1:8000/api
```

## Important Config

AI config lives in:

- `config/ai.php`

Current keys:

- `OPENAI_MODEL`
- `OPENAI_TRANSCRIPTION_MODEL`

OpenAI credentials live in:

- `config/services.php`

Required key:

- `OPENAI_API_KEY`

## Authentication

Protected routes use Sanctum bearer tokens.

Public auth endpoints:

- `POST /api/register`
- `POST /api/login`
- `POST /api/forgot-password`
- `POST /api/reset-password`
- `POST /api/refresh`

Protected auth endpoints:

- `GET /api/me`
- `POST /api/logout`

## Chat Features

Main chat modules:

- `app/Http/Controllers/Api/Chat`
- `app/Services/Chat`
- `app/Services/AI/OpenAIService.php`

Core features:

- chat session CRUD
- session pinning
- session model switching
- session context updates
- message send
- streamed assistant replies
- message regeneration
- message metadata
- usage tracking
- participant management
- voice transcription

## Chat Endpoints

### Sessions

- `GET /api/chat/sessions`
- `POST /api/chat/sessions`
- `GET /api/chat/sessions/{id}`
- `PUT /api/chat/sessions/{id}`
- `DELETE /api/chat/sessions/{id}`
- `PATCH /api/chat/sessions/{id}/pin`
- `PUT /api/chat/sessions/{id}/model`
- `PUT /api/chat/sessions/{id}/context`
- `DELETE /api/chat/sessions/{id}/messages`

### Messages

- `POST /api/chat/messages/send`
- `POST /api/chat/messages/stream`
- `POST /api/chat/messages/transcribe`
- `GET /api/chat/messages`
- `POST /api/chat/messages/{id}/regenerate`
- `DELETE /api/chat/messages/{id}`

### Metadata

- `GET /api/chat/messages/{id}/metadata`
- `POST /api/chat/messages/{id}/metadata`

### Usage

- `GET /api/chat/usage`
- `GET /api/chat/usage/{chat_id}`

### Participants

- `GET /api/chat/sessions/{id}/participants`
- `POST /api/chat/sessions/{id}/participants`
- `DELETE /api/chat/sessions/{id}/participants/{user_id}`

## Voice Transcription API

Endpoint:

```text
POST /api/chat/messages/transcribe
```

Accepted request fields:

- `audio` required file
- `language` optional string
- `prompt` optional string

Supported upload types are validated in:

- `app/Http/Requests/Api/Chat/MessageTranscribeRequest.php`

Current max upload size:

- `25600 KB`

Successful response shape:

```json
{
  "status": true,
  "message": "Voice transcribed",
  "data": {
    "text": "Hello, can you summarize this thread?",
    "language": "en",
    "duration": 4.82
  }
}
```

Implementation path:

- request validation: `app/Http/Requests/Api/Chat/MessageTranscribeRequest.php`
- controller: `app/Http/Controllers/Api/Chat/MessageController.php`
- chat service pass-through: `app/Services/Chat/ChatService.php`
- OpenAI transcription call: `app/Services/AI/OpenAIService.php`

## Development Notes

- JSON responses use `App\Traits\ApiResponse`
- validation lives under `app/Http/Requests`
- business logic lives under `app/Services`
- chat routes are defined in `routes/api.php`
- Postman collections live in `postman/`

## Useful Commands

```bash
php artisan test
php artisan optimize:clear
php artisan migrate:fresh --seed
```

## Postman

Available collections:

- `postman/ai_chatbot_all_modules_api.postman_collection.json`
- `postman/ai_chatbot_chat_api.postman_collection.json`

## Backend QA Checklist

1. Log in and get bearer token.
2. Create chat session.
3. Send message and verify assistant reply.
4. Stream message and verify chunked response.
5. Upload voice clip to `chat/messages/transcribe`.
6. Verify transcription text, language, and duration are returned.
7. Change session model.
8. Update session context.
9. Add and remove participant.
10. Regenerate assistant message.
