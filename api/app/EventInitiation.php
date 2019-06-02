<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventInitiation extends Model
{
    protected $fillable = [
        'expire_at',
        'message_ts',
        'user_id',
    ];

    /**
     * Event initiation users relationship
     *
     * @return HasMany
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function users(): HasMany
    {
        return $this->hasMany(EventInitiationUser::class, 'event_initiation_id', 'id');
    }
}
