<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Traitements — batch, individual, or prophylaxis (scheduled) health records.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('scope'); // lot | individual | prophylaxie
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pig_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('produit');
            $table->string('dose')->nullable();
            $table->string('type')->nullable();   // individual: vaccin | antibiotique | vermifuge …
            $table->string('motif')->nullable();
            $table->string('soignant')->nullable();
            $table->string('soignant_tel')->nullable();
            $table->unsignedBigInteger('cout')->nullable(); // minor units
            $table->date('date_rappel')->nullable();
            // optional link to a veterinary stock item consumed by this treatment
            $table->foreignId('stock_item_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantite_utilisee', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'scope', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
