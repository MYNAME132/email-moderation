<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Contracts\EmailServiceInterface;
use App\Models\Email as EmailModel;
use App\DTO\EmailDto;
use App\DTO\EmailFilterDto;
use App\DTO\PaginationDto;
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

    public function getAll(?EmailFilterDto $filter = null): PaginationDto
    {
        $query = EmailModel::with(['links', 'document']);

        if ($filter?->response_decision) {
            $query->where('response_decision', $filter->response_decision);
        }

        if ($filter?->sender) {
            $query->where('sender', 'ILIKE', '%' . $filter->sender . '%');
        }

        if ($filter?->subject) {
            $query->where('subject', 'ILIKE', '%' . $filter->subject . '%');
        }

        $query->latest();

        $perPage = $filter?->per_page ?? 5;
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
