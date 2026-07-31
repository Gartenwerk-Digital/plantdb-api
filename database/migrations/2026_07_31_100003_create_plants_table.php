<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('scientific_name');
            $table->foreignUuid('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignUuid('genus_id')->constrained('genera')->cascadeOnDelete();
            $table->string('cultivar')->nullable();

            $table->string('life_cycle')->nullable();
            $table->boolean('deciduous')->default(false);
            $table->jsonb('native_regions')->nullable();

            $table->unsignedSmallInteger('height_min_cm')->nullable();
            $table->unsignedSmallInteger('height_max_cm')->nullable();
            $table->unsignedSmallInteger('width_min_cm')->nullable();
            $table->unsignedSmallInteger('width_max_cm')->nullable();
            $table->string('growth_rate')->nullable();
            $table->string('root_depth')->nullable();

            $table->string('sun_requirement')->nullable();
            $table->unsignedTinyInteger('hardiness_zone_min')->nullable();
            $table->unsignedTinyInteger('hardiness_zone_max')->nullable();
            $table->boolean('suitable_for_pot')->default(false);

            $table->jsonb('soil_types')->nullable();
            $table->decimal('soil_ph_min', 3, 1)->nullable();
            $table->decimal('soil_ph_max', 3, 1)->nullable();
            $table->string('soil_moisture')->nullable();

            $table->unsignedTinyInteger('bloom_start_month')->nullable();
            $table->unsignedTinyInteger('bloom_end_month')->nullable();
            $table->jsonb('bloom_colors')->nullable();
            $table->boolean('fragrant')->default(false);

            $table->unsignedTinyInteger('fruit_season_start')->nullable();
            $table->unsignedTinyInteger('fruit_season_end')->nullable();
            $table->jsonb('edible_parts')->nullable();
            $table->text('harvest_notes')->nullable();

            $table->string('watering_frequency')->nullable();
            $table->string('fertilizing_frequency')->nullable();
            $table->boolean('pruning_required')->default(false);
            $table->string('maintenance_level')->nullable();

            $table->boolean('toxic_to_humans')->default(false);
            $table->boolean('toxic_to_pets')->default(false);
            $table->boolean('toxic_to_livestock')->default(false);
            $table->boolean('invasive')->default(false);
            $table->string('allergy_potential')->nullable();
            $table->boolean('attracts_bees')->default(false);
            $table->boolean('attracts_butterflies')->default(false);
            $table->boolean('deer_resistant')->default(false);

            $table->jsonb('propagation_methods')->nullable();

            $table->string('status')->default('draft')->index();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE plants
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('simple',
                    coalesce(scientific_name, '') || ' ' ||
                    coalesce(cultivar, '') || ' ' ||
                    coalesce(slug, '')
                )
            ) STORED
        SQL);

        DB::statement('CREATE INDEX plants_search_vector_gin ON plants USING GIN (search_vector)');
        DB::statement('CREATE INDEX plants_native_regions_gin ON plants USING GIN (native_regions)');
        DB::statement('CREATE INDEX plants_soil_types_gin ON plants USING GIN (soil_types)');
        DB::statement('CREATE INDEX plants_bloom_colors_gin ON plants USING GIN (bloom_colors)');
        DB::statement('CREATE INDEX plants_edible_parts_gin ON plants USING GIN (edible_parts)');
        DB::statement('CREATE INDEX plants_propagation_methods_gin ON plants USING GIN (propagation_methods)');
    }

    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
