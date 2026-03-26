<?php

namespace App\DTO;

use App\Models\SuggestedResponse;

class SuggestedResponseDto
{
    public function __construct(
        public string $id,
        public string $content,
        public bool $isSelected,
        public string $createdAt
    ) {}

    public static function fromModel(SuggestedResponse $response): self
    {
        return new self(
            id: $response->id,
            content: $response->content,
            isSelected: $response->is_selected,
            createdAt: $response->created_at->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_selected' => $this->isSelected,
            'created_at' => $this->createdAt
        ];
    }
}
