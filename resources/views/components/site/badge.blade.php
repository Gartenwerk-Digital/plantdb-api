@props(['variant' => 'default'])

@php
    $base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
    $variantClass = match ($variant) {
        'accent' => 'bg-accent/10 text-accent',
        'muted' => 'bg-muted text-muted-foreground',
        default => 'bg-primary/10 text-primary',
    };
@endphp

<span {{ $attributes->merge(['class' => $base.' '.$variantClass]) }}>
    {{ $slot }}
</span>
