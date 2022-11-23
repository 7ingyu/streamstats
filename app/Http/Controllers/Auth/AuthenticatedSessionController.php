<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TwitchController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        [
            'code' => $code,
            'scope' => $scope,
            'state' => $state
        ] = $request->all();

        $csrf = csrf_token();

        // CSRF doesn't match
        if ($csrf != ($state ?? false)) {
            Log::error($csrf . ' didn\'t match ' . $state);
            return $this->destroy($request);
        }

        // Get Access Token
        $access_token = null;
        try {
            $access_token = TwitchController::oauth($code);
        } catch (RequestException $e) {
            Log::error($e);
            return $this->destroy($request);
        }

        // Validate token
        $twitch_id = null;
        try {
            $twitch_id = TwitchController::validateToken($access_token);
        } catch (RequestException $e) {
            Log::error($e);
            return $this->destroy($request);
        }

        // Get user info
        $email = null;
        $username = null;
        try {
            [
                'email' => $email,
                'login' => $username,
            ] = TwitchController::getTwitchUserByID($access_token, $twitch_id);
        } catch (RequestException $e) {
            Log::error($e);
            return $this->destroy($request);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->destroy($request);
        }


        // Save data to user
        $user = User::updateOrCreate(
            ['twitch_id' => $twitch_id],
            [
                'access_token' => $access_token,
                'email' => $email,
                'username' => $username,
            ]
        );

        Auth::login($user);
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
