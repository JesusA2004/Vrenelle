<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('internal_name')->nullable();
            $table->text('description')->nullable();
            $table->string('type'); // percentage | fixed | special_price | free_shipping | collection_discount | category_discount
            $table->decimal('value', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('minimum_purchase')->nullable(); // cents
            $table->unsignedBigInteger('maximum_discount')->nullable(); // cents
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('stackable')->default(false);
            $table->timestamps();

            $table->index(['active', 'starts_at', 'ends_at']);
        });

        Schema::create('promotion_category', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['promotion_id', 'category_id']);
        });

        Schema::create('promotion_collection', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->primary(['promotion_id', 'collection_id']);
        });

        Schema::create('promotion_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotion_collection');
        Schema::dropIfExists('promotion_category');
        Schema::dropIfExists('promotions');
    }
};
