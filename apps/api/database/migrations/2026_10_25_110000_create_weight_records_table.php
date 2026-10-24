<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pesées — individual pig weigh-ins. Batch stats + growth curves aggregate these.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pig_id')->constrained()->cascadeOnDelete();
            // denormalised from the pig at creation, for fast batch grouping
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('weight_kg', 6, 2);
            $table->date('weighed_on');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'pig_id', 'weighed_on']);
            $table->index(['farm_id', 'batch_id', 'weighed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_records');
    }
};
