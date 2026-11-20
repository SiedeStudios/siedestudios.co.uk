<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Services\Monitoring\MonitorChecker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('monitors:check')]
#[Description('Check every status-page monitor once')]
class CheckMonitors extends Command
{
    public function handle(MonitorChecker $checker): int
    {
        $monitors = Monitor::active()->get();
        $checker->run($monitors);

        $down = $monitors->filter(fn (Monitor $m) => $m->fresh()->status === Monitor::DOWN)->count();
        $this->components->info("Checked {$monitors->count()} monitor(s)".($down ? ", {$down} down" : '').'.');

        return self::SUCCESS;
    }
}
