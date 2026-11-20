<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamp('invite_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('is_admin')->constrained()->nullOnDelete();
        });

        Schema::create('client_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->comment('public case study')->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('status', 16)->default('planning');
            $table->string('staging_url', 300)->nullable();
            $table->date('started_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });

        Schema::create('client_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('client_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('client_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->comment('uploader')->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('mime', 120)->nullable();
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('reference')->constrained()->nullOnDelete();
            $table->foreignId('client_project_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', fn (Blueprint $t) => $t->dropConstrainedForeignId('client_id'));
        Schema::table('invoices', function (Blueprint $t) {
            $t->dropConstrainedForeignId('client_project_id');
            $t->dropConstrainedForeignId('client_id');
        });
        Schema::dropIfExists('client_files');
        Schema::dropIfExists('client_updates');
        Schema::dropIfExists('client_milestones');
        Schema::dropIfExists('client_projects');
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('client_id'));
        Schema::dropIfExists('clients');
    }
};
