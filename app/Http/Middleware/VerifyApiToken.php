<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tokens = [
            env('APP_TOKEN1'),
        ];

        // Check application token
        if (!in_array($request->input('token'), $tokens) && !in_array($request->header('token'), $tokens)) {
            return new JsonResponse([
                'msg' => 'No valid token provided.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
