<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('url', 500);
            $table->string('method', 4)->default('GET');
            $table->unsignedSmallInteger('expected_status')->nullable()->comment('null = any 2xx/3xx');
            $table->string('keyword', 120)->nullable()->comment('response body must contain this');
            $table->unsignedTinyInteger('timeout')->default(10);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_paused')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 8)->default('unknown');
            $table->unsignedSmallInteger('consecutive_failures')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedInteger('last_response_ms')->nullable();
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->string('last_error', 120)->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('monitor_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->boolean('up');
            $table->unsignedInteger('response_ms')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error', 120)->nullable();
            $table->timestamp('checked_at')->index();

            $table->index(['monitor_id', 'checked_at']);
        });

        // One row per monitor per day — powers the 90-day uptime bars cheaply.
        Schema::create('monitor_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('checks')->default(0);
            $table->unsignedInteger('up_checks')->default(0);
            $table->unsignedBigInteger('total_ms')->default(0);

            $table->unique(['monitor_id', 'date']);
        });

        Schema::create('monitor_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('reason', 120)->nullable();
            $table->timestamps();

            $table->index(['monitor_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_incidents');
        Schema::dropIfExists('monitor_days');
        Schema::dropIfExists('monitor_checks');
        Schema::dropIfExists('monitors');
    }
};
