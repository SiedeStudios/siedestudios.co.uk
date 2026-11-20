<?php

use App\Models\MonitorCheck;
use App\Models\MonitorDay;
use App\Models\Share;
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune', ['--model' => [Share::class, MonitorCheck::class, MonitorDay::class]])->daily()->withoutOverlapping();
Schedule::command('monitors:check')->everyMinute()->withoutOverlapping(5)->runInBackground();
Schedule::command('privacy:prune')->dailyAt('03:17')->withoutOverlapping();
Schedule::command('gmodstore:sync')->hourly()->withoutOverlapping();
