<?php

namespace App\Services;

use App\Contracts\EmailServiceInterface;
use App\Contracts\OllamaClassificationServiceInterface;
use App\Contracts\ReadEmailServiceInterface;
use App\Enums\ResponseDecisionEnum;
use App\Jobs\ProcessEmailAIJob;
use App\Services\Helpers\EmailClassificationService;
use App\Services\Helpers\EmailParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReadEMailService implements ReadEmailServiceInterface
{
    private string $imapPath;
    private string $username;
    private string $password;
    private EmailServiceInterface $emailService;
    private EmailParser $emailParser;
    private EmailClassificationService $classifier;
    private OllamaClassificationServiceInterface $ollama_classifier;

    public function __construct(
        EmailService $emailService,
        EmailParser $emailParser,
        EmailClassificationService $classifier,
        OllamaClassificationServiceInterface $ollama_classifier
    ) {
        $this->emailService = $emailService;
        $this->emailParser = $emailParser;
        $this->classifier = $classifier;
        $this->ollama_classifier = $ollama_classifier;
        $this->imapPath = sprintf(
            "{%s:%s/imap/%s/novalidate-cert}INBOX",
            env('MAILBOX_HOST', 'imap.gmail.com'),
            env('MAILBOX_PORT', '993'),
            env('MAILBOX_ENCRYPTION', 'ssl')
        );
        $this->username = env('MAILBOX_USERNAME', '');
        $this->password = env('MAILBOX_PASSWORD', '');
    }

    /**
     * Fetch unread emails and store them in the database.
     */
    public function fetchUnread(): void
    {
        $conn = @imap_open($this->imapPath, $this->username, $this->password);

        if (!$conn) {
            Log::error('IMAP connection failed', ['error' => imap_last_error()]);
            return;
        }

        $emails = imap_search($conn, 'UNSEEN');

        if ($emails === false) {
            Log::warning('IMAP search failed or returned false', [
                'error' => imap_last_error()
            ]);
            $emails = [];
        }

        Log::info('Unread count: ' . count($emails));

        if (empty($emails)) {
            Log::info('No new emails found.');
            imap_close($conn);
            return;
        }

        $emails = array_slice($emails, 0, 2);

        foreach ($emails as $emailNumber) {
            $overview = imap_fetch_overview($conn, $emailNumber, 0)[0];

            try {
                $parsed = $this->emailParser->parse($conn, $emailNumber);

                $emailModel = null;

                DB::transaction(function () use ($overview, $parsed, &$emailModel) {

                    $emailModel = $this->emailService->create([
                        'sender'   => $overview->from,
                        'receiver' => $this->username,
                        'subject'  => $overview->subject ?? null,
                    ]);

                    $emailModel->document()->create([
                        'body' => [
                            'content' => $parsed['body']
                        ]
                    ]);

                    foreach ($parsed['links'] as $url) {
                        $emailModel->links()->create(['url' => $url]);
                    }

                    $decision = $this->classifier->classify($emailModel);

                    $emailModel->update([
                        'response_decision' => $decision
                    ]);
                });

                if ($emailModel && $emailModel->response_decision === ResponseDecisionEnum::PENDING) {
                    ProcessEmailAIJob::dispatch($emailModel->id);

                    Log::info('Ollama job dispatched', [
                        'email_id' => $emailModel->id
                    ]);
                }

                imap_setflag_full($conn, $emailNumber, '\\Seen');

                Log::info('Email stored successfully', [
                    'from'        => $overview->from,
                    'subject'     => $overview->subject,
                    'links_count' => count($parsed['links'])
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to store email', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        imap_close($conn);
    }
}
