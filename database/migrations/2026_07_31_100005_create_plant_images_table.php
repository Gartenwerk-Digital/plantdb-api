<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('url');
            $table->string('type');
            $table->string('license')->nullable();
            $table->string('attribution')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_images');
    }
};
