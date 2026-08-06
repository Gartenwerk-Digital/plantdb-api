<header {{ $attributes->merge(['class' => 'bg-background border-b border-border']) }}>
    <div class="mx-auto flex items-center justify-between px-6 py-4" style="max-width: var(--container-max)">
        <a href="/" class="font-serif text-2xl font-semibold text-foreground">
            PlantDB
        </a>

        <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
            <a href="/plants" class="text-foreground/80 hover:text-foreground transition-colors">Pflanzen</a>
            <a href="/docs/api" class="text-foreground/80 hover:text-foreground transition-colors">API</a>
            <a href="/about" class="text-foreground/80 hover:text-foreground transition-colors">Über uns</a>
        </nav>

        <button type="button" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-[var(--radius)] text-foreground hover:bg-muted transition-colors" aria-label="Menü">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>
</header>
