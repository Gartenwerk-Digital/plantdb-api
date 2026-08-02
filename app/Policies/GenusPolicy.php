<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Genus;
use App\Models\User;

final class GenusPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(): bool
    {
        return true;
    }

    public function delete(User $user, Genus $genus): bool
    {
        return ! $genus->plants()->exists();
    }

    public function deleteAny(): bool
    {
        return true;
    }
}
