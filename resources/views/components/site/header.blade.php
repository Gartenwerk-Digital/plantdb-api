@php
    $navLinks = [
        ['href' => '/developers', 'label' => 'Entwickler'],
        ['href' => '/contribute', 'label' => 'Mitmachen'],
        ['href' => '/about',      'label' => 'Über uns'],
    ];
@endphp

<header {{ $attributes->merge(['class' => 'bg-background/90 backdrop-blur border-b border-border sticky top-0 z-40']) }}>
    <div class="mx-auto flex items-center justify-between gap-6 px-6 py-4" style="max-width: var(--container-max)">
        <a href="/" class="font-serif text-2xl font-semibold text-foreground shrink-0">
            PlantDB
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-1 text-sm font-medium" aria-label="Hauptnavigation">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}"
                   class="px-3 py-2 rounded-[var(--radius)] text-foreground/80 hover:text-foreground hover:bg-muted transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="/docs/api"
               class="ml-2 inline-flex items-center gap-1 px-3 py-2 rounded-[var(--radius)] bg-primary text-primary-foreground hover:bg-[var(--forest-600)] transition-colors">
                API-Docs
            </a>
        </nav>

        {{-- Mobile menu (zero-JS via <details>) --}}
        <details class="md:hidden relative [&[open]>summary_.icon-open]:hidden [&:not([open])>summary_.icon-close]:hidden">
            <summary class="list-none [&::-webkit-details-marker]:hidden inline-flex items-center justify-center w-10 h-10 rounded-[var(--radius)] text-foreground hover:bg-muted transition-colors cursor-pointer"
                     aria-label="Menü öffnen">
                <svg class="icon-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg class="icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </summary>

            <nav class="absolute right-0 mt-2 w-56 rounded-[var(--radius-lg)] border border-border bg-card p-2 flex flex-col text-sm font-medium"
                 style="box-shadow: var(--shadow-lg)"
                 aria-label="Hauptnavigation mobil">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}"
                       class="px-3 py-2 rounded-[var(--radius)] text-foreground/90 hover:bg-muted transition-colors">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="/docs/api"
                   class="mt-1 px-3 py-2 rounded-[var(--radius)] bg-primary text-primary-foreground hover:bg-[var(--forest-600)] transition-colors">
                    API-Docs
                </a>
            </nav>
        </details>
    </div>
</header>
