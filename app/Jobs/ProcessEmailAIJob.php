<?php

namespace App\Jobs;

use App\Models\Email;
use App\Enums\ResponseDecisionEnum;
use App\Services\Ollama\OllamaClassificationService;
use App\Jobs\GenerateSuggestedResponsesJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEmailAIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $emailId;

    public $tries = 3;       // retry if Ollama fails
    public $backoff = 10;    // seconds between retries


    /**
     * Create a new job instance.
     */
    public function __construct(string $emailId)
    {
        $this->emailId = $emailId;

        $this->onQueue('ollama_validate');
    }

    /**
     * Execute the job.
     */
    public function handle(OllamaClassificationService $ollama)
    {
        Log::info('ProcessEmailWithOllama job started');
        $email = Email::find($this->emailId);

        if (!$email) {
            Log::warning('OllamaJob: Email not found', ['id' => $this->emailId]);
            return;
        }

        if ($email->response_decision !== ResponseDecisionEnum::PENDING) {
            return;
        }

        try {
            $text = ($email->subject ?? '') . "\n" . $email->body;

            $response = $ollama->classify($text);

            $decision = ResponseDecisionEnum::tryFrom(strtolower($response))
                ?? ResponseDecisionEnum::PENDING;

            $email->update([
                'response_decision' => $decision
            ]);

            if ($decision === ResponseDecisionEnum::APPROVED) {
                Log::info('Dispatching GenerateSuggestedResponsesJob', [
                    'email_id' => $email->id
                ]);
                GenerateSuggestedResponsesJob::dispatch($email->id);
            }

            Log::info('Ollama processed email', [
                'email_id' => $email->id,
                'decision' => $decision->value
            ]);
        } catch (\Throwable $e) {
            Log::error('Ollama job failed', [
                'email_id' => $this->emailId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
