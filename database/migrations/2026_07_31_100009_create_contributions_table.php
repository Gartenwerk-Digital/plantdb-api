<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->string('type');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->jsonb('payload');
            $table->string('status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
