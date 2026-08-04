<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('family_id')->constrained('families')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('common_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['family_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_translations');
    }
};
