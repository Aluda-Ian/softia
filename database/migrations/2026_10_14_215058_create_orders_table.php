<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders hold both B2B quotation requests (type = quote) and
     * B2C retail orders (type = order). See App\Enums for values.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('type', 20);
            $table->string('status', 20);
            $table->string('customer_type', 10);
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->string('kra_pin', 20)->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->text('delivery_address');
            $table->text('notes')->nullable();
            $table->string('payment_method', 30);
            $table->string('payment_reference')->nullable();
            $table->string('payment_status', 20);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
