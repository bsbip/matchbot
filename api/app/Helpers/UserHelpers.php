<?php

use App\Player;
use App\EventPlayer;

/**
 * Get the available users.
 *
 * @return array
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getAvailableUsers(): array
{
    $availableUsers = Player::where('default', true)
        ->pluck('username')
        ->toArray();

    return $availableUsers;
}

/**
 * Save an event player.
 *
 * @param  int $eventId the event id
 * @param  int $playerId the player id
 * @param  int $number the number of the player for this event
 *
 * @return bool true on success, false on failure
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function saveEventPlayer(int $eventId, int $playerId, int $number): bool
{
    $eventPlayer = new EventPlayer();
    $eventPlayer->event_id = $eventId;
    $eventPlayer->player_id = $playerId;
    $eventPlayer->number = $number;

    if ($eventPlayer->save()) {
        return true;
    }

    return false;
}

/**
 * Get Slack users for a match.
 *
 * @param  string $text the text with filter options
 *
 * @return array
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getActiveUsers(string $text): array
{
    $activeUsers = [];
    $availableUsers = getAvailableUsers();

    $users = getSlackUserList(env('SLACK_TOKEN'));

    // Check if users are added/removed
    $textStartAdd = strpos($text, 'add ');
    $textStartRemove = strpos($text, 'remove ');

    if ($textStartAdd !== false) {
        if (($textStartAdd < $textStartRemove) && $textStartRemove !== false) {
            $textAdd = substr($text, $textStartAdd + 3, strpos($text, 'remove') - 1);
        } else {
            $textAdd = substr($text, $textStartAdd + 3);
        }

        $addUsers = explode(' ', $textAdd);
        foreach ($addUsers as $addUser) {
            $start = strpos($addUser, '@');
            if ($start !== false) {
                $availableUsers[] = substr($addUser, $start + 1);
            }
        }
    }

    if ($textStartRemove !== false) {
        if (($textStartRemove < $textStartAdd) && $textStartAdd !== false) {
            $textAdd = substr($text, $textStartRemove + 6, strpos($text, 'add') - ($textStartRemove + 6));
        } else {
            $textAdd = substr($text, $textStartRemove + 6);
        }

        $addUsers = explode(' ', $textAdd);
        foreach ($addUsers as $addUser) {
            $start = strpos($addUser, '@');
            if ($start !== false) {
                $userKey = array_search(substr($addUser, $start + 1), $availableUsers);
                if ($userKey !== false) {
                    unset($availableUsers[$userKey]);
                }
            }
        }
    }

    foreach ($users as $key => $value) {
        if (in_array($value->name, $availableUsers)) {
            if (isset($value->presence)) {
                if ($value->presence == 'active') {
                    $activeUsers[$key] = $value;
                } elseif (strpos($text, 'online') === false) {
                    $activeUsers[$key] = $value;
                }
            }
        }
    }

    return $activeUsers;
}

/**
 * Get users by Slack id.
 *
 * @param  array $userIds the Slack user id's
 *
 * @return array
 *
 * @author Ramon Bakker <ramonbakker@rambit.nl>
 */
function getUsersBySlackId(array $userIds): array
{
    $users = [];
    $slackUsers = getSlackUserList(env('SLACK_TOKEN'));

    foreach ($slackUsers as $key => $value) {
        if (in_array($value->id, $userIds)) {
            // Add user to list
            $users[] = $value;
        }
    }

    return $users;
}
