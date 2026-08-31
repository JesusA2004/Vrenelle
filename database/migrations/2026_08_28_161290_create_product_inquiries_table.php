<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30);
            $table->unsignedInteger('quantity')->default(1);
            $table->text('message')->nullable();
            $table->string('status')->default('pending'); // pending | contacted | resolved | discarded
            $table->string('source')->default('whatsapp'); // whatsapp | form
            $table->timestamp('created_at')->nullable();

            $table->index(['product_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_inquiries');
    }
};
