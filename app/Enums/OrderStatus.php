<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PRE_ORDER = 'pre_order';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PRE_ORDER => 'Pre-Order',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
