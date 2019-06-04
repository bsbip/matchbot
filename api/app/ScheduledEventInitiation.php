<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduledEventInitiation extends Model
{
    protected $fillable = [
        'cron_expression',
        'channel_id',
        'expire_at',
    ];
}
