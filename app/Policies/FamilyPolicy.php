<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

final class FamilyPolicy
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

    public function delete(User $user, Family $family): bool
    {
        return ! $family->plants()->exists();
    }

    public function deleteAny(): bool
    {
        return true;
    }
}
