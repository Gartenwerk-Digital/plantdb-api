<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\TokenAbility;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property array<int, string> $abilities
 * @property string|null $expires_at
 */
final class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(TokenAbility::values())],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
