<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id', 'txn_id', 'txn_type', 'payment_status', 'verification', 'outcome',
    'mc_gross', 'mc_currency', 'payer_email', 'receiver_email', 'notes', 'payload',
])]
class IpnLog extends Model
{
    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
