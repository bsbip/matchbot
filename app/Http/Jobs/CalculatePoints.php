<?php

namespace App\Jobs;

use App\Event;
use App\EventTeam;
use App\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculatePoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * collection of players
     *
     * @var Collection
     */
    protected $players;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        Player::where('status', 1)
            ->update([
                'points' => 50,
            ]);

        $this->players = Player::where('status', 1)->get();

        Event::where('events.status', 1)
            ->with([
                'eventTeams' => function ($query) {
                    $query->with([
                        'result', 'players',
                    ]);
                },
            ])
            ->chunk(100, function ($matches) {
                foreach ($matches as $match) {
                    $this->calculatePoints($match);
                }
            });

        $this->players->each(function ($player) {
            $player->save();
        });
    }

    /**
     * Calculate points per match.
     *
     * @param  Event $match
     *
     * @return void
     */
    public function calculatePoints(Event $match): void
    {
        foreach ($match->eventTeams as $team) {
            $team->points = $this->calculatePointsForTeam($team);
        }

        $highestPointsMatch = max($match->eventTeams[0]->points, $match->eventTeams[1]->points);
        $lowestPointsMatch = min($match->eventTeams[0]->points, $match->eventTeams[1]->points);
        $fairnessCorrection = (($highestPointsMatch === 0 ? 1 : $highestPointsMatch) / ($lowestPointsMatch === 0 ? 1 : $lowestPointsMatch));
        $goalsDiff = abs($match->eventTeams[0]->result->score - $match->eventTeams[1]->result->score);
        $goalMultiplier = 1 + ($goalsDiff / 10);
        $basePoints = 20 * $goalMultiplier;
        $match->eventTeams[0]->win = $this->isWinner($match->eventTeams[0], $match->eventTeams[1]);
        $match->eventTeams[1]->win = $this->isWinner($match->eventTeams[1], $match->eventTeams[0]);

        foreach ($match->eventTeams as $team) {
            $team = $this->setPointsPerTeam($team, $highestPointsMatch, $basePoints, $fairnessCorrection);
        }
    }

    /**
     * Calculate points per team.
     *
     * @param  EventTeam $team
     *
     * @return int
     */
    public function calculatePointsForTeam(EventTeam $team): int
    {
        return $this->players->filter(function ($player) use ($team) {
            return in_array($player->name, $team->players->pluck('name')->toArray());
        })->sum('points');
    }

    /**
     * Set points per team.
     *
     * @param  EventTeam $team
     * @param  int $highestPoints
     * @param  int $basePoints
     * @param  float $fairnessCorrection
     *
     * @return EventTeam
     */
    public function setPointsPerTeam(EventTeam $team, int $highestPoints, int $basePoints, float $fairnessCorrection): EventTeam
    {
        if ($team->win) {
            if ($highestPoints == $team->points) {
                $points = ($basePoints / $fairnessCorrection);
            } else {
                $points = ($basePoints * $fairnessCorrection);
            }
        } else {
            if ($highestPoints == $team->points) {
                $points = -($basePoints * $fairnessCorrection);
            } else {
                $points = -($basePoints / $fairnessCorrection);
            }
        }

        $team->pointsAcquired = round($points);

        foreach ($team->players as $player) {
            $this->players->transform(function ($playerToChange) use ($player, $team) {

                if ($playerToChange->name === $player->name) {
                    $playerToChange->points += $this->setPointsPerPlayer($playerToChange, $team);
                }

                if ($playerToChange->points < 0) {
                    $playerToChange->points = 0;
                }

                return $playerToChange;
            });
        }

        return $team;
    }

    /**
     * Set points per player.
     *
     * @param  Player $player
     * @param  EventTeam $team
     *
     * @return float
     */
    public function setPointsPerPlayer(Player $player, EventTeam $team): float
    {
        $percentage = ($player->points / $team->points);

        if ($team->points === 0) {
            $percentage = 0.50;
        }

        if ($team->win) {
            $percentage = (1 - $percentage);
        }

        return round(($team->pointsAcquired * $percentage));
    }

    /**
     * Check if team is winner.
     *
     * @param  EventTeam $team
     * @param  EventTeam $opponent
     *
     * @return bool
     */
    public function isWinner(EventTeam $team, EventTeam $opponent): bool
    {
        return $team->result->score > $opponent->result->score;
    }
}
