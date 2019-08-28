<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySlackToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check Slack token
        $slackTokenValid = $this->checkSlackToken($request->input('token', ''), [
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN1'),
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN2'),
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN3'),
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN4'),
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN5'),
            env('SLACK_SLASH_COMMAND_MATCH_TOKEN6'),
        ]);

        if ($slackTokenValid !== true && !env('APP_DEBUG')) {
            return $slackTokenValid;
        }

        return $next($request);
    }

    /**
     * Check the Slack token.
     *
     * @param  string $inputToken the Slack token
     * @param  array $tokens valid tokens
     *
     * @return JsonResponse|bool
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    private function checkSlackToken(string $inputToken, array $tokens)
    {
        if (!in_array($inputToken, $tokens)) {
            return new JsonResponse([
                'response_type' => 'in_channel',
                'color' => '#ff0000',
                'text' => 'Geen toegang!',
                'code' => Response::HTTP_FORBIDDEN,
                'error_text' => 'No valid token provided!',
            ]);
        }

        return true;
    }
}
