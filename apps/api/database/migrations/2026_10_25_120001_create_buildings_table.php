<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Bâtiments — housing, used for density (space per animal) and comfort.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->decimal('surface_m2', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('building_id')->nullable()->after('farm_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('building_id');
        });
        Schema::dropIfExists('buildings');
    }
};
