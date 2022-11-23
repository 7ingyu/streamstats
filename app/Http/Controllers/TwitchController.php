<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
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
        Log::info('oath: ' . json_encode($data));
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
        Log::info('token: ' . $token);
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

}
