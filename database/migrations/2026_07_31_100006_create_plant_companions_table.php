<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_companions', function (Blueprint $table): void {
            $table->foreignUuid('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignUuid('companion_id')->constrained('plants')->cascadeOnDelete();
            $table->string('relationship');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->primary(['plant_id', 'companion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_companions');
    }
};
