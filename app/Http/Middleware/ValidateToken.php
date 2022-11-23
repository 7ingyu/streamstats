<?php

namespace App\Http\Middleware;

use App\Http\Controllers\TwitchController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class validateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $user = Auth::user();
        $twitch_id = null;

        if (!$user) {
          return (new AuthenticatedSessionController)->destroy($request);
        }

        try {
          $twitch_id = TwitchController::validateToken($user->access_token);
        } catch (RequestException $e) {
          return (new AuthenticatedSessionController)->destroy($request);
        }

        if ($twitch_id != $user->twitch_id) {
          return (new AuthenticatedSessionController)->destroy($request);
        }

        return $next($request);
    }
}
