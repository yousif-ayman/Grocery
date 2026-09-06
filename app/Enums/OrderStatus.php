<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PLACED = 'placed';
    case CANCELLED = 'cancelled';
}