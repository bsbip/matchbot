<?php

namespace App;

use App\TeamPlayer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
