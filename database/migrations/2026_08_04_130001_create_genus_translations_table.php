<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genus_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('genus_id')->constrained('genera')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('common_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['genus_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genus_translations');
    }
};
