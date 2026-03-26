<?php

namespace App\Contracts;

interface ReadEmailServiceInterface
{
    public function fetchUnread(): void;
}
