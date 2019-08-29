<?php

namespace App\Http\Controllers;

use App\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Actions for players
 */
class PlayerController extends Controller
{
    /**
     * Adds or update player.
     *
     * @param  Request $request the request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function __invoke(Request $request, string $playerId): JsonResponse
    {
        $player = Player::where('user_id', $playerId)->first();

        if (!isset($player)) {
            $slackUser = getSlackUser($playerId);

            if (!isset($slackUser->id)) {
                return new JsonResponse([
                    'message' => 'Gebruiker is niet gevonden.',
                    'errors' => [],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $player = new Player([
                'name' => $slackUser->real_name,
                'user_id' => $slackUser->id,
                'username' => $slackUser->name,
                'status' => 1,
            ]);
        }

        $player->default = $request->input('default');
        $player->save();

        $message = "Speler {$player->name} is %s de spelerlijst.";
        $message = sprintf($message, $player->default ? 'toegevoegd aan' : 'verwijderd van');

        // TODO Extract into service
        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => $message,
        ];

        //  TODO export env to config files
        sendSlackResponse($data, env('SLACK_WEBHOOK_URL'));

        return new JsonResponse([
            'message' => $message,
        ]);
    }
}
