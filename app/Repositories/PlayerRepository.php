<?php

namespace App\Repositories;

use App\Player;
use App\Services\SlackService;
use Illuminate\Support\Collection;

class PlayerRepository
{
    /**
     * @var SlackService
     */
    private $slackService;

    /**
     * Create a new instance of PlayerRepository
     *
     * @param SlackService $slackService
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function __construct(SlackService $slackService)
    {
        $this->slackService = $slackService;
    }
    /**
     * Retrieve default players
     *
     * @return Collection
     *
     * @author Roy Freij <roy@bsbip.com>
     * @version 1.0.0
     */
    public function all(): Collection
    {
        $defaultPlayers = Player::where('default', true)->get();

        return $this->slackService->getSlackUserList()
            ->filter(function ($slackUser) {
                return !$slackUser->is_bot && !$slackUser->deleted;
            })
            ->transform(function ($slackUser) use ($defaultPlayers) {
                $slackUser->default = $defaultPlayers->contains('user_id', $slackUser->id);

                return $slackUser;
            });
    }
}
