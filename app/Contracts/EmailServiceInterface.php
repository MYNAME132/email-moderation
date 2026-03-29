<?php

namespace App\Contracts;

use App\DTO\PaginationDto;
use App\Models\Email;
use App\DTO\EmailFilterDto;

interface EmailServiceInterface
{
    public function create(array $data): Email;

    public function getAll(EmailFilterDto $filter = null): PaginationDto;
}
