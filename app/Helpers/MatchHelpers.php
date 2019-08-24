<?php

use App\Team;
use App\Event;
use App\Player;
use App\EventTeam;
use App\TeamPlayer;
use App\EventInitiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

/**
 * Create a new match.
 *
 * @param  array $activeUsers the active users
 * @param  bool $random true to choose random users
 * @param  EventInitiation|null $eventInitiation the event initiation
 *
 * @return JsonResponse
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function createMatch(array $activeUsers, bool $random = true, $eventInitiation = null): JsonResponse
{
    $responseText = '';
    $maxUsers = 0;
    $randomUsers = [];
    $attachments = [];
    $teamPlayers = [];
    $data = [
        'response_type' => 'in_channel',
        'username' => 'Matchbot',
        'icon_url' => asset('assets/img/matchbot-icon.jpg'),
    ];

    if (sizeof($activeUsers) < Config::get('match.min_users')) {
        if (isset($eventInitiation)) {
            $responseText = trans('event-initiation.cannot_create_match');

            $eventInitiation->start_when_possible = true;
            $eventInitiation->save();
        } else {
            $responseText = 'Er zijn niet genoeg spelers beschikbaar op dit moment.';
        }

        $data['text'] = $responseText;
        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        return new JsonResponse([
            'msg' => $data['text'],
            'errors' => new \StdClass(),
        ]);
    } else {
        $maxUsers = Config::get('match.min_users');
    }

    \DB::beginTransaction();

    if ($maxUsers > 0) {
        if ($random) {
            // Create array with new keys of type integer
            $activeUsers = array_values($activeUsers);
            // Get random keys
            for ($i = 0; $i < $maxUsers; $i++) {
                $randomKey = -1;
                while (in_array($randomKey, $randomUsers) || $randomKey === -1) {
                    $randomKey = mt_rand(0, sizeof($activeUsers) - 1);
                }
                $randomUsers[$i] = $randomKey;
            }
        } elseif (sizeof($activeUsers) === $maxUsers) {
            $randomUsers = array_keys($activeUsers);
        } else {
            $data['text'] = 'Er zijn ' . $maxUsers . ' voor een match nodig.';
            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            return new JsonResponse([
                'msg' => $data['text'],
                'errors' => new \StdClass(),
            ]);
        }

        // Save to database
        $event = new Event();

        if ($random) {
            $event->name = 'Matchbot match';
        } else {
            $event->name = 'Zelf samengestelde match';
        }

        $event->start = date('Y-m-d H:i:s');
        $event->status = 0;

        if (!$event->save()) {
            \DB::rollBack();

            $data['text'] = 'Match aanmaken mislukt. Probeer het opnieuw.';
            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            return new JsonResponse([
                'msg' => $data['text'],
                'errors' => new \StdClass(),
            ]);
        }
    }

    $responseText .= '<@' . $activeUsers[$randomUsers[0]]->id . '> (' . $activeUsers[$randomUsers[0]]->real_name . ') ';
    $responseText .= 'en <@' . $activeUsers[$randomUsers[1]]->id . '> (' . $activeUsers[$randomUsers[1]]->real_name . ')';
    $responseText .= ' spelen een tafelvoetbalwedstrijd tegen ';
    $responseText .= '<@' . $activeUsers[$randomUsers[2]]->id . '> (' . $activeUsers[$randomUsers[2]]->real_name . ') ';
    $responseText .= 'en <@' . $activeUsers[$randomUsers[3]]->id . '> (' . $activeUsers[$randomUsers[3]]->real_name . ').';

    $attachments = [
        [
            'color' => '#042B95',
            'text' => 'Team 1',
            'title' => $activeUsers[$randomUsers[0]]->real_name,
            'thumb_url' => $activeUsers[$randomUsers[0]]->profile->image_72,
        ],
        [
            'color' => '#042B95',
            'text' => 'Team 1',
            'title' => $activeUsers[$randomUsers[1]]->real_name,
            'thumb_url' => $activeUsers[$randomUsers[1]]->profile->image_72,
        ],
        [
            'color' => '#0E0E11',
            'text' => 'Team 2',
            'title' => $activeUsers[$randomUsers[2]]->real_name,
            'thumb_url' => $activeUsers[$randomUsers[2]]->profile->image_72,
        ],
        [
            'color' => '#0E0E11',
            'text' => 'Team 2',
            'title' => $activeUsers[$randomUsers[3]]->real_name,
            'thumb_url' => $activeUsers[$randomUsers[3]]->profile->image_72,
        ],
    ];

    $teamPlayers[1] = [];
    $teamPlayers[1][] = $activeUsers[$randomUsers[0]];
    $teamPlayers[1][] = $activeUsers[$randomUsers[1]];

    $teamPlayers[2] = [];
    $teamPlayers[2][] = $activeUsers[$randomUsers[2]];
    $teamPlayers[2][] = $activeUsers[$randomUsers[3]];

    foreach ($teamPlayers as $key => $value) {
        $players = [];

        foreach ($value as $number => $teamPlayer) {
            $player = Player::where('user_id', $teamPlayer->id)
                ->first(['id']);

            if (!$player) {
                $player = new Player();
                $player->user_id = $teamPlayer->id;
                $player->name = $teamPlayer->real_name;
                $player->username = $teamPlayer->name;
                $player->status = 1;
                $player->default = false;

                if (!$player->save()) {
                    \DB::rollBack();

                    $data['text'] = 'Opslaan van speler mislukt. Probeer het opnieuw.';
                    sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

                    return new JsonResponse([
                        'msg' => $data['text'],
                        'errors' => new \StdClass(),
                    ]);
                }
            }

            $players[] = $player;

            if (!saveEventPlayer($event->id, $player->id, $number + 1)) {
                \DB::rollBack();

                $data['text'] = 'Opslaan van speler voor match mislukt. Probeer het opnieuw.';
                sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

                return new JsonResponse([
                    'msg' => $data['text'],
                    'errors' => new \StdClass(),
                ]);
            }
        }

        // Save team player and team
        if (sizeof($value) === 2) {
            $teamPlayer = DB::table('team_players AS team_player1')
                ->join('team_players AS team_player2', 'team_player1.team_id', '=', 'team_player2.team_id')
                ->where('team_player1.player_id', $players[0]->id)
                ->where('team_player2.player_id', $players[1]->id)
                ->first();

            if (!$teamPlayer) {
                $team = new Team();
                $team->name = $value[0]->real_name . '+' . $value[1]->real_name;
                $team->status = 1;

                if (!$team->save()) {
                    \DB::rollBack();

                    $data['text'] = 'Opslaan van team mislukt. Probeer het opnieuw.';
                    sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

                    return new JsonResponse([
                        'msg' => $data['text'],
                        'errors' => new \StdClass(),
                    ]);
                }

                $teamPlayer1 = new TeamPlayer();
                $teamPlayer1->team_id = $team->id;
                $teamPlayer1->player_id = $players[0]->id;
                $teamPlayer1->status = 1;

                $teamPlayer2 = new TeamPlayer();
                $teamPlayer2->team_id = $team->id;
                $teamPlayer2->player_id = $players[1]->id;
                $teamPlayer2->status = 1;

                if (!$teamPlayer1->save() || !$teamPlayer2->save()) {
                    \DB::rollBack();

                    $data['text'] = 'Opslaan van teamspeler mislukt. Probeer het opnieuw.';
                    sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

                    return new JsonResponse([
                        'msg' => $data['text'],
                        'errors' => new \StdClass(),
                    ]);
                }
            } else {
                $team = Team::find($teamPlayer->team_id);
            }
        }

        if (sizeof(get_object_vars($team)) === 0) {
            $data['text'] = 'Team niet gevonden.';
            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            return new JsonResponse([
                'msg' => $data['text'],
                'errors' => new \StdClass(),
            ]);
        }

        $eventTeam = new EventTeam();
        $eventTeam->event_id = $event->id;
        $eventTeam->team_id = $team->id;

        if (!$eventTeam->save()) {
            \DB::rollBack();

            $data['text'] = 'Opslaan van matchteam mislukt. Probeer het opnieuw.';
            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            return new JsonResponse([
                'msg' => $data['text'],
                'errors' => new \StdClass(),
            ]);
        }
    }

    if (isset($eventInitiation)) {
        $eventInitiation->event_id = $event->id;
        $eventInitiation->save();
    }

    \DB::commit();

    if (!$random) {
        $responseText .= "\n*Zelf samengestelde match*";
    }

    $responseText .= "\n<" . env('APP_URL') . '/?match=' . $event->id . '|Match ' . $event->id . '>';

    $attachments[] = [
        'fallback' => "\n<" . env('APP_URL') . '/results|Resultaten voor deze match toevoegen>',
        'actions' => [
            [
                'type' => 'button',
                'text' => 'Resultaten toevoegen',
                'url' => env('APP_URL') . '/results',
                'style' => 'primary',
            ],
            [
                'type' => 'button',
                'text' => 'Nieuwe match aanmaken',
                'url' => env('APP_URL') . '/match',
            ],
        ],
    ];

    $data = [
        'response_type' => 'in_channel',
        'username' => 'Matchbot',
        'icon_url' => asset('assets/img/matchbot-icon.jpg'),
        'text' => $responseText,
        'attachments' => $attachments,
    ];

    sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

    return new JsonResponse([
        'msg' => 'Match is aangemaakt.',
    ]);
}
