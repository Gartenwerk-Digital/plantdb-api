<x-mail::message>
# Your contribution has been approved

Thank you! Your submission has been reviewed and merged into the plant database.

- **Reference:** `{{ $contribution->id }}`
- **Type:** {{ $contribution->type->value }}

Thanks for helping grow PlantDB,<br>
{{ config('app.name') }}
</x-mail::message>
