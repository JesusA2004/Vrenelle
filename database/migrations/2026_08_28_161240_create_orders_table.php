<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            $table->string('status')->default('pending'); // OrderStatus
            $table->string('payment_status')->default('pending'); // PaymentStatus
            $table->string('fulfillment_status')->default('unfulfilled'); // FulfillmentStatus

            $table->unsignedBigInteger('subtotal')->default(0); // cents
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('currency', 3)->default('MXN');

            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 30);

            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            $table->string('payment_method')->nullable(); // stripe | paypal | mercadopago
            $table->unsignedTinyInteger('installment_months')->nullable();

            $table->text('notes')->nullable();
            $table->string('idempotency_key')->nullable()->unique();

            $table->string('tracking_number')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['payment_status']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
