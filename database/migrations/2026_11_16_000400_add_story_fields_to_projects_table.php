<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('results')->nullable()->after('body')->comment('[{value, label}] headline numbers');
            $table->json('chapter_images')->nullable()->after('results')->comment('project_image id per ## chapter');
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn(['results', 'chapter_images']));
    }
};
