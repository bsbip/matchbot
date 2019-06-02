<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\EventInitiation;
use App\EventInitiationUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use App\Jobs\CreateMatchFromInitiation;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions for interaction
 */
class InteractionController extends Controller
{
    /**
     * Handle an interaction.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = json_decode($request->input('payload'));
        $matchCreationWaitTime = 5;

        $eventInitiation = EventInitiation::where('message_ts', $payload->message->ts)
            ->first();
        $expireAtObj = Carbon::parse($eventInitiation->expire_at);

        $user = getSlackUser($payload->user->id);

        foreach ($payload->actions as $action) {
            if (isset($eventInitiation->event_id)) {
                sendSlackMessage([
                    'channel' => $payload->channel->id,
                    'user' => $payload->user->id,
                    'text' => "Er is al een match aangemaakt voor initiatie {$eventInitiation->id}.",
                ], 'postEphemeral');

                return new JsonResponse(null, Response::HTTP_OK);
            }

            if (isset($action->selected_option)) {
                $eventInitiation->expire_at = now()->addMinutes($action->selected_option->value);

                if (!is_null($eventInitiation->getOriginal('expire_at')) && $expireAtObj->lessThan(now())) {
                    sendSlackMessage([
                        'channel' => $payload->channel->id,
                        'user' => $payload->user->id,
                        'text' => 'Het is niet meer mogelijk om een wachttijd te kiezen, omdat de oorspronkelijke wachttijd reeds is verstreken. Er zal een match worden aangemaakt zodra er voldoende belangstellenden zijn.',
                    ], 'postEphemeral');

                    return new JsonResponse(null, Response::HTTP_OK);
                }
            } elseif (isset($action->value)) {
                if (in_array($action->value, ['participate', 'decline'])) {
                    if (isset($eventInitiation->event_id) || (isset($eventInitiation->expire_at) && $expireAtObj->diffInSeconds(now()) < $matchCreationWaitTime)) {
                        sendSlackMessage([
                            'channel' => $payload->channel->id,
                            'user' => $payload->user->id,
                            'text' => 'Het is niet meer mogelijk om je keuze aan te geven.',
                        ], 'postEphemeral');

                        return new JsonResponse(null, Response::HTTP_OK);
                    }

                    $eventInitiationUser = EventInitiationUser::firstOrCreate([
                        'user_id' => $payload->user->id,
                        'event_initiation_id' => $eventInitiation->id,
                    ], [
                        'participate' => $action->value === 'participate',
                    ]);

                    if ($eventInitiationUser->participate !== ($action->value === 'participate')) {
                        $eventInitiationUser->participate = $action->value === 'participate';
                        $eventInitiationUser->save();
                    }
                } elseif ($action->value === 'start_now') {
                    if (isset($eventInitiation->expire_at) && $expireAtObj->lessThan(now())) {
                        sendSlackMessage([
                            'channel' => $payload->channel->id,
                            'user' => $payload->user->id,
                            'text' => 'Het is niet meer mogelijk om een match zelf te laten beginnen, omdat de oorspronkelijke wachttijd reeds is verstreken. Er zal een match worden aangemaakt zodra er voldoende belangstellenden zijn.',
                        ], 'postEphemeral');

                        return new JsonResponse(null, Response::HTTP_OK);
                    }

                    $eventInitiation->expire_at = now();
                }
            }
        }

        $eventInitiationUsers = EventInitiationUser::where('event_initiation_id', $eventInitiation->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $blocks = $payload->message->blocks;
        $playerElements = [];
        $amountOfParticipants = 0;
        $amountOfDecliners = 0;

        foreach ($eventInitiationUsers as $eventInitiationUser) {
            if ($eventInitiationUser->participate) {
                $emoji = ':heavy_check_mark:';
                $amountOfParticipants++;
            } else {
                $emoji = ':heavy_multiplication_x:';
                $amountOfDecliners++;
            }

            $playerElements[] = [
                'type' => 'plain_text',
                'text' => "{$emoji} {$user->profile->real_name}",
                'emoji' => true,
            ];
        }

        $blocks[1]->elements = $playerElements;
        $blocks[2]->elements = [
            [
                'type' => 'plain_text',
                'text' => "{$amountOfParticipants} potentiële speler(s)",
            ],
            [
                'type' => 'plain_text',
                'text' => "{$amountOfDecliners} afwijzende speler(s)",
            ],
        ];

        // Create a match if initiation already expired and amount of
        // participants is now high enough
        if ($expireAtObj->lessThan(now()) && $amountOfParticipants >= Config::get('match.min_users')) {
            CreateMatchFromInitiation::dispatch($eventInitiation->id)
                ->delay($eventInitiation->expire_at);
        } elseif ($eventInitiation->isDirty('expire_at')) {
            $blocks[0]->text->text = "Geef aan of je mee wilt doen met de match van {$eventInitiation->expire_at->toTimeString()}.";

            if (is_null($eventInitiation->getOriginal('expire_at'))) {
                $matchTimeText = 'De matchtijd is aangepast naar ' .
                $eventInitiation->expire_at->toTimeString() .
                    '.';
            } else {
                $matchTimeText = 'De matchtijd is aangepast van ' .
                Carbon::parse($eventInitiation->getOriginal('expire_at'))->toTimeString() .
                ' naar ' .
                $eventInitiation->expire_at->toTimeString() .
                    '.';
            }

            $eventInitiation->save();

            sendSlackMessage([
                'channel' => $payload->channel->id,
                'text' => 'De wachttijd voor initiatie ' .
                $eventInitiation->id .
                ' is om ' .
                now()->toTimeString() .
                ' aangepast door ' .
                $user->profile->real_name .
                '. ' . $matchTimeText,
            ]);

            CreateMatchFromInitiation::dispatch($eventInitiation->id)
                ->delay($eventInitiation->expire_at);
        }

        sendSlackMessage([
            'channel' => $payload->channel->id,
            'ts' => $eventInitiation->message_ts,
            'blocks' => $blocks,
        ], 'update');

        return new JsonResponse($request->all());
    }

}
