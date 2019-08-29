<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

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
    private $guzzle;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @var string
     */
    private $slackToken;

    /**
     * Create a new instance of SlackService
     *
     * @param Client $guzzle
     *
     * @author Roy Freij <info@royfreij.nl>
     * @version 1.0.0
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
        $this->webhookUrl = Config::get('slack.webhook_url');
        $this->slackToken = Config::get('slack.token');
    }
}
