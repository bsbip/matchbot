<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySlackSignature
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
        $parts = [
            env('SLACK_API_VERSION'),
            $request->header('X-Slack-Request-Timestamp'),
            $request->getContent(),
        ];
        $computedSignature = 'v0=' . hash_hmac('sha256', implode(':', $parts), env('SLACK_SIGNING_SECRET'));

        if (!hash_equals($computedSignature, $request->header('X-Slack-Signature'))) {
            Log::info('Invalid request signature: ' . $computedSignature . ' does not match ' . $request->header('X-Slack-Signature'));

            return new JsonResponse([
                'msg' => 'Request signature is invalid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
