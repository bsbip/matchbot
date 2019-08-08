<?php

namespace App\Jobs;

use App\EventInitiation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job for creating a match
 */
class CreateMatchFromInitiation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $initiationId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($initiationId)
    {
        $this->initiationId = $initiationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventInitiation = EventInitiation::whereNull('event_id')
            ->where(function ($query) {
                $query->where('start_when_possible', false)
                    ->where('expire_at', '<=', now()->toDateTimeString())
                    ->whereNotNull('expire_at');
            })
            ->orWhere(function ($query) {
                $query->where('start_when_possible', true)
                    ->whereHas('users', function ($query) {
                        $query->where('participate', true);
                    }, '>=', Config::get('match.min_users'));
            })
            ->with(['users' => function ($query) {
                $query->where('participate', true);
            }])
            ->find($this->initiationId);

        if (!$eventInitiation) {
            return;
        }

        $users = getUsersBySlackId($eventInitiation->users->pluck('user_id')->toArray());

        createMatch($users, true, $eventInitiation);

        return;
    }
}
