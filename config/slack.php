<?php

return [
    'webhook_url' => env('SLACK_WEBHOOK_URL'),
    'token' => env('SLACK_TOKEN'),
    'slash_command_token' => [
        'one' => env('SLACK_SLASH_COMMAND_MATCH_TOKEN1'),
    ],
    'api_url' => env('SLACK_API_URL'),
    'api_version' => env('SLACK_API_VERSION'),
    'initiation_mention' => env('SLACK_INITIATION_MENTION'),
    'signing_secret' => env('SLACK_SIGNING_SECRET'),
];
