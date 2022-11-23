<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TwitchController extends Controller
{
    /**
     * Get access code.
     *
     * @param string $code
     * @return array $data
     */
    public static function oauth($code)
    {
        // Get Access Token
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => env('APP_URL'),
        ])
            ->throw();
        $data = $response->json();
        return $data['access_token'];
    }

    /**
     * Validate token.
     *
     * @param null
     * @return bool
     */
    public static function validateToken($token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Client-Id' => env('TWITCH_CLIENT_ID'),
        ])
            ->get('https://id.twitch.tv/oauth2/validate')
            ->throw();
        $data = $response->json();
        return $data['user_id'];
    }

    /**
     * Get user by twitch_id
     *
     * @param string $twitch_id
     * @param string $access_token
     * @return array $user_data
     */
    public static function getTwitchUserByID($access_token, $twitch_id)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Client-Id' => env('TWITCH_CLIENT_ID'),
        ])
            ->get('https://api.twitch.tv/helix/users?id=' . $twitch_id)
            ->throw();
        $data = $response->json();
        return $data['data'][0];
    }

    /**
     * Get followed streams
     *
     * @param null
     * @return array $user_data
     */
    public static function getTwitchFollowedStreams()
    {
        $streams = [];
        $user = Auth::user();
        $cursor = true;

        self::validateToken($user->access_token);

        while (!!$cursor) {
            $uri = 'https://api.twitch.tv/helix/streams/followed?user_id=' . $user->twitch_id;
            if (gettype($cursor) == 'string') {
                $uri = $uri . '&after=' . $cursor;
            }
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $user->access_token,
                    'Client-Id' => env('TWITCH_CLIENT_ID'),
                ])
                    ->get($uri)
                    ->throw();
                $data = $response->json();
                $streams[] = $data['data'];
                $cursor = $data['pagination']['cursor'] ?? false;
            } catch (RequestException $e) {
                Log::error($e);
                $cursor = false;
            }
        }

        return $streams;
    }

}
