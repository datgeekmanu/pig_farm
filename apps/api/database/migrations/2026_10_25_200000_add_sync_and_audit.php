<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Client-generated id for offline-created records (idempotent sync).
        Schema::table('weight_records', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->after('farm_id');
            $table->unique(['farm_id', 'client_uuid']);
        });
        Schema::table('mortality_records', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->after('farm_id');
            $table->unique(['farm_id', 'client_uuid']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action'); // created | updated | deleted
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['farm_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::table('weight_records', function (Blueprint $table) {
            $table->dropUnique(['farm_id', 'client_uuid']);
            $table->dropColumn('client_uuid');
        });
        Schema::table('mortality_records', function (Blueprint $table) {
            $table->dropUnique(['farm_id', 'client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
