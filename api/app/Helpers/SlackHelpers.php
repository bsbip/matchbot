<?php

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
        // Failed to get data
        return false;
    }

    return true;
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
        $res = $client->get('https://slack.com/api/users.list', [
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
 * @param  string $token the Slack token
 * @return object
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getSlackUser(string $userId, string $token): object
{
    $client = new \GuzzleHttp\Client();

    try {
        $res = $client->get('https://slack.com/api/users.info', [
            'query' => [
                'token' => $token,
                'pretty' => 1,
                'user' => $userId,
            ],
        ]);
    } catch (Exception $e) {
        // Failed to get data
        return [];
    }

    $data = json_decode($res->getBody());

    if (!isset($data->user)) {
        return [];
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
