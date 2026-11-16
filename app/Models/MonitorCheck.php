<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Raw check results — kept a week for response-time charts (daily rollups keep uptime). */
#[Fillable(['monitor_id', 'up', 'response_ms', 'http_status', 'error', 'checked_at'])]
class MonitorCheck extends Model
{
    use MassPrunable;

    public $timestamps = false;

    protected function casts(): array
    {
        return ['up' => 'boolean', 'response_ms' => 'integer', 'http_status' => 'integer', 'checked_at' => 'datetime'];
    }

    public function prunable()
    {
        return static::where('checked_at', '<', now()->subDays(7));
    }
}
