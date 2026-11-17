<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tour booking requests (no online payment)
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 12)->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tour_title');
            $table->date('date')->index();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('infants')->default(0);
            $table->json('addons')->nullable();
            $table->decimal('estimated_total', 10, 2)->default(0);
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('hotel')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'confirmed', 'completed', 'cancelled'])->default('new')->index();
            $table->string('pickup_time')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // Ticket orders (paid online via N-Genius)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 12)->unique();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending')->index();
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->text('notes')->nullable();
            $table->string('payment_provider')->default('ngenius');
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('tickets_sent')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('date');
            $table->string('time_slot', 10)->nullable();
            $table->unsignedSmallInteger('adults')->default(0);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('infants')->default(0);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('enquiries');
    }
};
