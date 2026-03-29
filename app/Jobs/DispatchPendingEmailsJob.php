<?php

namespace App\Jobs;

use App\Enums\ResponseDecisionEnum;
use App\Enums\StatusEnum;
use App\Models\Email;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DispatchPendingEmailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Dispatching pending emails...');

        Email::where('response_decision', ResponseDecisionEnum::PENDING)
            ->where('response_status', StatusEnum::PENDING)
            ->chunk(50, function ($emails) {

                foreach ($emails as $email) {

                    Log::info('Dispatching ProcessEmailAIJob', [
                        'email_id' => $email->id
                    ]);

                    ProcessEmailAIJob::dispatch($email->id)
                        ->onQueue('ollama_validate');
                }
            });
    }
}
