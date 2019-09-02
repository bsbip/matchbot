<?php

namespace App;

use App\EventPlayer;
use App\EventTeam;
use App\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
    ];

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
     * Teams relationship
     *
     * @return HasManyThrough
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class,
            EventTeam::class,
            'event_id',
            'id',
            'id',
            'team_id'
        );
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
