<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('ticket');      // icon key used by the website
            $table->string('blurb')->nullable();
            $table->string('color')->nullable();            // tile gradient (hex) on the homepage
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tours and tickets share one table (type = tour|ticket)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['tour', 'ticket'])->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('duration')->nullable();
            $table->string('timing')->nullable();

            // pricing (AED)
            $table->decimal('price', 10, 2);                 // adult / "from" price
            $table->decimal('old_price', 10, 2)->nullable(); // shown struck through
            $table->decimal('child_price', 10, 2)->nullable();
            $table->decimal('infant_price', 10, 2)->default(0);
            $table->json('addons')->nullable();              // [{key,label,sub,price,per}]

            // merchandising
            $table->string('badge')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('hotel_pickup')->default(false);
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            // content
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('highlights')->nullable();
            $table->text('overview')->nullable();
            $table->json('features')->nullable();            // [{icon,label}]
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('itinerary')->nullable();           // [{time,title,text}]
            $table->string('pickup_areas')->nullable();
            $table->string('pickup_time')->nullable();
            $table->json('good_to_know')->nullable();
            $table->json('cancellation')->nullable();
            $table->json('faqs')->nullable();                // [{q,a}]
            $table->string('group_type')->nullable();
            $table->string('languages')->nullable();

            // booking rules
            $table->json('time_slots')->nullable();          // tickets: ["10:00", ...]
            $table->unsignedSmallInteger('min_notice_days')->default(0);
            $table->unsignedInteger('daily_capacity')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
        });

        // Date / time-slot availability overrides (default = available)
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('time_slot', 10)->nullable();     // null = whole day
            $table->enum('status', ['available', 'limited', 'soldout'])->default('soldout');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
