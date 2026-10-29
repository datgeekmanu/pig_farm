<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lot-level weigh-in session (field app weighs a sample of the lot, not
        // individual pigs). Stores the session average + uniformity.
        Schema::create('lot_weighings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->date('weighed_on');
            $table->unsignedInteger('count')->nullable();   // animals weighed
            $table->decimal('avg_weight_kg', 6, 2);
            $table->decimal('uniformity_cv', 5, 2)->nullable();
            $table->json('weights')->nullable();            // raw sample, when provided
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'batch_id', 'weighed_on']);
        });

        // Feed given to a lot — the basis for feed conversion (FCR / IC).
        Schema::create('feed_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_item_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('quantity_kg', 12, 2);
            $table->unsignedBigInteger('cost')->nullable(); // minor units
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'batch_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_intakes');
        Schema::dropIfExists('lot_weighings');
    }
};
