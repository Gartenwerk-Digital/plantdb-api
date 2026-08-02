<x-mail::message>
# Your contribution was not accepted

Thank you for taking the time to contribute. After review, we decided not to merge this submission.

- **Reference:** `{{ $contribution->id }}`
- **Type:** {{ $contribution->type->value }}

**Reviewer notes:**

> {{ $contribution->review_notes }}

Feel free to submit an improved version.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
