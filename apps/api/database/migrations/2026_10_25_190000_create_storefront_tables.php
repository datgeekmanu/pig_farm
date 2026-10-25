<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products — a farm's storefront listings (live pigs or pork cuts).
        // Not globally farm-scoped: the public catalog reads across farms.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // live_pig | pork_cut
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('unit')->default('tete'); // tete | kg
            $table->unsignedBigInteger('prix'); // minor units, per unit
            $table->decimal('stock_qty', 12, 2)->default(0);
            $table->boolean('is_published')->default(false);
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'is_published']);
        });

        // Buyers — storefront customers (separate auth from farm users).
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();   // seller
            $table->foreignId('buyer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete(); // herd/finance link
            $table->string('status')->default('placed'); // placed|confirmed|ready|delivered|cancelled
            $table->unsignedBigInteger('total')->default(0);
            $table->string('delivery_address')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index(['buyer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom');            // snapshot
            $table->string('unit');
            $table->unsignedBigInteger('prix'); // snapshot, minor units
            $table->decimal('quantity', 12, 2);
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method')->nullable(); // mobile_money | card | cash
            $table->string('status')->default('pending'); // pending | paid | failed
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('products');
    }
};
