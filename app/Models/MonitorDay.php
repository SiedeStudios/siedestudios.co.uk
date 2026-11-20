<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['monitor_id', 'date', 'checks', 'up_checks', 'total_ms'])]
class MonitorDay extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected function casts(): array
    {
        return ['date' => 'date', 'checks' => 'integer', 'up_checks' => 'integer', 'total_ms' => 'integer'];
    }

    public function prunable()
    {
        return static::where('date', '<', now()->subDays(400)->toDateString());
    }
}
