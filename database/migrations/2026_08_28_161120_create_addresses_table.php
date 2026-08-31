<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label')->default('casa'); // casa | oficina | otra
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('street');
            $table->string('ext_number', 20);
            $table->string('int_number', 20)->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 10);
            $table->string('country', 2)->default('MX');
            $table->text('references')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
