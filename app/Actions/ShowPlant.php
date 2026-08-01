<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Plant;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

final class ShowPlant
{
    public function __invoke(string $slug): Plant
    {
        $query = Plant::query()
            ->where('status', PlantStatus::Approved->value)
            ->where('slug', $slug);

        /** @var Plant $plant */
        $plant = QueryBuilder::for($query)
            ->allowedIncludes(
                AllowedInclude::relationship('images'),
                AllowedInclude::relationship('companions'),
                AllowedInclude::relationship('care_tasks', 'careTasks'),
                AllowedInclude::relationship('translations'),
            )
            ->firstOrFail();

        return $plant;
    }
}
