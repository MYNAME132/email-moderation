<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Contracts\EmailServiceInterface;
use App\Models\Email as EmailModel;
use App\DTO\EmailDto;
use App\DTO\PaginationDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailService implements EmailServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function create(array $data): EmailModel
    {
        $email = EmailModel::create([
            'sender' => $data['sender'],
            'receiver' => $data['receiver'],
            'subject' => $data['subject'] ?? null,
            'status' => StatusEnum::PENDING,
        ]);
        Log::info('Email record created', ['email_id' => $email->id]);
        return $email;
    }

    public function getAll(?Request $request = null, int $perPage = 5): PaginationDto
    {
        $query = EmailModel::with(['links', 'document']);

        if ($request) {
            if ($request->filled('response_decision')) {
                $query->where('response_decision', $request->response_decision);
            }

            if ($request->filled('sender')) {
                $query->where('sender', 'ILIKE', '%' . $request->sender . '%');
            }

            if ($request->filled('subject')) {
                $query->where('subject', 'ILIKE', '%' . $request->subject . '%');
            }
        }

        $query->latest();

        $emails = $query->paginate($perPage);

        $data = collect($emails->items())
            ->map(fn($email) => EmailDto::fromModel($email))
            ->toArray();

        return new PaginationDto(
            data: $data,
            currentPage: $emails->currentPage(),
            lastPage: $emails->lastPage(),
            perPage: $emails->perPage(),
            total: $emails->total()
        );
    }
}
