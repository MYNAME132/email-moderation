# Email Automation — CLAUDE.md

Laravel 12 / PHP 8.2 backend that fetches unread emails via IMAP, classifies
them with rule-based heuristics and Ollama AI, and generates suggested reply
drafts.

## Tech stack

- **Framework:** Laravel 12, PHP 8.2
- **Database:** PostgreSQL (`EmailManager` DB)
- **Queue driver:** database
- **AI:** Ollama (local LLM, default model `phi`, default URL
  `http://ollama:11434`)
- **Email:** IMAP via `imap_*` PHP functions (Gmail default)
- **Testing:** PHPUnit 11

## Common commands

```bash
# First-time setup
composer setup          # install, .env, key:generate, migrate, npm build

# Development (starts server + queue listener + log viewer + vite together)
composer dev

# Run tests
composer test

# Run migrations
php artisan migrate

# Run a specific queue worker
php artisan queue:work --queue=emails
php artisan queue:work --queue=ollama_validate
php artisan queue:work --queue=ollama_response

# Code style (Laravel Pint)
./vendor/bin/pint
```

## Architecture — Email pipeline

```
[Scheduler every minute]
  ├── FetchEmailsJob (queue: emails)
  │     └── ReadEMailService::fetchUnread()
  │           ├── IMAP fetch (max 2 emails per run)
  │           ├── EmailParser → body + links
  │           ├── DB transaction: create Email + Document + Link records
  │           ├── EmailClassificationService (rule-based) → REJECTED | PENDING
  │           └── if PENDING → dispatch ProcessEmailAIJob
  │
  └── DispatchPendingEmailsJob (queue: default)
        └── Re-dispatches any stuck PENDING emails to ollama_validate

ProcessEmailAIJob (queue: ollama_validate, 3 retries, 10s backoff)
  └── OllamaClassificationService → APPROVED | REJECTED
        └── if APPROVED → dispatch GenerateSuggestedResponsesJob

GenerateSuggestedResponsesJob (queue: ollama_response, 3 retries, 10s backoff)
  └── OllamaResponseGenerationService → 3 SuggestedResponse records
```

## Rule-based classifier (`EmailClassificationService`)

Rejects emails matching any of:

- Sender contains `no-reply`, `noreply`, `do-not-reply`
- Sender domain is in blocked list (`codecademy.com`, `greenhouse.io`)
- Subject contains: `notification`, `newsletter`, `update`, `policy`, `privacy`,
  `digest`
- Body contains: `unsubscribe`, `manage preferences`, `privacy policy`,
  `email settings`, `manage my consent`

Anything that passes → `PENDING` (sent to Ollama for further classification).

## Key enums

| Enum                   | Values                            |
| ---------------------- | --------------------------------- |
| `ResponseDecisionEnum` | `pending`, `approved`, `rejected` |
| `StatusEnum`           | `pending`, `sent`, `failed`       |

## Models & relationships

```
Email (UUID)
  ├── hasOne  Document   (body stored as JSON: {content: string})
  ├── hasMany Link       (url)
  ├── hasMany SuggestedResponse
  └── belongsTo SuggestedResponse (selected_response_id)
```

## API routes (all under `/api`, middleware: `RequestLogger`)

| Method | Path                                       | Controller                                          |
| ------ | ------------------------------------------ | --------------------------------------------------- |
| POST   | `/send-email`                              | `EmailController@sendEmail`                         |
| GET    | `/emails`                                  | `EmailController@getEmails`                         |
| GET    | `/emails/{emailId}/suggested-responses`    | `SuggestedResponseController@getSuggestedResponses` |
| PATCH  | `/suggested-responses/{responseId}/select` | `SuggestedResponseController@selectResponse`        |

## Service providers & DI bindings

- `EmailServiceProvider` — binds `ReadEmailServiceInterface`,
  `EmailServiceInterface`
- `SuggestedResponseServiceProvider` — binds `SuggestedResponseServiceInterface`
- `OllamaServiceProvider` — binds `OllamaServiceInterface`,
  `OllamaClassificationServiceInterface`,
  `OllamaResponseGenerationServiceInterface`

## Environment variables

```
MAILBOX_HOST / MAILBOX_PORT / MAILBOX_ENCRYPTION / MAILBOX_USERNAME / MAILBOX_PASSWORD
OLLAMA_BASE_URL    # default: http://ollama:11434
OLLAMA_MODEL       # default: phi
DB_CONNECTION=pgsql
QUEUE_CONNECTION=database
```

## Project structure

```
app/
  Contracts/        # interfaces for all services
  DTO/              # EmailDto, EmailFilterDto, PaginationDto, SuggestedResponseDto
  Enums/            # ResponseDecisionEnum, StatusEnum
  Http/
    Controllers/    # EmailController, SuggestedResponseController
    Middleware/     # RequestLogger
    Requests/       # form request validation
  Jobs/             # FetchEmailsJob, ProcessEmailAIJob, GenerateSuggestedResponsesJob,
                    # DispatchPendingEmailsJob
  Models/           # Email, Document, Link, SuggestedResponse, User
  Providers/        # service provider bindings
  Services/
    EmailService.php
    ReadEMailService.php
    SuggestedResponseService.php
    Helpers/
      EmailParser.php
      EmailClassificationService.php
    Ollama/
      OllamaService.php              # HTTP client to Ollama /api/generate
      OllamaClassificationService.php
      OllamaResponseGenerationService.php
```

## Postman Workflow

After every Postman MCP request:

1. Check docker logs
2. Check laravel.log
3. Check ollama logs
4. Report errors

Commands:

docker logs laravel_queue_emails --tail 20 docker logs
laravel_queue_ollama_validate --tail 20 docker logs
laravel_queue_ollama_response --tail 20
