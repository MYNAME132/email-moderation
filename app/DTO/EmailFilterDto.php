<?php

namespace App\DTO;

class EmailFilterDto
{
    public function __construct(
        public ?string $response_decision = null,
        public ?string $sender = null,
        public ?string $subject = null,
        public int $per_page = 5,
        public ?int $page = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            response_decision: $data['response_decision'] ?? null,
            sender: $data['sender'] ?? null,
            subject: $data['subject'] ?? null,
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 5,
            page: isset($data['page']) ? (int) $data['page'] : null,
        );
    }
}
