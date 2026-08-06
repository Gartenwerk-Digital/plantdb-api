<div {{ $attributes->merge(['class' => 'bg-card text-card-foreground rounded-[var(--radius-lg)] border border-border p-6']) }} style="box-shadow: var(--shadow-md)">
    {{ $slot }}
</div>
