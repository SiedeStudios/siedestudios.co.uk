<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'client_name', 'client_email', 'client_company', 'currency', 'total', 'status',
    'notes', 'issued_at', 'due_at', 'paid_at', 'paypal_txn_id', 'paid_amount', 'payer_email',
])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function ipnLogs(): HasMany
    {
        return $this->hasMany(IpnLog::class)->latest('id');
    }

    public static function generateReference(?string $prefix = null): string
    {
        $prefix = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $prefix ?: 'SS')) ?: 'SS';

        do {
            $reference = $prefix.'-'.now()->format('ym').'-'.Str::upper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function recalculateTotal(): void
    {
        $this->loadMissing('items');

        $minor = $this->items->sum(fn (InvoiceItem $item) => Money::lineTotal($item->quantity, $item->unit_price));

        $this->total = Money::fromMinor($minor);
    }
}
