<?php

namespace App\Jobs;

use App\Enums\ResponseDecisionEnum;
use App\Enums\StatusEnum;
use App\Models\Email;
use App\Models\SuggestedResponse;
use App\Services\Ollama\OllamaResponseGenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSuggestedResponsesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $emailId;

    public $tries = 3;
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(string $emailId)
    {
        $this->emailId = $emailId;

        $this->onQueue('ollama_response');
    }

    /**
     * Execute the job.
     */
    public function handle(OllamaResponseGenerationService $ollama): void
    {
        Log::info('GenerateSuggestedResponsesJob started', [
            'email_id' => $this->emailId
        ]);

        $email = Email::with('document')->find($this->emailId);

        if (!$email) {
            Log::warning('Email not found', [
                'email_id' => $this->emailId
            ]);
            return;
        }

        if ($email->response_decision !== ResponseDecisionEnum::APPROVED) {
            return;
        }

        if ($email->suggestedResponses()->exists()) {
            return;
        }

        $document = $email->document?->body;

        $emailContent = is_array($document)
            ? ($document['text'] ?? json_encode($document))
            : $document;

        if (!$document) {
            return;
        }

        try {

            $responses = $ollama->generateResponses($emailContent);

            foreach ($responses as $response) {
                SuggestedResponse::create([
                    'email_id' => $email->id,
                    'content' => $response,
                    'is_selected' => false
                ]);
            }

            $email->update([
                'status' => StatusEnum::SENT
            ]);

            Log::info('Suggested responses created', [
                'email_id' => $email->id
            ]);
        } catch (\Throwable $e) {

            Log::error('AI response generation failed', [
                'email_id' => $email->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }

        Log::info('Suggested responses created', [
            'email_id' => $email->id
        ]);
    }
}
