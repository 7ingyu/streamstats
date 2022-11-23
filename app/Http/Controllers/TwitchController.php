<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TwitchController extends Controller
{
    /**
     * Authorize and save user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return
     */
    public function oauth(Request $request)
    {
        $input = $request->collect();
        Log::info($input);
        return;
    }

}
