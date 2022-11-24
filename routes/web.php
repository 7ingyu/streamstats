<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwitchController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Home', [
        'clientId' => env('TWITCH_CLIENT_ID'),
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'appUrl' => env('APP_URL'),
        'csrf' => csrf_token(),
    ]);
})->name('home');

Route::get('/dashboard', [TwitchController::class, 'render'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/oauth', [AuthenticatedSessionController::class, 'store'])->name('twitch.oauth');

Route::middleware('twitch')
    ->get('/twitch/follows', [TwitchController::class, 'getFollowedStreams'])
    ->name('twitch.follows');

require __DIR__.'/auth.php';
