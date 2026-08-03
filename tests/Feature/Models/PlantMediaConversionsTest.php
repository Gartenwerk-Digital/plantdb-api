<?php

declare(strict_types=1);

use App\Enums\PlantImageType;
use App\Models\Plant;

it('registers a media collection per PlantImageType', function (): void {
    $plant = new Plant;
    $plant->registerMediaCollections();

    $registered = collect($plant->mediaCollections)->pluck('name')->all();

    foreach (PlantImageType::cases() as $type) {
        expect($registered)->toContain($type->value);
    }
});

it('registers portrait and thumb conversions', function (): void {
    $plant = new Plant;
    $plant->registerMediaConversions();

    $conversions = collect($plant->mediaConversions)->map(fn ($c): string => $c->getName())->all();

    expect($conversions)->toContain('portrait')
        ->and($conversions)->toContain('thumb');
});
