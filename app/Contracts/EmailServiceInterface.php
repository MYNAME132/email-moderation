<?php

namespace App\Contracts;

use App\DTO\PaginationDto;
use App\Models\Email;
use Illuminate\Http\Request;

interface EmailServiceInterface
{
    public function create(array $data): Email;

    public function getAll(?Request $request = null, int $perPage = 5): PaginationDto;
}
