<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\CarbonInterface;
use Laravel\Sanctum\NewAccessToken;

final class CreateApiKey
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function __invoke(
        User $user,
        string $name,
        array $abilities,
        ?CarbonInterface $expiresAt = null,
    ): NewAccessToken {
        return $user->createToken($name, $abilities, $expiresAt);
    }
}
