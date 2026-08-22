<footer {{ $attributes->merge(['class' => 'border-t border-border text-muted-foreground text-sm mt-24']) }}>
    <div class="mx-auto flex flex-col md:flex-row items-center justify-between gap-4 px-6 py-8" style="max-width: var(--container-max)">
        <p>&copy; {{ date('Y') }} PlantDB — Community-gepflegte Pflanzendatenbank.</p>
        <nav class="flex items-center gap-6">
            <a href="{{ route('site.impressum') }}" class="hover:text-foreground transition-colors">Impressum</a>
            <a href="{{ route('site.datenschutz') }}" class="hover:text-foreground transition-colors">Datenschutz</a>
        </nav>
    </div>
</footer>
