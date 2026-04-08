<?php

namespace App\Http\Controllers;

use App\Contracts\OllamaResponseGenerationServiceInterface;
use App\Http\Requests\GenerateFromPromptRequest;
use App\Models\Email;
use App\Models\UserPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OllamaController extends Controller
{
    public function __construct(
        private OllamaResponseGenerationServiceInterface $responseGenerationService
    ) {}

    public function generate(GenerateFromPromptRequest $request): JsonResponse
    {
        Log::info('Incoming direct prompt generation request');

        $email = Email::with('document')->findOrFail($request->validated('email_id'));
        $emailContent = $email->document?->body['content'] ?? '';

        $prompt = $request->validated('prompt');

        $responses = $this->responseGenerationService->generateFromPrompt($prompt, $emailContent);

        UserPrompt::create([
            'email_id' => $email->id,
            'prompt' => $prompt,
            'response' => implode("\n", $responses),
        ]);

        return response()->json(['responses' => $responses]);
    }
}
