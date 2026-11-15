<?php

use App\Models\Share;
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune', ['--model' => [Share::class]])->daily()->withoutOverlapping();
