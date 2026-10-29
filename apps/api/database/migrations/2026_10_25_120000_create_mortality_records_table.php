<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mortalité — deaths, individual (pig_id) or batch losses (batch_id + count).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mortality_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pig_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('died_on');
            $table->string('cause')->nullable();
            $table->unsignedInteger('count')->default(1); // >1 for batch losses
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'batch_id', 'died_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mortality_records');
    }
};
