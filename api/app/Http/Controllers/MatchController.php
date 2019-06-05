<?php

namespace App\Http\Controllers;

use DB;
use App\Event;
use Exception;
use Validator;
use App\Player;
use App\Result;
use App\EventTeam;
use Carbon\Carbon;
use App\Jobs\CreateMatch;
use App\Jobs\InitiateMatch;
use Illuminate\Http\Request;
use App\Jobs\CalculatePoints;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

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

            $msg = 'Aanvraag ontvangen via webinterface. ';
            $msg .= "Even geduld alsjeblieft, Matchbot probeert teams samen te stellen.\n";
            $msg .= '*' . sizeof($users) . ' potentiële spelers*';

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
                'text' => $msg,
                'attachments' => $attachments,
            ];

            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            $data = [
                'msg' => $msg,
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
    public function createCustom(Request $request, $minUsers = null): JsonResponse
    {
        if (is_null($minUsers)) {
            $minUsers = Config::get('match.min_users');
        }

        $userIds = [];
        $users = json_decode(json_encode($request->all()), false);

        // Validate users
        if (sizeof($users) !== $minUsers) {
            return new JsonResponse([
                'msg' => 'Er zijn ' . $minUsers . ' spelers nodig.',
                'errors' => new \StdClass(),
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        foreach ($users as $user) {
            if (!isset($user->id)) {
                return new JsonResponse([
                    'msg' => 'Speler niet gevonden. Er zijn ' . $minUsers . ' spelers nodig.',
                    'errors' => new \StdClass(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (array_key_exists($user->id, $userIds)) {
                return new JsonResponse([
                    'msg' => 'Speler ' . $user->real_name . ' is meerdere keren geselecteerd.',
                    'errors' => new \StdClass(),
                ], Response::HTTP_NOT_ACCEPTABLE);
            }

            $userIds[$user->id] = true;
        }

        return createMatch($users, false);
    }

    /**
     * Get the user list.
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getUserList(): JsonResponse
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

            // Determine if user is in list with default players
            $user->default = array_search($user->id, $players) !== false;
        }

        return new JsonResponse($data);
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
     * @param int $page
     * @param int $limit
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getEventResults(int $page = 0, int $limit = 0): JsonResponse
    {
        $results = [];

        $results = Event::where('events.status', 1)
            ->with(['eventTeams' => function ($q) {
                $q->join('teams', 'teams.id', '=', 'event_teams.team_id')
                    ->join('results', function ($j) {
                        $j->on('event_teams.event_id', '=', 'results.event_id')
                            ->on('results.team_id', '=', 'event_teams.team_id')
                            ->where('deleted', false);
                    });
            }])
            ->orderBy('events.end', 'desc')
            ->when($limit > 0, function ($q) use ($page, $limit) {
                return $q->skip($page * $limit)
                    ->take($limit);
            })
            ->get();

        if (sizeof($results) == 0) {
            return new JsonResponse('Geen resultaten gevonden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($results);
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
     * @param string $statusType event status type
     * @param bool $limit true to limit
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getEvents(string $statusType = 'all', bool $limit = true): JsonResponse
    {
        $events = Event::with([
            'eventTeams' => function ($q) {
                $q->with(['result' => function ($q) {
                    $q->where('deleted', false);
                }]);
                $q->with('team');
            }]);

        if ($statusType === 'with-results') {
            $events = $events->where('events.status', '>', 0);
        } elseif ($statusType === 'without-results') {
            $events = $events->where('events.status', 0);
        }

        if ($limit) {
            $startDate = new \DateTime();
            $startDate->sub(new \DateInterval('P7D'));
            $endDate = new \DateTime();
            $events = $events->whereBetween('events.created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $events = $events->orderBy('events.start', 'desc')
            ->get();

        return new JsonResponse($events);
    }

    /**
     * Save the result of a match.
     *
     * @param Request $request
     * @param mixed $update true to update result
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function saveResult(Request $request, $update = false): JsonResponse
    {
        $update = filter_var($update, FILTER_VALIDATE_BOOLEAN);
        $matchId = $request->input('id');
        $scores = [];
        $crawlScores = [];
        $note = $request->input('note');
        $errors = [
            'errors' => new \StdClass(),
            'msg' => '',
        ];

        $scores[0] = $request->input('scoreTeam1');
        $scores[1] = $request->input('scoreTeam2');

        $crawlScores[0] = $request->input('crawlsTeam1');
        $crawlScores[1] = $request->input('crawlsTeam2');

        if (!isset($note)) {
            $note = '';
        }

        $validation = Validator::make(
            [
                'event_id' => $matchId,
                'score_team1' => $scores[0],
                'score_team2' => $scores[1],
                'crawl_score_team1' => $crawlScores[0],
                'crawl_score_team2' => $crawlScores[1],
            ],
            [
                'event_id' => 'required',
                'score_team1' => 'required',
                'score_team1' => 'required',
                'crawl_score_team1' => 'required',
                'crawl_score_team2' => 'required',
            ]
        );

        if ($validation->fails()) {
            return new JsonResponse([
                'errors' => $validation->errors(),
                'msg' => 'Vul de benodigde velden in.',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $eventTeams = EventTeam::where('event_teams.event_id', $matchId)
            ->orderBy('id', 'asc')
            ->get();

        if (sizeof($eventTeams) == 0) {
            $errors['msg'] = 'Match niet gevonden.';

            return new JsonResponse($errors, Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        // Update event info
        $event = Event::find($matchId);

        if (isset($event)) {
            if (!$update) {
                $event->end = date('Y-m-d H:i:s');
            }

            $event->status = 1;
            $event->save();
        }

        // Check if results for this event are already saved
        $results = Result::where('event_id', $matchId)
            ->where('deleted', false)
            ->get();

        if (sizeof($results) > 0 && !$update) {
            $responseText = 'Resultaten zijn reeds opgeslagen.';

            if (sizeof($results) === 2) {
                $errors['errors']->score = [];
                $errors['errors']->score[] = 'Score: ' . $results[0]->score . '-' . $results[1]->score;
                $errors['errors']->crawl_score = [];
                $errors['errors']->crawl_score[] = 'Kruipscore: ' . $results[0]->crawl_score . '-' . $results[1]->crawl_score;
                $errors['errors']->note = [];
                $errors['errors']->note[] = 'Commentaar: ' . $results[0]->note;
            }

            $errors['msg'] = $responseText;

            return new JsonResponse($errors, Response::HTTP_CONFLICT);
        }

        // Save results
        foreach ($eventTeams as $key => $eventTeam) {
            if (!isset($scores[$key]) || !isset($crawlScores[$key])) {
                $errors['msg'] = 'De (kruip)score is in een verkeerd formaat ingevoerd.';
            }

            if ($update) {
                $result = Result::where('event_id', $matchId)
                    ->where('deleted', false)
                    ->where('team_id', $eventTeam->team_id)
                    ->first();

                if (!isset($result)) {
                    $errors['msg'] = "Geen resultaten gevonden.\n";

                    return new JsonResponse($errors, Response::HTTP_NOT_ACCEPTABLE);
                }
            } else {
                $result = new Result();
            }

            $result->event_id = $matchId;
            $result->team_id = $eventTeam->team_id;
            $result->score = $scores[$key];
            $result->crawl_score = $crawlScores[$key];
            $result->note = $note;
            $result->event_team_id = $eventTeam->id;

            if (!$result->save()) {
                $errors['msg'] = 'Opslaan van resultaten is mislukt.';

                return new JsonResponse($errors, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
            $msg = 'Resultaten zijn gewijzigd.';
            $slackResponseText = 'De resultaten voor match ' . $matchId . ' zijn gewijzigd.';
        } else {
            $msg = 'Resultaten zijn toegevoegd.';
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
            'msg' => $msg,
        ]);
    }

    /**
     * Delete the result of a match.
     * Performs a soft delete.
     *
     * @param int $id the id of the match
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function deleteResult(int $id): JsonResponse
    {
        $errors = [
            'errors' => new \StdClass(),
            'msg' => '',
        ];

        $results = Result::where('event_id', $id)
            ->where('deleted', false)
            ->get();

        if (sizeof($results) === 0) {
            $errors['msg'] = 'Resultaten niet gevonden.';

            return new JsonResponse($errors, Response::HTTP_NOT_FOUND);
        }

        $event = Event::where('id', $results[0]->event_id)
            ->where('status', '>', 0)
            ->first();

        if (!isset($event)) {
            $errors['msg'] = 'Match niet gevonden.';

            return new JsonResponse($errors, Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        foreach ($results as $result) {
            $result->deleted = true;
            if (!$result->save()) {
                $errors['msg'] = 'Het verwijderen van resultaten is mislukt.';

                return new JsonResponse($errors, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $event->status = 0;

        if (!$event->save()) {
            $errors['msg'] = 'Het verwijderen van resultaten is mislukt.';

            return new JsonResponse($errors, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        DB::commit();

        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => 'De resultaten voor match ' . $event->id . ' zijn verwijderd.',
        ];

        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        CalculatePoints::dispatch();

        return new JsonResponse([
            'msg' => 'De resultaten zijn verwijderd.',
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
