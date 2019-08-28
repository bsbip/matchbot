<?php

namespace App;

use App\Event;
use App\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'score',
        'crawl_score',
        'team_id',
        'event_id',
        'note',
        'event_team_id',
        'deleted',
    ];

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
