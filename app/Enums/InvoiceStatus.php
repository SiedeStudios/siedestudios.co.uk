<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Void = 'void';

    public function isPayable(): bool
    {
        return $this === self::Open;
    }

    public function isPublic(): bool
    {
        return $this !== self::Draft;
    }
}
