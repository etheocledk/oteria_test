<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    /**
     * Administrators can do everything on incidents.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incident $incident): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Analysts may only edit incidents they reported or are assigned to.
     */
    public function update(User $user, Incident $incident): bool
    {
        return $incident->reporter_id === $user->id
            || $incident->assignee_id === $user->id;
    }

    public function comment(User $user, Incident $incident): bool
    {
        return true;
    }

    public function assign(User $user, Incident $incident): bool
    {
        return false;
    }

    public function delete(User $user, Incident $incident): bool
    {
        return false;
    }
}
