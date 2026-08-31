<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('option_name'); // e.g. "Talla", "Material"
            $table->string('option_value'); // e.g. "7", "Oro amarillo"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_variant_id', 'option_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_options');
    }
};
