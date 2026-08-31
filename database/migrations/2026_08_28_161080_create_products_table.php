<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained('collections')->nullOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('stone_id')->nullable()->constrained('stones')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            // Pricing
            $table->unsignedBigInteger('price'); // stored in cents (MXN)
            $table->unsignedBigInteger('compare_at_price')->nullable();
            $table->unsignedBigInteger('cost')->nullable();

            // Inventory / availability
            $table->string('inventory_mode')->default('stock'); // stock | made_to_order | inquiry_only
            $table->integer('stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(2);
            $table->boolean('show_price')->default(true);

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_customizable')->default(false);

            // Jewelry attributes (flexible, nullable)
            $table->string('carats')->nullable();
            $table->string('purity')->nullable();
            $table->string('weight')->nullable();
            $table->string('color')->nullable();
            $table->string('cut')->nullable();
            $table->string('size')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('finish')->nullable();
            $table->string('certification')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('warranty')->nullable();
            $table->text('care_instructions')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index(['is_featured']);
            $table->index(['is_new']);
            $table->index(['category_id', 'is_active']);
            $table->index(['collection_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
