<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ventes — sales, with one or more line items.
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->date('date_vente');
            $table->string('acheteur')->nullable();
            $table->string('acheteur_tel')->nullable();
            $table->string('acheteur_email')->nullable();
            $table->string('mode_paiement')->nullable();   // especes | mobile_money | virement | cheque
            $table->string('statut_paiement')->default('paye'); // paye | partiel | impaye
            $table->unsignedBigInteger('total')->default(0); // minor units, computed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'date_vente']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pig_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type_porc')->nullable();
            $table->unsignedInteger('quantite')->default(1);
            $table->decimal('poids_total_kg', 10, 2)->nullable();
            $table->unsignedBigInteger('prix_kg')->nullable(); // minor units
            $table->unsignedInteger('age_mois')->nullable();
            $table->decimal('remise_pct', 5, 2)->default(0);
            $table->decimal('taxe_pct', 5, 2)->default(0);
            $table->unsignedBigInteger('line_total')->default(0); // minor units, computed
            $table->timestamps();
        });

        // Dépenses — expenses.
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('montant'); // minor units
            $table->string('categorie');
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'date']);
            $table->index(['farm_id', 'categorie']);
        });

        // Salaires — payroll.
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('nom_ouvrier');
            $table->string('poste')->nullable();
            $table->unsignedBigInteger('montant'); // minor units
            $table->date('date_paiement');
            $table->string('mois_concerne'); // YYYY-MM
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'mois_concerne']);
        });

        // Seuil de rentabilité (break-even target) — set in farm settings (EPIC 7).
        Schema::table('farms', function (Blueprint $table) {
            $table->unsignedBigInteger('seuil_rentabilite')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('farms', fn (Blueprint $t) => $t->dropColumn('seuil_rentabilite'));
        Schema::dropIfExists('salaries');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
