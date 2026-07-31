<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_care_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('task_type');
            $table->unsignedTinyInteger('month_start');
            $table->unsignedTinyInteger('month_end');
            $table->string('frequency')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_care_tasks');
    }
};
