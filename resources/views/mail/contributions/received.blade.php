<x-mail::message>
# Thanks for your contribution!

We received your submission and it is now pending review by our moderators.

- **Reference:** `{{ $contribution->id }}`
- **Type:** {{ $contribution->type->value }}

You'll receive another email once the review is complete.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
