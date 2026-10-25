<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conduite en bandes — batching of sows for planned production.
        Schema::create('bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->unsignedInteger('taille_objectif')->nullable(); // target sow count
            $table->date('date_prevue_saillie')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Saillie / gestation — one breeding cycle for a sow.
        Schema::create('breedings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sow_id')->constrained('pigs')->cascadeOnDelete();
            $table->foreignId('boar_id')->nullable()->constrained('pigs')->nullOnDelete();
            $table->foreignId('band_id')->nullable()->constrained()->nullOnDelete();
            $table->date('bred_on');                 // date de saillie
            $table->string('method')->nullable();    // naturelle | insemination
            $table->date('expected_farrow_on');      // predicted mise bas (bred_on + gestation)
            $table->date('ultrasound_on')->nullable();
            $table->string('ultrasound_result')->nullable(); // gestante | vide
            $table->date('heat_return_on')->nullable();      // retour en chaleur
            $table->string('status')->default('saillie'); // saillie | gestante | vide | mise_bas
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
        });

        // Mise bas — farrowing result (a litter).
        Schema::create('farrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('breeding_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sow_id')->constrained('pigs')->cascadeOnDelete();
            $table->date('farrowed_on');
            $table->unsignedInteger('born_alive')->default(0);  // nés vivants
            $table->unsignedInteger('born_dead')->default(0);   // mort-nés
            $table->unsignedInteger('mummies')->default(0);     // momifiés
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'farrowed_on']);
        });

        // Sevrage — weaning of a litter.
        Schema::create('weanings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('farrowing_id')->constrained()->cascadeOnDelete();
            $table->date('weaned_on');
            $table->unsignedInteger('weaned_count')->default(0);
            $table->decimal('avg_weight_kg', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weanings');
        Schema::dropIfExists('farrowings');
        Schema::dropIfExists('breedings');
        Schema::dropIfExists('bands');
    }
};
