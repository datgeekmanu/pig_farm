<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('seuil_rentabilite');
            $table->string('email')->nullable()->after('phone');
            $table->string('address')->nullable()->after('email');
            $table->text('payment_info')->nullable()->after('address'); // for receipts
            $table->string('receipt_color')->nullable()->after('payment_info');
            $table->string('logo_path')->nullable()->after('receipt_color');
            $table->json('notification_prefs')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email', 'address', 'payment_info', 'receipt_color', 'logo_path', 'notification_prefs']);
        });
    }
};
