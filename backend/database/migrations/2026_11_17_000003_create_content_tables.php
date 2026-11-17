<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('country')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('title')->nullable();
            $table->text('text');
            $table->date('review_date')->nullable();
            $table->string('source')->nullable();            // Google, Tripadvisor, Website…
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('reels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('guest')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('poster')->nullable();
            $table->string('video')->nullable();             // uploaded MP4
            $table->string('youtube_id')->nullable();
            $table->string('duration', 10)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('excerpt', 400)->nullable();
            $table->string('cover_image')->nullable();
            $table->longText('body')->nullable();
            $table->string('read_time')->nullable();
            $table->json('related_products')->nullable();    // product ids
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
        });

        // Single-row site settings, edited on the "Site settings" page
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Desert Voyagers Tourism LLC');
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('hours')->nullable();
            $table->string('address')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('award_name')->nullable();
            $table->unsignedSmallInteger('award_year')->nullable();
            $table->string('award_url')->nullable();
            $table->decimal('rating_average', 2, 1)->nullable();
            $table->unsignedInteger('rating_total')->nullable();
            $table->string('notification_email')->nullable(); // where new bookings are emailed
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('reels');
        Schema::dropIfExists('reviews');
    }
};
