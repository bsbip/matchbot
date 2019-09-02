<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerifyAppToken
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
        $enable = false;
        $tokens = [
            env('APP_TOKEN1'),
        ];

        // Check application token
        if (!in_array($request->input('token'), $tokens) && $enable) {
            return new Response(view('errors.403'));
        }

        return $next($request);
    }
}
