<?php

namespace App\DTO;

use App\Models\Email;

class EmailDto
{
    public function __construct(
        public string $id,
        public string $sender,
        public string $receiver,
        public ?string $subject,
        public string $status,
        public string $response_decision,
        public string $createdAt,
        public array $links,
        public ?string $body = null,
    ) {}

    public static function fromModel(Email $email): self
    {
        $email->loadMissing('document');

        $body = null;
        if ($email->document && isset($email->document->body['content'])) {
            $body = $email->document->body['content'];
        }

        return new self(
            id: $email->id,
            sender: $email->sender,
            receiver: $email->receiver,
            subject: $email->subject,
            status: $email->status->value,
            response_decision: $email->response_decision?->value ?? 'pending',
            createdAt: $email->created_at->toISOString(),
            links: $email->links
                ->map(fn($link) => $link->url)
                ->toArray(),
            body: $body,
        );
    }
}
