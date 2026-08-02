<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RevokeApiKey
{
    public function __invoke(User $user, int $tokenId): void
    {
        $deleted = $user->tokens()->whereKey($tokenId)->delete();

        throw_if($deleted === 0, ModelNotFoundException::class);
    }
}
