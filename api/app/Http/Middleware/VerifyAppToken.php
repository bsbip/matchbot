<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class VerifyAppToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $enable = false;
        $tokens = [
            env('APP_TOKEN1')
        ];

        // Check application token
        if (!in_array($request->input('token'), $tokens) && $enable) {
            return new Response(view('errors.403'));
        }

        return $next($request);
    }
}
