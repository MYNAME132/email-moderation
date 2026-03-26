<?php

namespace App\DTO;

class PaginationDto
{
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $lastPage,
        public int $perPage = 5,
        public int $total
    ) {}

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'meta' => [
                'current_page' => $this->currentPage,
                'last_page' => $this->lastPage,
                'per_page' => $this->perPage,
                'total' => $this->total
            ]
        ];
    }
}
