<?php

namespace App;

use App\EventTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamPlayer extends Model
{
    /**
     * Event teams relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function eventTeams(): HasMany
    {
        return $this->hasMany(EventTeam::class, 'team_id', 'team_id');
    }

    /**
     * Players relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function players(): HasMany
    {
        return $this->hasMany('App\Player', 'id', 'player_id');
    }
}
