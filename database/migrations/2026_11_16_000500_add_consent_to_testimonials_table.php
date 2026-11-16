<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // UK GDPR: we must be able to show when someone agreed to be published.
            $table->timestamp('consented_at')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', fn (Blueprint $table) => $table->dropColumn('consented_at'));
    }
};
