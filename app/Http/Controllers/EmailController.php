<?php

namespace App\Http\Controllers;

use App\Contracts\EmailServiceInterface;
use App\DTO\EmailFilterDto;
use App\Http\Requests\CreateEmailRequest;
use App\Http\Requests\GetEmailsRequest;
use App\Http\Requests\SendEmailRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function __construct(
        private EmailServiceInterface $emailService
    ) {}


    public function sendEmail(CreateEmailRequest $request): JsonResponse
    {
        Log::info('Incoming send email request', [
            'payload' => $request->validated()
        ]);

        $data = $request->toDto();
        $email = $this->emailService->create($data);

        return response()->json([
            'message' => 'Email queued for sending',
            'email_id' => $email->id
        ], 201);
    }

    public function getEmails(GetEmailsRequest $request): JsonResponse
    {
        Log::info('Incoming get emails request', [
            'query' => $request->query()
        ]);

        $filterDto = EmailFilterDto::fromArray($request->validated());
        $result = $this->emailService->getAll($filterDto);

        return response()->json($result->toArray());
    }

    public function sendEmailById(SendEmailRequest $request): JsonResponse
    {
        $emailId = $request->getEmailId();

        Log::info('Incoming dispatch email request', [
            'email_id' => $emailId,
        ]);

        $this->emailService->sendEmail($emailId);

        return response()->json(['message' => 'Email sent successfully']);
    }
}
