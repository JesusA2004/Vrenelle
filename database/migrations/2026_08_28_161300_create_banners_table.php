<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('position')->default('hero'); // hero | story | special_collection | strip
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('desktop_media')->nullable();
            $table->string('mobile_media')->nullable();
            $table->string('video_desktop')->nullable();
            $table->string('video_mobile')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('content_position')->default('center'); // left | center | right
            $table->string('text_theme')->default('light'); // light | dark
            $table->string('overlay')->nullable(); // e.g. "rgba(0,0,0,.35)"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['position', 'active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
