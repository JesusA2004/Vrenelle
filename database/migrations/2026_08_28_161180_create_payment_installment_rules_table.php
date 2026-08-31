<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_installment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // stripe | paypal | mercadopago
            $table->unsignedTinyInteger('months'); // 3, 6, 9, 12
            $table->unsignedBigInteger('minimum_amount')->default(0); // cents
            $table->unsignedBigInteger('maximum_amount')->nullable(); // cents
            $table->decimal('fee_percentage', 6, 3)->default(0); // total cost of the installment plan
            $table->decimal('customer_fee_percentage', 6, 3)->default(0); // portion passed to customer
            $table->decimal('merchant_fee_percentage', 6, 3)->default(0); // portion absorbed by the store
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['provider', 'months']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_installment_rules');
    }
};
