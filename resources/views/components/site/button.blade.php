@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center gap-2 px-4 py-2 rounded-[var(--radius)] font-sans text-sm font-medium transition-colors';
    $variantClass = match ($variant) {
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-[var(--sand-300)]',
        'ghost' => 'text-foreground hover:bg-muted',
        default => 'bg-primary text-primary-foreground hover:bg-[var(--forest-600)]',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$variantClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$variantClass]) }}>
        {{ $slot }}
    </button>
@endif
