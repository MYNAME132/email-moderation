# Email Moderation System Architecture

The system runs scheduled jobs every minute to:

- fetch emails
- process them using AI
- generate suggested responses
- manage failures and retries

---

## System Architecture

The application follows a **pipeline-based processing architecture**.

This ensures a fully automated workflow with fault tolerance and scalability.

---

## Database Organization

The database is built around the **emails** table, which acts as the central
entity of the system.

### Core Tables

- emails
- documents
- links
- suggested_responses

### Laravel System Tables

- jobs
- failed_jobs
- job_batches
- cache
- cache_locks
- migrations
- sessions
- password_reset_tokens
- users

---

## Database Relationships

### Relationships

- Emails → Documents (1:1, CASCADE delete)
- Emails → Links (1:many, CASCADE delete)
- Emails → Suggested Responses (1:many, CASCADE delete)
- Emails → Selected Response (many:1, SET NULL on delete)

This structure ensures:

- structured email storage
- flexible document format
- scalable AI response generation
- safe deletion and consistency

---

## Database Tables

### emails

Core table storing email metadata.

**Stores:**

- sender
- receiver
- subject
- status
- response decision
- response status
- selected response

**Features:**

- UUID primary keys
- indexed sender and receiver
- indexed status and response fields
- foreign key to suggested_responses

This table drives the entire workflow.

---

### documents

Stores email body as JSONB.

**Purpose:**

- flexible email content storage
- structured AI-readable format
- full-text search support
- attachments and metadata

**Features:**

- one document per email
- JSONB body
- GIN index for fast search
- cascade delete with email

---

### links

Stores URLs extracted from email content.

**Purpose:**

- track links
- detect suspicious URLs
- store references

**Relationship:** One email can contain multiple links.

---

### suggested_responses

Stores AI-generated responses.

**Purpose:**

- multiple response suggestions
- user or system selection
- final response storage

**Flow:** AI generates several responses → user selects one → email stores
selected response.

---

## Job Pipeline

The system uses Laravel scheduled jobs running every minute.

### FetchEmailsJob

**Queue:** emails

**Responsibilities:**

- connect to Gmail IMAP
- read unread emails
- store them in database
- set status to pending

**Service Used:** ReadEMailService

---

### DispatchPendingEmailsJob

**Queue:** default

**Responsibilities:**

- find pending emails
- dispatch AI processing job
- send to ollama_validate queue

This job connects database and AI processing.

---

### ProcessEmailAIJob

**Queue:** ollama_validate

**Responsibilities:**

- classify email using AI
- determine if email is approved or rejected
- update response decision
- dispatch response generation if approved
- retry up to 3 times on failure

**Service Used:** OllamaClassificationService

---

### GenerateSuggestedResponsesJob

**Queue:** ollama_response

**Responsibilities:**

- generate AI responses
- store suggestions
- update email status
- retry up to 3 times

**Service Used:** OllamaResponseGenerationService

---

## Job Pipeline Summary

Pipeline stages:

- fetch
- classify
- generate responses
- store results

This provides:

- automation
- scalability
- reliability
- retry mechanism

---

## Controllers Organization

Controllers expose API endpoints and use services for business logic.

### EmailController

- **sendEmail** — creates email and queues it
- **getEmails** — returns filtered emails using EmailService

### SuggestedResponseController

- **getSuggestedResponses** — retrieves AI response suggestions
- **selectResponse** — marks a response as selected

Controllers are organized under `app/Http/Controllers` using dependency
injection and FormRequest validation.

---

## Additional Notes

- **Services:** Business logic is abstracted into services (EmailService,
  OllamaService) under `app/Services`
- **Models:** Eloquent models define relationships and fillable fields (Email,
  SuggestedResponse, etc.)
- **Enums:** Define statuses and decisions (StatusEnum, ResponseDecisionEnum)
- **Deployment:** Use Docker Compose; Ollama must be running for AI features
- **Logging & Monitoring:** Jobs log actions and handle retries automatically

## Planing for futter

- **Database:** Add additional ENUM values to status and response_status for
  more granular control over email states.
- **Controller:** Add an endpoint to send manual responses (without AI) and
  improve handling of suggested responses
- **AI:** Enable prompt injection so users can run custom prompts in addition to
  the pre-existing ones.
- **IMAP:** Replace IMAP with a simpler integration method for fetching emails
