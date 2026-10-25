<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock items — feed (alimentation) and veterinary supplies.
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // alimentation | veterinaire
            $table->string('nom');
            $table->string('categorie')->nullable();
            $table->decimal('quantite', 12, 2)->default(0);
            $table->string('unite')->nullable();
            $table->decimal('seuil_alerte', 12, 2)->nullable();
            $table->unsignedBigInteger('prix_unitaire')->nullable(); // minor units
            $table->date('date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'type']);
        });

        // Append-only ledger of stock changes (+ in / - out).
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('change', 12, 2);
            $table->string('reason')->nullable(); // achat | traitement | perte | ajustement
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_items');
    }
};
