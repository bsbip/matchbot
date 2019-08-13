<?php

namespace App\Jobs;

use App\Player;
use Carbon\Carbon;
use App\EventInitiation;
use App\EventInitiationUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use App\Jobs\CreateMatchFromInitiation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Job for interacting
 */
class Interact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $matchCreationWaitTime = 5;
        $eventInitiation = $this->getEventInitiation();
        $expireAtObj = Carbon::parse($eventInitiation->expire_at);

        foreach ($this->payload->actions as $action) {
            if (isset($eventInitiation->event_id)) {
                sendSlackMessage([
                    'channel' => $this->payload->channel->id,
                    'user' => $this->payload->user->id,
                    'text' => trans('event-initiation.match_already_created', [
                        'id' => $eventInitiation->id,
                    ]),
                ], 'postEphemeral');

                return;
            }

            if (isset($action->selected_option)) {
                $eventInitiation->expire_at = now()->addMinutes($action->selected_option->value);

                if (!is_null($eventInitiation->getOriginal('expire_at')) && $expireAtObj->lessThan(now())) {
                    sendSlackMessage([
                        'channel' => $this->payload->channel->id,
                        'user' => $this->payload->user->id,
                        'text' => trans('event-initiation.cannot_choose_wait_time'),
                    ], 'postEphemeral');

                    return;
                }
            } elseif (isset($action->value)) {
                if (in_array($action->value, ['participate', 'refuse'])) {
                    if (
                        isset($eventInitiation->event_id) ||
                        (isset($eventInitiation->expire_at) &&
                            $expireAtObj->diffInSeconds(now()) < $matchCreationWaitTime)
                    ) {
                        sendSlackMessage([
                            'channel' => $this->payload->channel->id,
                            'user' => $this->payload->user->id,
                            'text' => trans('event-initiation.cannot_choose'),
                        ], 'postEphemeral');

                        return;
                    }

                    $participate = $action->value === 'participate';
                    $eventInitiationUser = $this->saveEventInitiationUser($eventInitiation, $participate);
                } elseif ($action->value === 'start_now') {
                    if (isset($eventInitiation->expire_at) && $expireAtObj->lessThan(now())) {
                        sendSlackMessage([
                            'channel' => $this->payload->channel->id,
                            'user' => $this->payload->user->id,
                            'text' => trans('event-initiation.cannot_start'),
                        ], 'postEphemeral');

                        return;
                    }

                    $eventInitiation->expire_at = now();
                } elseif ($action->value === 'schedule_again' && isset($eventInitiation->expire_at)) {
                    if (
                        isset($eventInitiation->event_id) ||
                        $expireAtObj->diffInSeconds(now()) < $matchCreationWaitTime
                    ) {
                        sendSlackMessage([
                            'channel' => $this->payload->channel->id,
                            'user' => $this->payload->user->id,
                            'text' => trans('event-initiation.cannot_schedule'),
                        ], 'postEphemeral');

                        return;
                    }

                    $eventInitiation->expire_at = null;
                    $eventInitiation->start_when_possible = false;
                }
            }
        }

        $eventInitiationUsers = $this->getEventInitiationUsers($eventInitiation->id);
        $blocks = $this->payload->message->blocks;
        $playerElements = [];
        $userRealName = null;
        $amountOfParticipants = 0;
        $amountOfRefusers = 0;

        foreach ($eventInitiationUsers as $eventInitiationUser) {
            if ($eventInitiationUser->participate) {
                $emoji = ':heavy_check_mark:';
                $amountOfParticipants++;
            } else {
                $emoji = ':heavy_multiplication_x:';
                $amountOfRefusers++;
            }

            if ($eventInitiationUser->player === null) {
                // Player does not exist in database.
                // Get real name of user by making a Slack API request.
                $eventInitiationSlackUserRealName = getSlackUser($eventInitiationUser->user_id)->profile->real_name;
            } else {
                $eventInitiationSlackUserRealName = $eventInitiationUser->player->name;
            }

            if ($eventInitiationUser->user_id === $this->payload->user->id) {
                $userRealName = $eventInitiationSlackUserRealName;
            }

            $playerElements[] = [
                'type' => 'plain_text',
                'text' => "{$emoji} {$eventInitiationSlackUserRealName}",
                'emoji' => true,
            ];
        }

        $blocks[1]->elements = $playerElements;
        $blocks[2]->elements = [
            [
                'type' => 'plain_text',
                'text' => trans_choice(
                    'event-initiation.potential_players',
                    $amountOfParticipants,
                    [
                        'amount' => $amountOfParticipants,
                    ]
                ),
            ],
            [
                'type' => 'plain_text',
                'text' => trans_choice(
                    'event-initiation.refusing_players',
                    $amountOfRefusers,
                    [
                        'amount' => $amountOfRefusers,
                    ]
                ),
            ],
        ];

        // Create a match if initiation already expired and amount of
        // participants is now high enough and the scheduled match is not reset.
        if (
            $expireAtObj->lessThan(now()) &&
            $amountOfParticipants >= Config::get('match.min_users') &&
            $eventInitiation->getOriginal('start_when_possible')
        ) {
            deleteEventInitiationScheduledSlackMessages($eventInitiation->id);
            CreateMatchFromInitiation::dispatch($eventInitiation->id);
        } elseif ($eventInitiation->isDirty('expire_at')) {
            if (is_null($eventInitiation->expire_at)) {
                $blocks[0]->text->text = trans('event-initiation.choose_for_next_match');
                $matchTimeText = trans('event-initiation.scheduled_match_reset', [
                    'time' => Carbon::parse($eventInitiation->getOriginal('expire_at'))->toTimeString(),
                ]);
            } else {
                $blocks[0]->text->text = trans('event-initiation.choose_for_match_with_time', [
                    'time' => $eventInitiation->expire_at->toTimeString(),
                ]);

                if (is_null($eventInitiation->getOriginal('expire_at'))) {
                    $matchTimeText = trans('event-initiation.match_time_changed_to', [
                        'time' => $eventInitiation->expire_at->toTimeString(),
                    ]);
                } else {
                    $matchTimeText = trans('event-initiation.match_time_changed_from_to', [
                        'from_time' => Carbon::parse($eventInitiation->getOriginal('expire_at'))->toTimeString(),
                        'to_time' => $eventInitiation->expire_at->toTimeString(),
                    ]);
                }
            }

            deleteEventInitiationScheduledSlackMessages($eventInitiation->id);

            $eventInitiation->save();

            if (is_null($userRealName)) {
                // User did not chose, but changed match time
                $player = Player::where('user_id', $this->payload->user->id)
                    ->first();

                if ($player === null) {
                    $userRealName = getSlackUser($this->payload->user->id)->profile->real_name;
                } else {
                    $userRealName = $player->profile->real_name;
                }
            }

            sendSlackMessage([
                'channel' => $this->payload->channel->id,
                'text' => trans('event-initiation.match_time_changed_at_by_user', [
                    'id' => $eventInitiation->id,
                    'time' => now()->toTimeString(),
                    'user' => $userRealName,
                ]) . ' ' . $matchTimeText,
            ]);

            if (!is_null($eventInitiation->expire_at)) {
                if (Carbon::parse($eventInitiation->expire_at)->lessThanOrEqualTo(now())) {
                    CreateMatchFromInitiation::dispatch($eventInitiation->id);
                } else {
                    CreateMatchFromInitiation::dispatch($eventInitiation->id)
                        ->delay($eventInitiation->expire_at);

                    scheduleSlackMessage($eventInitiation, $this->payload->channel->id);
                }
            }
        }

        sendSlackMessage([
            'channel' => $this->payload->channel->id,
            'ts' => $eventInitiation->message_ts,
            'blocks' => $blocks,
        ], 'update');
    }

    /**
     * Get the event initiation.
     *
     * @return EventInitiation
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function getEventInitiation(): EventInitiation
    {
        return EventInitiation::where('message_ts', $this->payload->message->ts)
            ->first();
    }

    /**
     * Get the users for the event initiation.
     *
     * @param int $id the event initiation id
     *
     * @return Collection
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function getEventInitiationUsers(int $id): Collection
    {
        return EventInitiationUser::where('event_initiation_id', $id)
            ->with(['player'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Save the event initiation user.
     *
     * @param EventInitiation $eventInitiation
     * @param bool $participate
     *
     * @return EventInitiationUser
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function saveEventInitiationUser(EventInitiation $eventInitiation, bool $participate): EventInitiationUser
    {
        $eventInitiationUser = EventInitiationUser::firstOrCreate([
            'user_id' => $this->payload->user->id,
            'event_initiation_id' => $eventInitiation->id,
        ], [
            'participate' => $participate,
        ]);

        if ($eventInitiationUser->participate !== $participate) {
            $eventInitiationUser->participate = $participate;
            $eventInitiationUser->save();
        }

        return $eventInitiationUser;
    }
}
