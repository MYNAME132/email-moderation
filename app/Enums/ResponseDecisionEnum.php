<?php

namespace App\Enums;

enum ResponseDecisionEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
