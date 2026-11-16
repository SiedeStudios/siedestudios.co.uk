<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('gmodstore_id')->unique();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('tagline', 300)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('background_url', 500)->nullable();
            $table->unsignedBigInteger('price')->nullable()->comment('minor units');
            $table->unsignedBigInteger('original_price')->nullable()->comment('minor units, before discount');
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('views')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sales_count')->nullable();
            $table->boolean('sales_capped')->default(false);
            $table->boolean('is_listed')->default(true)->comment('active on GmodStore');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->string('sync_error', 300)->nullable();
            $table->timestamps();

            $table->index(['is_visible', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
