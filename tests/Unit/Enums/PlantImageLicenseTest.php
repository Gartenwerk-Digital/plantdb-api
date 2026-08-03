<?php

declare(strict_types=1);

use App\Enums\PlantImageLicense;

it('has a non-empty label for every case', function (PlantImageLicense $license): void {
    expect($license->label())->not->toBe('');
})->with(PlantImageLicense::cases());

it('marks CC0 and Public Domain as not requiring attribution', function (): void {
    expect(PlantImageLicense::Cc0->requiresAttribution())->toBeFalse()
        ->and(PlantImageLicense::PublicDomain->requiresAttribution())->toBeFalse();
});

it('marks all other licenses as requiring attribution', function (): void {
    $requiring = collect(PlantImageLicense::cases())
        ->filter(fn (PlantImageLicense $l): bool => $l->requiresAttribution())
        ->map(fn (PlantImageLicense $l): string => $l->value)
        ->all();

    expect($requiring)->toContain(
        PlantImageLicense::CcBy->value,
        PlantImageLicense::CcBySa->value,
        PlantImageLicense::CcByNc->value,
        PlantImageLicense::CcByNd->value,
        PlantImageLicense::AllRightsReserved->value,
    );
});

it('exposes a value=>label options array with all cases', function (): void {
    $options = PlantImageLicense::options();

    expect($options)->toHaveCount(count(PlantImageLicense::cases()))
        ->and($options[PlantImageLicense::CcBy->value])->toContain('CC BY 4.0');
});
