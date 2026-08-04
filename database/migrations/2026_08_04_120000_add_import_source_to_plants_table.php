<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table): void {
            $table->string('import_source')->nullable()->index()->after('review_notes');
            $table->string('source_key')->nullable()->index()->after('import_source');
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table): void {
            $table->dropIndex(['source_key']);
            $table->dropIndex(['import_source']);
            $table->dropColumn(['import_source', 'source_key']);
        });
    }
};
