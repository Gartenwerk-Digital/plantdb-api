@php
    use Illuminate\Support\Str;

    $displayName = $translation?->common_name ?: $plant->scientific_name;
    $portraits = $plant->getMedia(\App\Enums\PlantImageType::Portrait->value);

    $careFields = array_filter([
        'Sonne' => $plant->sun_requirement?->value,
        'Bewässerung' => $plant->watering_frequency?->value,
        'Düngung' => $plant->fertilizing_frequency?->value,
        'Pflege' => $plant->maintenance_level?->value,
        'Bodenfeuchte' => $plant->soil_moisture?->value,
        'Winterhärte' => $plant->hardiness_zone_min && $plant->hardiness_zone_max
            ? sprintf('Zone %d–%d', $plant->hardiness_zone_min, $plant->hardiness_zone_max)
            : null,
    ]);
@endphp

@extends('site.layouts.base')

@section('title', $displayName)
@section('description', Str::limit($translation?->description ?: $plant->scientific_name.' — Pflanzendaten aus der PlantDB.', 160))

@section('content')

<article class="space-y-16">

    {{-- Hero --}}
    <header class="space-y-4">
        <div class="flex flex-wrap gap-2">
            @if($plant->family?->name)
                <x-site.badge>{{ $plant->family->name }}</x-site.badge>
            @endif
            @if($plant->genus?->name)
                <x-site.badge variant="muted">{{ $plant->genus->name }}</x-site.badge>
            @endif
            @if($plant->life_cycle)
                <x-site.badge variant="accent">{{ Str::headline($plant->life_cycle->value) }}</x-site.badge>
            @endif
        </div>
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight">{{ $displayName }}</h1>
        <p class="text-lg text-muted-foreground font-serif italic">{{ $plant->scientific_name }}@if($plant->cultivar) ‘{{ $plant->cultivar }}’@endif</p>
    </header>

    {{-- Bildergalerie --}}
    @if($portraits->isNotEmpty())
    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($portraits as $media)
            @php
                $attribution = $media->getCustomProperty('attribution');
            @endphp
            <figure class="rounded-[var(--radius-lg)] overflow-hidden border border-border bg-card">
                <img src="{{ $media->getUrl('portrait') }}" alt="{{ $displayName }}" loading="lazy" class="w-full h-auto block" />
                @if($attribution)
                    <figcaption class="px-3 py-2 text-xs text-muted-foreground">{{ $attribution }}</figcaption>
                @endif
            </figure>
        @endforeach
    </section>
    @endif

    {{-- Beschreibung --}}
    @if($translation?->description)
    <section class="max-w-3xl">
        <h2 class="text-2xl font-bold mb-4">Beschreibung</h2>
        <p class="text-base leading-relaxed text-foreground/90">{{ $translation->description }}</p>
    </section>
    @endif

    {{-- Pflege-Daten --}}
    @if(count($careFields) > 0)
    <section>
        <h2 class="text-2xl font-bold mb-6">Pflege &amp; Standort</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($careFields as $label => $value)
                <x-site.card class="!p-4">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ $label }}</p>
                    <p class="mt-1 text-base font-medium">{{ Str::headline((string) $value) }}</p>
                </x-site.card>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Care Tasks --}}
    @if($plant->careTasks->isNotEmpty())
    <section>
        <h2 class="text-2xl font-bold mb-6">Pflege-Kalender</h2>
        <ul class="divide-y divide-border border border-border rounded-[var(--radius-lg)] bg-card">
            @foreach($plant->careTasks as $task)
                <li class="flex flex-wrap items-baseline justify-between gap-2 px-4 py-3">
                    <span class="font-medium">{{ Str::headline($task->task_type->value) }}</span>
                    <span class="text-sm text-muted-foreground">
                        Monat {{ $task->month_start }}–{{ $task->month_end }}@if($task->frequency) · {{ $task->frequency }}@endif
                    </span>
                </li>
            @endforeach
        </ul>
    </section>
    @endif

    {{-- Mehrsprachige Namen --}}
    @if($plant->translations->isNotEmpty())
    <section>
        <h2 class="text-2xl font-bold mb-6">Mehrsprachige Namen</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 max-w-2xl">
            @foreach($plant->translations as $t)
                @if($t->common_name)
                    <div class="flex items-baseline gap-3">
                        <dt class="w-12 text-xs uppercase tracking-wide text-muted-foreground">{{ $t->locale }}</dt>
                        <dd class="font-medium">{{ $t->common_name }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>
    @endif

</article>

<x-site.seo :plant="$plant" :translation="$translation" />

@endsection
