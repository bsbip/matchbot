<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Http\Request;
use App\Http\Requests;

/**
 * Job for creating a match
 */
class CreateMatch implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $text;
    protected $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($text, $users = [])
    {
        $this->text = $text;
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->users)) {
            $activeUsers = $this->users;
        } else {
            $activeUsers = getActiveUsers($this->text);
        }

        return createMatch($activeUsers);
    }
}
