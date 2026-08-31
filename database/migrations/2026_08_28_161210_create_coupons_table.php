<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('type'); // percentage | fixed | free_shipping
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedBigInteger('minimum_purchase')->nullable(); // cents
            $table->unsignedBigInteger('maximum_discount')->nullable(); // cents
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('first_purchase_only')->default(false);
            $table->boolean('stackable')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'starts_at', 'expires_at']);
        });

        Schema::create('coupon_category', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['coupon_id', 'category_id']);
        });

        Schema::create('coupon_collection', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->primary(['coupon_id', 'collection_id']);
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['coupon_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_collection');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupons');
    }
};
