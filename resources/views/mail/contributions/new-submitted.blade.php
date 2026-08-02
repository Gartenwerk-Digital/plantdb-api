<x-mail::message>
# New Contribution Submitted

A new community contribution is waiting for review.

- **ID:** {{ $contribution->id }}
- **Type:** {{ $contribution->type->value }}
- **Plant ID:** {{ $contribution->plant_id ?? '—' }}
- **Submitted by:** {{ optional($contribution->submitter)->email ?? 'unknown' }}

<x-mail::button :url="$adminUrl">
Review in Admin
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
