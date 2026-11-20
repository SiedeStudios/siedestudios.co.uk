<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Brand colour that tints the case study page. With accent_auto it is
            // re-detected from the cover; null means no tint.
            $table->string('accent_color', 7)->nullable()->after('cover_image');
            $table->boolean('accent_auto')->default(true)->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn(['accent_color', 'accent_auto']));
    }
};
