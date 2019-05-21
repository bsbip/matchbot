<?php

namespace App;

use App\Result;
use App\EventTeam;
use App\EventPlayer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
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
        return $this->hasMany(EventTeam::class, 'event_id', 'id');
    }

    /**
     * Results relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'event_id');
    }

    /**
     * Result relationship
     *
     * @return HasOne
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function result(): HasOne
    {
        return $this->hasOne(Result::class, 'event_id');
    }

    /**
     * Event players relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function eventPlayers(): HasMany
    {
        return $this->hasMany(EventPlayer::class, 'event_id', 'id');
    }
}
