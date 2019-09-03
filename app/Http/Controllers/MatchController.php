<?php

namespace App\Http\Controllers;

use DB;
use App\Event;
use Exception;
use App\Player;
use App\Result;
use App\EventTeam;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Jobs\CreateMatch;
use App\Jobs\InitiateMatch;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\CalculatePoints;
use App\Support\PeriodSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\CreateCustomEventRequest;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

/**
 * Actions for matches
 */
class MatchController extends Controller
{
    /**
     * Initiate a match.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function initiate(Request $request): JsonResponse
    {
        // Check if valid wait time has been provided
        if (strlen($request->input('text')) > 0) {
            try {
                Carbon::parse($request->input('text'));
            } catch (Exception $e) {
                Log::info($e);

                return new JsonResponse([
                    'text' => trans('event-initiation.provide_valid_wait_time'),
                ], Response::HTTP_OK);
            }
        }
        InitiateMatch::dispatch($request->all());

        return new JsonResponse([
            'response_type' => 'in_channel',
            'text' => trans('event-initiation.match_will_be_initiated'),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new match.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function create(Request $request): JsonResponse
    {
        if (strpos($request->input('text'), 'help') !== false) {
            $availableUsers = getAvailableUsers();
            $users = getSlackUserList(env('SLACK_TOKEN'));

            return new JsonResponse([
                'color' => '#00ff00',
                'text' => $this->viewHelp($users, $availableUsers),
            ]);
        }

        if ($request->has('users')) {
            $attachments = [];
            $users = getUsersBySlackId($request->input('users'));
            $this->dispatch(new CreateMatch('', $users));

            $message = 'Aanvraag ontvangen via webinterface. ';
            $message .= "Even geduld alsjeblieft, Matchbot probeert teams samen te stellen.\n";
            $message .= '*' . sizeof($users) . ' potentiële spelers*';

            foreach ($users as $user) {
                $attachments[] = [
                    'author_name' => $user->real_name,
                    'author_icon' => $user->profile->image_72,
                ];
            }

            $data = [
                'response_type' => 'in_channel',
                'username' => 'Matchbot',
                'icon_url' => asset('assets/img/matchbot-icon.jpg'),
                'text' => $message,
                'attachments' => $attachments,
            ];

            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            $data = [
                'msg' => $message,
            ];

        } else {
            $this->dispatch(new CreateMatch($request->input('text')));

            $data = [
                'response_type' => 'in_channel',
                'username' => 'Matchbot',
                'icon_url' => asset('assets/img/matchbot-icon.jpg'),
                'text' => 'Aanvraag ontvangen. Even geduld alsjeblieft, Matchbot probeert teams samen te stellen.',
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Create a new custom match.
     *
     * @param  Request $request the request
     *
     * @param  int|null $minUsers the minimum amount of users
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function createCustom(CreateCustomEventRequest $request): JsonResponse
    {
        $users = json_decode(json_encode($request->input('users')));

        return createMatch($users, (bool) $request->input('random'));
    }

    /**
     * Get the user list.
     *
     * @param Request $request
     *
     * @return JsonResponse|InertiaResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     * @author Roy Freij <info@royfreij.nl>
     */
    public function getUserList(Request $request)
    {
        $data = [];
        $players = Player::where('default', true)
            ->pluck('user_id')
            ->toArray();

        $users = getSlackUserList(env('SLACK_TOKEN'));

        foreach ($users as $user) {
            // Filter: only get users (no bots and not deleted)
            if (
                $user->id !== 'USLACKBOT' &&
                (isset($user->is_bot) && !$user->is_bot) &&
                (isset($user->deleted) && !$user->deleted)
            ) {
                $data[] = $user;
            }

            $user->default = array_search($user->id, $players) !== false;
        }

        if (Str::contains($request->header('Accept'), 'application/json')) {
            return new JsonResponse([
                $data,
            ]);
        }

        $component = 'Match';

        if ($request->routeIs('players')) {
            $component = 'Players';
        }

        return Inertia::render($component, [
            'data' => $data,
        ]);

    }

    /**
     * Save the result of a match via Slack.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function saveResultSlack(Request $request): JsonResponse
    {
        $text = $request->input('text');
        $matchId = substr($text, 0, strpos($text, ' '));
        $scores = [];
        $crawlScores = [];
        $note = substr($text, strpos($text, 'note ') + 5);
        $errors = [];

        // Check presence and order of text elements
        $posScore = strpos($text, 'score');
        $posCrawlScore = strpos($text, 'crawlscore');
        $posNote = strpos($text, 'note');

        if ($posScore === false || $posCrawlScore === false || $posNote == false) {
            $errors[] = 'De vereiste elementen zijn niet gevonden.';

            return createErrorMessage($errors);
        }

        if ($posCrawlScore < $posScore) {
            $errors[] = 'Zet de elementen in de juiste volgorde.';

            return createErrorMessage($errors);
        } elseif ($posNote < $posScore || $posNote < $posCrawlScore) {
            $errors[] = 'Zet de elementen in de juiste volgorde.';

            return createErrorMessage($errors);
        }

        $scoresText = substr($text, strpos($text, 'score ') + 6, strpos($text, ' crawlscore') - (strpos($text, 'score ') + 6));
        $scores[0] = substr($scoresText, 0, strpos($scoresText, '-'));
        $scores[1] = substr($scoresText, strpos($scoresText, '-') + 1);

        $crawlScoresText = substr($text, strpos($text, 'crawlscore ') + 11, strpos($text, ' note') - (strpos($text, 'crawlscore') + 11));
        $crawlScores[0] = substr($crawlScoresText, 0, strpos($crawlScoresText, '-'));
        $crawlScores[1] = substr($crawlScoresText, strpos($crawlScoresText, '-') + 1);

        $eventTeams = EventTeam::where('event_teams.event_id', $matchId)
            ->orderBy('id', 'asc')
            ->get();

        if (sizeof($eventTeams) == 0) {
            $errors[] = 'Match niet gevonden.';

            return createErrorMessage($errors);
        }

        // Update event info
        $event = Event::find($matchId);
        if (sizeof($event) > 0) {
            $event->end = date('Y-m-d H:i:s');
            $event->status = 1;
            $event->save();
        }

        // Check if results for this event are already saved
        $results = Result::where('event_id', $matchId)
            ->where('deleted', false)
            ->get();

        if (sizeof($results) > 0) {
            $responseText = "Results are already saved.\n";

            if (sizeof($results) === 2) {
                $responseText .= ' Score: ' . $results[0]->score . '-' . $results[1]->score . "\n";
                $responseText .= ' Crawl score: ' . $results[0]->crawl_score . '-' . $results[1]->score;
            }

            $errors[] = $responseText;

            return createErrorMessage($errors);
        }

        // Save results
        foreach ($eventTeams as $key => $eventTeam) {
            if (!isset($scores[$key]) || !isset($crawlScores[$key])) {
                $errors[] = '(Crawl) score is in wrong format.';
            }

            $result = new Result();
            $result->event_id = $matchId;
            $result->team_id = $eventTeam->team_id;
            $result->score = $scores[$key];
            $result->crawl_score = $crawlScores[$key];
            $result->note = $note;

            if (!$result->save()) {
                $errors[] = 'Failed to save result';

                return createErrorMessage($errors);
            }
        }

        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => 'Resultaten opgeslagen.',
        ];

        CalculatePoints::dispatch();

        return new JsonResponse($data);
    }

    /**
     * Get events with results and teams.
     *
     * @param Request $request
     *
     * @return JsonResponse|InertiaResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     * @author Roy Freij <info@royfreij.nl>
     */
    public function getEventResults(Request $request)
    {
        $results = [];

        $results = Event::where('events.status', 1)
            ->with(['results' => function ($query) {
                $query
                    ->where('deleted', false)
                    ->with('team');
            }])
            ->orderBy('events.end', 'desc')
            ->paginate((int) $request->query('limit', 25));

        $data = (new PaginatedResourceResponse($results))->resource;

        if (Str::contains($request->header('Accept'), 'application/json')) {
            return new JsonResponse([
                'collection' => $data->toArray(),
            ]);
        }

        return Inertia::render('Overview', [
            'collection' => $data->toArray(),
        ]);
    }

    /**
     * Get the help text.
     *
     * @param  object $users Slack users
     * @param  array $availableUsers users in standard list
     *
     * @return string
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function viewHelp(object $users, array $availableUsers): string
    {
        $usersText = 'Actieve gebruikers: ';
        $availableUsersText = 'Spelers aanwezig in standaardlijst: ';

        foreach ($users as $key => $value) {
            if (isset($value->presence)) {
                if ($value->presence == 'active') {
                    $usersText .= $value->name . ', ';
                }
            }
        }

        foreach ($availableUsers as $availableUser) {
            $availableUsersText .= $availableUser . ', ';
        }

        $usersText = substr($usersText, 0, -2);
        $availableUsersText = substr($availableUsersText, 0, -2);

        $helpText = "Matchbot | Help\n\n" .
        $usersText . "\n" .
        $availableUsersText . "\n\n" .
        "Match aanmaken:\n" .
        "*/match &lt;parameters&gt;*\tKies willekeurig spelers uit de standaardlijst die online zijn.\n\n" .
        "Parameters:\n" .
        "*add &lt;@gebruikersnaam&gt;*\tVoeg voor de komende match (extra) spelers toe aan de lijst waaruit spelers worden gekozen. Gebruikersnamen (inclusief @-teken) scheiden met een spatie.\n" .
        "*help*\tToon help.\n" .
        "*online*\tSelecteer alleen spelers die online zijn.\n" .
        "*remove &lt;@gebruikersnaam&gt;*\tVerwijder voor de komende match spelers uit de lijst waaruit spelers worden gekozen. Gebruikersnamen (inclusief @-teken) scheiden met een spatie.\n\n" .
        'Beheer van resultaten en statistieken weergeven: ' . env('APP_URL');

        return $helpText;
    }

    /**
     * Get events.
     *
     * @param Request $request
     *
     * @return JsonResponse|InertiaResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     * @author Roy Freij <info@royfreij.nl>
     */
    public function getEvents(Request $request)
    {
        $statusType = $request->query('statusType', 'without-results');

        $events = Event::query()
            ->when($statusType === 'with-results', function ($query) {
                $query->where('status', '>', 0);
            })
            ->when($statusType === 'without-results', function ($query) {
                $query->where('status', 0);
            })
            ->when($request->has('limited'), function ($query) {
                $period = PeriodSupport::lastSevenDays();
                $query->whereBetween('events.created_at', [
                    $period->from,
                    $period->to,
                ]);
            })
            ->with([
                'eventTeams' => function ($query) {
                    $query->with([
                        'team',
                        'result',
                    ]);
                },
            ])
            ->orderBy('start', 'desc')
            ->get();

        $data = [
            'events' => $events->toArray(),
        ];

        if (Str::contains($request->header('Accept'), 'application/json')) {
            return new JsonResponse($data);
        }

        return Inertia::render('Results', $data);
    }

    /**
     * Save the result of a match.
     *
     * @param UpdateEventRequest $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function saveResult(UpdateEventRequest $request): JsonResponse
    {
        $update = $request->isMethod(Request::METHOD_PUT);
        $matchId = $request->input('event_id');

        DB::beginTransaction();

        $event = Event::find($matchId);

        if (isset($event)) {
            if (!$update) {
                $event->end = Carbon::now();
            }

            $event->status = 1;
            $event->save();
        }

        $results = Result::where('event_id', $matchId)
            ->where('deleted', false)
            ->get();

        if (sizeof($results) > 0 && !$update) {
            return new JsonResponse([
                'message' => trans('results.already_saved'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $eventTeams = EventTeam::where('event_teams.event_id', $matchId)
            ->orderBy('id')
            ->get();

        foreach ($eventTeams as $key => $eventTeam) {

            $teamResults = $request->get('teams')[$key];

            Result::updateOrCreate([
                'team_id' => $eventTeam->team_id,
                'event_id' => $matchId,
                'deleted' => false,
            ], [
                'score' => $teamResults['score'],
                'crawl_score' => $teamResults['crawl_score'],
                'note' => $request->input('note') ?? '',
                'event_team_id' => $eventTeam->id,
            ]);
        }

        DB::commit();

        $record = Result::where('results.event_id', $matchId)
            ->where('deleted', false)
            ->join('event_teams AS et', function ($q) {
                $q->on('et.event_id', '=', 'results.event_id');
                $q->on('et.team_id', '=', 'results.team_id');
            })
            ->join('teams AS t', 't.id', '=', 'results.team_id')
            ->get();

        if ($update) {
            $message = 'Resultaten zijn gewijzigd.';
            $slackResponseText = 'De resultaten voor match ' . $matchId . ' zijn gewijzigd.';
        } else {
            $message = 'Resultaten zijn toegevoegd.';
            $slackResponseText = 'De resultaten voor match ' . $matchId . ' zijn toegevoegd.';
        }

        $attachments = $this->makeResultAttachments($event, $record);

        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => $slackResponseText,
            'attachments' => $attachments,
        ];

        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        CalculatePoints::dispatch();

        return new JsonResponse([
            'message' => $message,
        ]);
    }

    /**
     * Delete the result of a match.
     * Performs a soft delete.
     *
     * @param int $eventId
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     * @author Roy Freij <info@royfreij.nl>
     */
    public function deleteResult(int $eventId): JsonResponse
    {
        $event = Event::findOrFail($eventId);

        DB::beginTransaction();

        $resultsUpdated = Result::where([
            'event_id' => $event->id,
            'deleted' => false,
        ])->update([
            'deleted' => true,
        ]);

        if ($resultsUpdated) {
            $event->update([
                'status' => false,
            ]);
        } else {
            return new JsonResponse([
                'message' => 'Resultaten niet gevonden',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::commit();

        // TODO extract into service
        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => 'De resultaten voor match ' . $event->id . ' zijn verwijderd.',
        ];

        // TODO export env to config files
        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        CalculatePoints::dispatch();

        return new JsonResponse([
            'message' => 'De resultaten zijn verwijderd.',
        ]);
    }

    /**
     * Make result attachments for an event.
     *
     * @param  object $event the event
     * @param  object $record the results with teams
     *
     * @return array
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function makeResultAttachments(Event $event, Collection $record): array
    {
        $attachments = [
            [
                'text' => "\n<" . env('APP_URL') . '|Bekijken>',
            ],
        ];

        if (!isset($event->start)) {
            return $attachments;
        }
        if (!isset($record[0]->name) || !isset($record[1]->name)) {
            return $attachments;
        }
        if (!isset($record[0]->score) || !isset($record[1]->score)) {
            return $attachments;
        }
        if (!isset($record[0]->crawl_score) || !isset($record[1]->crawl_score)) {
            return $attachments;
        }
        if (!isset($record[0]->note)) {
            return $attachments;
        }

        $attachments = [
            [
                'fields' => [
                    [
                        'title' => 'Matchnaam',
                        'value' => $event->name,
                        'short' => true,
                    ],
                    [
                        'title' => 'Datum/tijd match',
                        'value' => date_format(date_create($event->start), 'd-m-Y H:i:s'),
                        'short' => true,
                    ],
                    [
                        'title' => 'Team 1',
                        'value' => $record[0]->name,
                        'short' => true,
                    ],
                    [
                        'title' => 'Team 2',
                        'value' => $record[1]->name,
                        'short' => true,
                    ],
                    [
                        'title' => 'Score',
                        'value' => $record[0]->score . '-' . $record[1]->score,
                        'short' => true,
                    ],
                    [
                        'title' => 'Kruipscore',
                        'value' => $record[0]->crawl_score . '-' . $record[1]->crawl_score,
                        'short' => true,
                    ],
                ],
            ],
            [
                'title' => 'Commentaar',
                'text' => $record[0]->note === '' ? '-' : $record[0]->note,
            ],
            [
                'fallback' => "\n<" . env('APP_URL') . '|Bekijken>',
                'actions' => [
                    [
                        'type' => 'button',
                        'text' => 'Nieuwe match aanmaken',
                        'url' => env('APP_URL') . '/match',
                        'style' => 'primary',
                    ],
                    [
                        'type' => 'button',
                        'text' => 'Resultaten wijzigen',
                        'url' => env('APP_URL') . '/results',
                    ],
                    [
                        'type' => 'button',
                        'text' => 'Resultaten weergeven',
                        'url' => env('APP_URL'),
                    ],
                    [
                        'type' => 'button',
                        'text' => 'Statistieken bekijken',
                        'url' => env('APP_URL') . '/stats',
                    ],
                    [
                        'type' => 'button',
                        'text' => 'Spelers beheren',
                        'url' => env('APP_URL') . '/players',
                    ],
                ],
            ],
        ];

        return $attachments;
    }

}
