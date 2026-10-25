<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global nutrient reference (code + display unit).
        Schema::create('nutrients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();  // em, mat, lysine, calcium, phosphore, cellulose
            $table->string('nom');
            $table->string('unit');             // kcal/kg, %
            $table->timestamps();
        });

        // Global target min/max per growth phase and nutrient.
        Schema::create('phase_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('phase'); // sevrage | demarrage | croissance | finition
            $table->foreignId('nutrient_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_value', 10, 3)->nullable();
            $table->decimal('max_value', 10, 3)->nullable();
            $table->timestamps();

            $table->unique(['phase', 'nutrient_id']);
        });

        // Farm-scoped ingredient library (prices vary per farm/region).
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->unsignedBigInteger('prix_kg'); // minor units of the farm currency, per kg
            $table->timestamps();
        });

        // Nutrient content per kg of an ingredient (same unit as the nutrient).
        Schema::create('ingredient_nutrient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nutrient_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 3);
            $table->timestamps();

            $table->unique(['ingredient_id', 'nutrient_id']);
        });

        // Saved formulas (also serve as calc history; mode = how it was built).
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('phase')->nullable();
            $table->string('mode')->default('manual'); // manual | lp
            $table->decimal('poids_total_kg', 8, 2)->nullable();
            $table->unsignedBigInteger('cost_per_kg')->nullable(); // minor units, computed
            $table->timestamps();
        });

        Schema::create('formula_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('percent', 6, 3); // incorporation %
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formula_lines');
        Schema::dropIfExists('formulas');
        Schema::dropIfExists('ingredient_nutrient');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('phase_requirements');
        Schema::dropIfExists('nutrients');
    }
};
