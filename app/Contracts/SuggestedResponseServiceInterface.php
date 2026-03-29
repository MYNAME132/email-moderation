<?php

namespace App\Contracts;

use App\DTO\PaginationDto;
use Illuminate\Http\Request;

interface SuggestedResponseServiceInterface
{
    public function getByEmail(string $emailId, Request $request): PaginationDto;
    public function selectResponse(string $responseId): array;
}
