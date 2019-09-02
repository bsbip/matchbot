<?php

namespace App;

use App\EventInitiation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInitiationScheduledMessage extends Model
{
    protected $fillable = [
        'scheduled_message_id',
        'channel_id',
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
}
