<?php

namespace App;

use App\Team;
use App\Event;
use App\Player;
use App\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EventTeam extends Model
{
    /**
     * Result relationship
     *
     * @return HasOne
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function result(): HasOne
    {
        return $this->hasOne(Result::class, 'event_team_id', 'id')
            ->where('deleted', 0);
    }

    /**
     * Team relationship
     *
     * @return HasOne
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function team(): HasOne
    {
        return $this->hasOne(Team::class, 'id', 'team_id');
    }

    /**
     * Event relationship
     *
     * @return BelongsTo
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
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
        return $this->belongsToMany(Player::class, 'team_players', 'team_id', 'player_id', 'team_id', 'id');
    }
}
