<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "À faire aujourd'hui" tasks are generated client-side; this persists which
// ones have been completed (per farm, per day).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('done_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->string('task_key');
            $table->date('done_on');
            $table->timestamps();

            $table->unique(['farm_id', 'task_key', 'done_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('done_tasks');
    }
};
