<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case READY_TO_SEND = 'ready_to_send';
}
