<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_company')->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 16)->default('draft');
            $table->text('notes')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('paypal_txn_id')->nullable()->unique();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('payer_email')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ipn_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('txn_id')->nullable()->index();
            $table->string('txn_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('verification', 16);
            $table->string('outcome', 32)->nullable();
            $table->string('mc_gross')->nullable();
            $table->string('mc_currency', 8)->nullable();
            $table->string('payer_email')->nullable();
            $table->string('receiver_email')->nullable();
            $table->text('notes')->nullable();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipn_logs');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
