<?php

return [
    'mention' => env('SLACK_INITIATION_MENTION'),
    'wait_times' => [
        1,
        5,
        10,
        15,
        30,
        60,
    ],
    'scheduled_message_offset' => 1,
];
