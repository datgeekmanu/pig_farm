<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Individually tracked pig (suivi individuel) — breeder or fattening animal.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identifiant');
            $table->string('nom')->nullable();
            $table->string('sexe')->nullable();        // male | female
            $table->string('categorie')->nullable();   // verrat | truie | engraissement | porcelet
            $table->date('date_naissance')->nullable();
            $table->date('date_arrivee')->nullable();
            $table->boolean('date_inconnue')->default(false);
            $table->decimal('poids_arrivee_kg', 6, 2);
            $table->unsignedBigInteger('prix_achat')->nullable();    // minor units
            $table->unsignedBigInteger('valeur_reforme')->nullable(); // minor units
            $table->string('provenance')->nullable();
            // parents: linked to a pig OR a free-text name when the parent isn't tracked
            $table->foreignId('pere_id')->nullable()->constrained('pigs')->nullOnDelete();
            $table->foreignId('mere_id')->nullable()->constrained('pigs')->nullOnDelete();
            $table->string('pere_nom')->nullable();
            $table->string('mere_nom')->nullable();
            $table->string('status')->default('active'); // active | sold | dead
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'identifiant']);
            $table->index(['farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pigs');
    }
};
