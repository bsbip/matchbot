<?php

namespace App;

use App\TeamPlayer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Player extends Model
{
    /**
     * Team players relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function teamPlayers(): HasMany
    {
        return $this->hasMany(TeamPlayer::class, 'player_id', 'id');
    }

    /**
     * Team relationship
     *
     * @return HasManyThrough
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(
            Team::class,
            TeamPlayer::class,
            'player_id',
            'id',
            'id',
            'team_id'
        );
    }

    /**
     * results relationship
     *
     * @return HasManyThrough
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(
            Result::class,
            TeamPlayer::class,
            'player_id',
            'team_id',
            'id',
            'team_id'
        );
    }
}
