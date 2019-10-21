<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * WIP: Class still needs work before able to use in production
 *
 * @author Roy Freij <info@royfreij.nl>
 * @version 1.0.0
 */
class SlackService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var string
     */
    private $slackToken;

    /**
     * @var int
     */
    const ACTIVE = 1;

    /**
     * Create a new instance of SlackService
     *
     * @param Client $guzzle
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->webhookUrl = Config::get('slack.webhook_url');
        $this->apiUrl = Config::get('slack.api_url');
        $this->slackToken = Config::get('slack.token');
    }

    /**
     * Get Slack user list
     *
     * @return array|object
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function getSlackUserList(): Collection
    {
        try {
            $response = $this->client->get("{$this->apiUrl}/users.list", [
                'query' => [
                    'token' => $this->slackToken,
                    'pretty' => self::ACTIVE,
                    'presence' => self::ACTIVE,
                ],
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return new Collection([]);
        }

        $responseBody = json_decode($response->getBody());

        return isset($responseBody->members) ? new Collection($responseBody->members) : new Collection([]);
    }
}
