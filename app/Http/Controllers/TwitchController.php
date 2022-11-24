<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\TopStream;
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
    public static function getFollowedStreams()
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

    /**
     * Get top streams
     *
     * @param null
     * @return array $user_data
     */
    public static function getTopStreams()
    {
        // Get Access Token
        $auth_res = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
        ])
            ->throw();
        $data = $auth_res->json();
        $access_token = $data['access_token'];

        $stream_res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Client-Id' => env('TWITCH_CLIENT_ID'),
        ])
            ->get('https://api.twitch.tv/helix/streams?first=100')
            ->throw();
        $data = $stream_res->json();
        return $data['data'];
    }

    /**
     * Get top streams
     *
     * @param array $tags
     * @return array $user_data
     */
    public static function getTagNames($tags)
    {
        // Get Access Token
        $auth_res = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
        ])
            ->throw();
        $data = $auth_res->json();
        $access_token = $data['access_token'];

        $uri = 'https://api.twitch.tv/helix/tags/streams?';
        foreach ($tags as $tag) {
            $uri = $uri . 'tag_id=' . $tag . '&';
        }
        $uri = substr($uri, 0, -1); // remove extra &
        $tag_data = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Client-Id' => env('TWITCH_CLIENT_ID'),
        ])
            ->get($uri)
            ->throw();
        $tag_data = $tag_data->json();
        $names = [];
        foreach ($tag_data['data'] as $data) {
            $names[] = $data['localization_names']['en-us'];
        }
        return $names;
    }

    public function render () {
        $topStreams = TopStream::all();
        $userStreams = self::getFollowedStreams();

        // Total number of streams for each game
        $streamsPerGame = TopStream::selectRaw('count(game_name) as streams, game_name')
            ->groupBy('game_name')
            ->pluck('streams', 'game_name');

        // Top games by viewer count for each game
        $gamesByViewers = TopStream::groupBy('game_name')
            ->selectRaw('sum(viewers) as viewers, game_name')
            ->pluck('viewers', 'game_name');

        // Median number of viewers for all streams
        $medianViews = TopStream::select('viewers')
            ->pluck('viewers')
            ->median();

        // List of top 100 streams by viewer count that can be sorted asc & desc
        $topStreamsAsc = TopStream::orderBy('viewers', 'asc')->get();
        $topSteamsDesc = TopStream::orderBy('viewers', 'desc')->get();

        // Total number of streams by their start time (rounded to the nearest hour)
        $streamsPerHr = TopStream::selectRaw('count(*) as streams, date_format(start_time, "%Y-%m-%d %H:00") as time')
            ->groupByRaw('date_format(start_time, "%Y-%m-%d %H:00")')
            ->pluck('streams', 'time');

        // Which of the top 1000 streams is the logged in user following?
        $stream_ids = [];
        foreach ($userStreams as $user_stream) {
            $stream_ids[] = $user_stream[0]['id'];
        }
        $followedTopStreams = TopStream::whereIn('stream_id', $stream_ids)->get();

        // How many viewers does the lowest viewer count stream that the logged in user is following need to gain in order to make it into the top 100?
        $needForTop100 = null;
        if (collect($user_stream)->count() > 0) {
            $lowest = INF;
            foreach ($userStreams as $user_stream) {
                $viewers = $user_stream[0]['viewer_count'];
                $lowest =  $viewers < $lowest ?  $viewers: $lowest;
            }
            $lowest_in_top = TopStream::orderBy('viewers', 'asc')->first()->viewers;
            $diff = $lowest_in_top - $lowest;
            $needForTop100 = $diff < 0 ? 0 : $diff;
        }

        // Which tags are shared between the user followed streams and the top 1000 streams?
        // Also make sure to translate the tags to their respective name.
        $user_tags = [];
        foreach ($userStreams as $user_stream) {
            $user_tags = array_merge($user_tags, $user_stream[0]['tag_ids'] ?? []);
        }
        $user_tags = array_unique($user_tags);
        $shared_tags = [];
        foreach ($topStreams as $topStream) {
            $stream_tags = collect(explode(', ', $topStream->tags));
            $intersection = $stream_tags->intersect($user_tags);
            if ($intersection->count()) {
                $shared_tags = array_merge($shared_tags, $intersection->all());
            }
        }
        $shared_tags = array_unique($shared_tags);
        $sharedTags = self::getTagNames($shared_tags);

        return Inertia::render('Dashboard', [
            'userStreams' => $userStreams,
            'topStreams' => $topStreams,
            'sharedTags' => $sharedTags,
            'needForTop100' => $needForTop100,
            'followedTopStreams' => $followedTopStreams,
            'streamsPerHr' => $streamsPerHr,
            'topStreamsAsc' => $topStreamsAsc,
            'topSteamsDesc' => $topSteamsDesc,
            'medianViews' => $medianViews,
            'gamesByViewers' => $gamesByViewers,
            'streamsPerGame' => $streamsPerGame,
        ]);
    }
}
