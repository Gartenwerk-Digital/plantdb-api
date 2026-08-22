@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'plant' => null,
    'translation' => null,
])

@php
    $canonicalUrl = $canonical ?? url()->current();
    $ogImage = $image;

    if ($plant !== null && $ogImage === null) {
        $portrait = $plant->getFirstMediaUrl(\App\Enums\PlantImageType::Portrait->value, 'portrait');
        $ogImage = $portrait !== '' ? url($portrait) : null;
    }

    $locale = app()->getLocale();
    $ogLocale = match ($locale) {
        'de' => 'de_DE',
        'en' => 'en_US',
        default => $locale,
    };
@endphp

<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="PlantDB">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif

@if($plant !== null)
    @php
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Thing',
            'name' => $translation?->common_name ?: $plant->scientific_name,
            'alternateName' => $plant->scientific_name,
            'description' => $translation?->description,
            'image' => $ogImage,
            'url' => $canonicalUrl,
            'inLanguage' => $locale,
        ], static fn ($v): bool => $v !== null && $v !== '');
    @endphp
    <script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
