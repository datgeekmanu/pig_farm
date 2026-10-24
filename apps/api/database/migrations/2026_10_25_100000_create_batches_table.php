<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lot / bande d'engraissement — a fattening batch of pigs.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('batiment_loge')->nullable();
            $table->date('date_mise_en_lot');
            $table->unsignedInteger('effectif_initial');
            $table->date('date_naissance')->nullable();
            $table->decimal('poids_moyen_initial_kg', 6, 2)->nullable();
            // money in minor units of the farm currency (XOF = whole francs, EUR = cents)
            $table->unsignedBigInteger('prix_achat_unitaire')->nullable();
            $table->string('status')->default('active'); // active | sold | closed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
