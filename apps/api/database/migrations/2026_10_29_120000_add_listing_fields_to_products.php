<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A product doubles as a lot "listing" for the field app's sell screen.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
            $table->unsignedBigInteger('market_price')->nullable()->after('prix'); // reference market price/kg
            $table->boolean('visible')->default(true)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['category', 'market_price', 'visible']));
    }
};
