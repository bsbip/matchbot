<?php
use App\EventInitiation;
use App\EventInitiationScheduledMessage;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
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
 * Get the permalink of a Slack message.
 *
 * @param string $messageTs
 * @param string $channelId
 *
 * @return object
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getSlackMessagePermalink(string $messageTs, string $channelId): object
{
    try {
        $client = new \GuzzleHttp\Client();

        $res = $client->get(env('SLACK_API_URL') . '/chat.getPermalink', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                'Authorization' => 'Bearer ' . env('SLACK_TOKEN'),
            ],
            'query' => [
                'channel' => $channelId,
                'message_ts' => $messageTs,
            ],
        ]);
    } catch (Exception $e) {
        Log::error($e);
    }

    return json_decode($res->getBody($res));
}

/**
 * Schedule a Slack message for an event initiation.
 *
 * @param EventInitiation $eventInitiation
 * @param string $channelId
 *
 * @return void
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function scheduleSlackMessage(EventInitiation $eventInitiation, string $channelId): void
{
    $permalink = getSlackMessagePermalink($eventInitiation->message_ts, $channelId);
    $expireAt = Carbon::parse($eventInitiation->expire_at);

    // Check if time before expiration is not enough
    if ($expireAt->diffInMinutes(now()) < Config::get('initiation.scheduled_message_offset')) {
        return;
    }

    $res = sendSlackMessage([
        'channel' => $channelId,
        'text' => trans('event-initiation.initiation_expires_in_short_time', [
            'id' => "<{$permalink->permalink}|$eventInitiation->id>",
            'time' => $expireAt->toTimeString(),
        ]),
        'post_at' => $expireAt
            ->subMinutes(Config::get('initiation.scheduled_message_offset'))
            ->getTimestamp(),
    ], 'scheduleMessage');

    $responseBody = json_decode($res->getBody());

    if (!isset($responseBody->scheduled_message_id)) {
        return;
    }

    EventInitiationScheduledMessage::create([
        'scheduled_message_id' => $responseBody->scheduled_message_id,
        'channel_id' => $channelId,
        'event_initiation_id' => $eventInitiation->id,
    ]);
}

/**
 * Delete scheduled Slack messages for an event initiation.
 *
 * @param int $eventInitiationId
 *
 * @return void
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function deleteEventInitiationScheduledSlackMessages($eventInitiationId): void
{
    $scheduledMessages = EventInitiationScheduledMessage::where('event_initiation_id', $eventInitiationId)
        ->get();

    $client = new \GuzzleHttp\Client();

    foreach ($scheduledMessages as $scheduledMessage) {
        try {
            $client->post(env('SLACK_API_URL') . '/chat.deleteScheduledMessage', [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . env('SLACK_TOKEN'),
                ],
                'json' => [
                    'channel' => $scheduledMessage->channel_id,
                    'scheduled_message_id' => $scheduledMessage->scheduled_message_id,
                ],
            ]);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    EventInitiationScheduledMessage::where('event_initiation_id', $eventInitiationId)
        ->delete();
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
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $token,
            ],
            'query' => [
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
        'headers' => [
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . env('SLACK_TOKEN'),
        ],
        'query' => [
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
