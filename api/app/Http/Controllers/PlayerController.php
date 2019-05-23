<?php

namespace App\Http\Controllers;

use App\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions for players
 */
class PlayerController extends Controller
{
    /**
     * Add a new player.
     *
     * @param  Request $request the request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function addPlayer(Request $request): JsonResponse
    {
        // Check if player already exists
        $player = Player::where('user_id', $request->input('id'))
            ->first();

        if (isset($player)) {
            return new JsonResponse([
                'msg' => 'Speler is al toegevoegd.',
                'errors' => new \StdClass(),
            ], Response::HTTP_CONFLICT);
        }

        // Get Slack user
        $slackUser = getSlackUser((int) $request->input('id'), env('SLACK_TOKEN'));

        if (sizeof($slackUser) === 0) {
            return new JsonResponse([
                'msg' => 'Gebruiker is niet gevonden.',
                'errors' => new \StdClass(),
            ], Response::HTTP_NOT_FOUND);
        }

        $player = new Player();
        $player->name = $slackUser->real_name;
        $player->user_id = $slackUser->id;
        $player->username = $slackUser->name;
        $player->status = 1;
        $player->default = $request->input('default');

        if (!$player->save()) {
            return new JsonResponse([
                'msg' => 'Er is iets fout gegaan bij het opslaan van de speler.',
                'errors' => new \StdClass(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($player->default) {
            $msg = 'Speler ' . $player->name . ' is toegevoegd aan de spelerslijst.';
        } else {
            $msg = 'Speler ' . $player->name . ' is verwijderd van de spelerslijst.';
        }

        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => $msg,
        ];

        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        return new JsonResponse([
            'msg' => $msg,
        ]);
    }

    /**
     * Update a player.
     *
     * @param  Request $request the request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function updatePlayer(Request $request): JsonResponse
    {
        $player = Player::where('user_id', Request::input('id'))
            ->first();

        if (!isset($player)) {
            return $this->addPlayer($request);
        } else {
            $player->default = Request::input('default');

            if (!$player->save()) {
                return new JsonResponse([
                    'msg' => 'Er is iets fout gegaan bij het wijzigen van de speler.',
                    'errors' => new \StdClass(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($player->default) {
                $msg = 'Speler ' . $player->name . ' is toegevoegd aan de spelerslijst.';
            } else {
                $msg = 'Speler ' . $player->name . ' is verwijderd van de spelerslijst.';
            }

            $data = [
                'response_type' => 'in_channel',
                'username' => 'Matchbot',
                'icon_url' => asset('assets/img/matchbot-icon.jpg'),
                'text' => $msg,
            ];

            sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

            return new JsonResponse([
                'msg' => $msg,
            ]);
        }
    }

}
