<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\EventInitiation;
use App\EventInitiationUser;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use App\Jobs\CreateMatchFromInitiation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Job for initiating a match
 */
class InitiateMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventInitiation = new EventInitiation();

        if (Config::get('initiation.mention')) {
            $expireText = '<!' . Config::get('initiation.mention') . '> ';
        } else {
            $expireText = '';
        }

        if (isset($this->input['text']) && strlen($this->input['text']) > 0) {
            $eventInitiation->expire_at = Carbon::parse($this->input['text']);
            $expireText .= trans('event-initiation.choose_for_match_with_time', [
                'time' => $eventInitiation->expire_at->toTimeString(),
            ]);
        } else {
            $expireText .= trans('event-initiation.choose_for_next_match');
        }

        if (isset($this->input['user_id'])) {
            $user = getSlackUser($this->input['user_id']);
            $usersChosenText = ":heavy_check_mark: {$user->profile->real_name}";
            $potentialPlayersAmount = 1;
        } else {
            $user = null;
            $usersChosenText = trans('event-initiation.nobody_chose');
            $potentialPlayersAmount = 0;
        }

        $res = sendSlackMessage([
            'channel' => $this->input['channel_id'],
            'text' => trans('event-initiation.match_initiated'),
            'blocks' => [
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $expireText,
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'plain_text',
                            'text' => $usersChosenText,
                            'emoji' => true,
                        ],
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'plain_text',
                            'text' => trans_choice(
                                'event-initiation.potential_players',
                                $potentialPlayersAmount,
                                [
                                    'amount' => $potentialPlayersAmount,
                                ]
                            ),
                        ],
                        [
                            'type' => 'plain_text',
                            'text' => trans_choice(
                                'event-initiation.refusing_players',
                                0,
                                [
                                    'amount' => 0,
                                ]
                            ),
                        ],
                    ],
                ],
                [
                    'type' => 'actions',
                    'elements' => $this->getControlElements(),
                ],
                [
                    'type' => 'actions',
                    'elements' => [
                        [
                            'type' => 'button',
                            'style' => 'primary',
                            'value' => 'participate',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => trans('event-initiation.participate'),
                            ],
                        ],
                        [
                            'type' => 'button',
                            'style' => 'danger',
                            'value' => 'refuse',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => trans('event-initiation.refuse'),
                            ],
                        ],
                    ],
                ],
            ],
        ], 'postMessage');

        $responseBody = json_decode($res->getBody());

        if (!isset($responseBody->ts)) {
            Log::error('Failed to post Slack message for event initiation.');

            return;
        }

        $eventInitiation->message_ts = $responseBody->ts;

        if (isset($this->input['user_id'])) {
            $eventInitiation->user_id = $this->input['user_id'];
        }

        DB::beginTransaction();

        $eventInitiation->save();

        if (!is_null($user)) {
            EventInitiationUser::create([
                'user_id' => $this->input['user_id'],
                'event_initiation_id' => $eventInitiation->id,
                'participate' => true,
            ]);
        }

        if (!is_null($eventInitiation->expire_at)) {
            scheduleSlackMessage($eventInitiation, $this->input['channel_id']);
        }

        DB::commit();

        if (isset($eventInitiation->expire_at)) {
            CreateMatchFromInitiation::dispatch($eventInitiation->id)
                ->delay($eventInitiation->expire_at);
        }
    }

    /**
     * Get control elements for the initiation.
     *
     * @return array
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function getControlElements(): array
    {
        $waitTimes = Config::get('initiation.wait_times');
        $waitTimeOptions = [];

        foreach ($waitTimes as $waitTime) {
            if ($waitTime === 1) {
                $unit = 'minuut';
            } else {
                $unit = 'minuten';
            }

            $waitTimeOptions[] = [
                'text' => [
                    'type' => 'plain_text',
                    'text' => "{$waitTime} {$unit}",
                ],
                'value' => "{$waitTime}",
            ];
        }

        return [
            [
                'action_id' => 'wait_time_select',
                'type' => 'static_select',
                'placeholder' => [
                    'type' => 'plain_text',
                    'text' => trans('event-initiation.change_wait_time'),
                ],
                'options' => $waitTimeOptions,
            ],
            [
                'type' => 'button',
                'value' => 'start_now',
                'text' => [
                    'type' => 'plain_text',
                    'text' => trans('event-initiation.start_now'),
                ],
            ],
            [
                'type' => 'button',
                'value' => 'schedule_again',
                'text' => [
                    'type' => 'plain_text',
                    'text' => trans('event-initiation.schedule_again'),
                ],
            ],
        ];
    }
}
