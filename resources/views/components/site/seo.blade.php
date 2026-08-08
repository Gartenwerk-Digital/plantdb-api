@props([
    'plant',
    'translation' => null,
])

@php
    $portrait = $plant->getFirstMediaUrl(\App\Enums\PlantImageType::Portrait->value, 'portrait');

    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Thing',
        'name' => $translation?->common_name ?: $plant->scientific_name,
        'alternateName' => $plant->scientific_name,
        'description' => $translation?->description,
        'image' => $portrait !== '' ? url($portrait) : null,
        'url' => route('site.plants.show', $plant->slug),
        'inLanguage' => app()->getLocale(),
    ], static fn ($v): bool => $v !== null && $v !== '');
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
