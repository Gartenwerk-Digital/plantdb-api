<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CareTaskType;
use App\Models\Plant;
use App\Models\PlantCareTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantCareTask>
 */
final class PlantCareTaskFactory extends Factory
{
    protected $model = PlantCareTask::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'task_type' => CareTaskType::Watering->value,
            'month_start' => 4,
            'month_end' => 9,
            'frequency' => 'wöchentlich',
            'notes' => null,
        ];
    }
}
