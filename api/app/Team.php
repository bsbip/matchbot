<?php

namespace App;

use App\Player;
use App\Result;
use App\EventTeam;
use App\TeamPlayer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
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
        return $this->hasMany(EventTeam::class, 'team_id');
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
        return $this->hasMany(Result::class, 'team_id');
    }

    /**
     * Team players relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function teamPlayers(): HasMany
    {
        return $this->hasMany(TeamPlayer::class, 'team_id', 'id');
    }

    /**
     * Players relationship
     *
     * @return BelongsToMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'team_players', 'team_id', 'player_id');
    }
}
