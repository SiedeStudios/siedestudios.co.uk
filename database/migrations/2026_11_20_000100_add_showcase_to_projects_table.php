<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Settings for the generated cover mockup: which screenshot goes in
            // each window, their title bars and the glow colour.
            $table->json('showcase')->nullable()->after('accent_auto');
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('showcase'));
    }
};
