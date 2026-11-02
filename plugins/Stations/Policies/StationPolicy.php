<?php

namespace Plugins\Stations\Policies;

use App\Models\User;
use Plugins\Stations\Models\Station;

class StationPolicy
{
    /**
     * Determine whether the user can list stations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create stations.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the station.
     */
    public function view(User $user, Station $station): bool
    {
        return $station->user_id === $user->getKey();
    }

    /**
     * Determine whether the user can update the station.
     */
    public function update(User $user, Station $station): bool
    {
        return $this->view($user, $station);
    }

    /**
     * Determine whether the user can delete the station.
     */
    public function delete(User $user, Station $station): bool
    {
        return $this->view($user, $station);
    }
}
