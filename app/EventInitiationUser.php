<?php

namespace App;

use App\Player;
use App\EventInitiation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInitiationUser extends Model
{
    protected $fillable = [
        'user_id',
        'participate',
        'event_initiation_id',
    ];

    /**
     * Event initiation relationship
     *
     * @return BelongsTo
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function eventInitiation(): BelongsTo
    {
        return $this->belongsTo(EventInitiation::class, 'event_initiation_id');
    }

    /**
     * Player relationship
     *
     * @return BelongsTo
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'user_id');
    }
}
