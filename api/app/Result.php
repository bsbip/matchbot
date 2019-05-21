<?php

namespace App;

use App\Team;
use App\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    /**
     * Team relationship
     *
     * @return BelongsTo
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
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
}
