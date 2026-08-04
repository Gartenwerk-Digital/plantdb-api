<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plant_translations', function (Blueprint $table): void {
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::table('plant_translations', function (Blueprint $table): void {
            $table->dropIndex(['locale']);
        });
    }
};
