<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailRequest;
use App\Services\EmailService;
use App\Contracts\EmailServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(
        private EmailServiceInterface $emailService
    ) {}


    public function sendEmail(SendEmailRequest $request)
    {
        Log::info('Incoming send email request', [
            'payload' => $request->validated()
        ]);

        try {
            $data = $request->toDto();

            $email = $this->emailService->create($data);

            return response()->json([
                'message' => 'Email queued for sending',
                'email_id' => $email->id
            ], 201);
        } catch (\Throwable $e) {

            Log::error('Email creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }

    public function getEmails(Request $request, EmailService $emailService)
    {
        try {
            $result = $emailService->getAll($request);
            return response()->json($result->toArray());
        } catch (\Throwable $e) {
            Log::error('Failed to fetch emails', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
