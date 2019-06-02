<?php
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Send a Slack response.
 *
 * @param  array $data the data to send
 * @param  string $url the (webhook) url
 *
 * @return bool true on success, false on failure
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function sendSlackResponse(array $data, string $url): bool
{
    $client = new \GuzzleHttp\Client();

    try {
        $client->request('POST', $url, [
            'json' => $data,
        ]);
    } catch (Exception $e) {
        Log::error($e);

        return false;
    }

    return true;
}

/**
 * Send a Slack message.
 *
 * @param array $data
 * @param string $method the Slack chat method type
 *
 * @return ResponseInterface
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function sendSlackMessage($data = [], $method = 'postMessage'): ResponseInterface
{
    $client = new \GuzzleHttp\Client();

    try {
        $res = $client->post(env('SLACK_API_URL') . '/chat.' . $method, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . env('SLACK_TOKEN'),
            ],
            'json' => $data,
        ]);
    } catch (Exception $e) {
        Log::error($e);
    }

    return $res;
}

/**
 * Get Slack user list.
 *
 * @param  string $token the Slack token
 *
 * @return array
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getSlackUserList(string $token): array
{
    $client = new \GuzzleHttp\Client();

    try {
        $res = $client->get(env('SLACK_API_URL') . '/users.list', [
            'query' => [
                'token' => $token,
                'pretty' => 1,
                'presence' => 1,
            ],
        ]);
    } catch (Exception $e) {
        // Failed to get data
        return [];
    }

    $members = json_decode($res->getBody());

    // Check if data is available
    if (!isset($members->members)) {
        return [];
    }

    return json_decode($res->getBody())->members;
}

/**
 * Get a Slack user.
 *
 * @param  string $userId the id of the user
 * @return object
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getSlackUser(string $userId): object
{
    $client = new \GuzzleHttp\Client();

    $res = $client->get(env('SLACK_API_URL') . '/users.info', [
        'query' => [
            'token' => env('SLACK_TOKEN'),
            'pretty' => 1,
            'user' => $userId,
        ],
    ]);

    $data = json_decode($res->getBody());

    if (!isset($data->user)) {
        throw new HttpException(Response::HTTP_NOT_FOUND);
    }

    return $data->user;
}

/**
 * Create an error message.
 *
 * @param  array $errors the errors
 *
 * @return array
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function createErrorMessage(array $errors): array
{
    $errorsText = '';

    foreach ($errors as $error) {
        $errorsText .= $error . "\n";
    }

    $data = [
        'response_type' => 'in_channel',
        'username' => 'Matchbot',
        'icon_url' => asset('assets/img/matchbot-icon.jpg'),
        'text' => $errorsText,
    ];

    return $data;
}
