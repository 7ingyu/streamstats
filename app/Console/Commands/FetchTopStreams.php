<?php

namespace App\Console\Commands;

use App\Http\Controllers\TwitchController;
use Illuminate\Console\Command;

class FetchTopStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:top';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch top 100 streams and save';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $streams = TwitchController::getTopStreams();
        dd(collect($streams)->count());
    }
}
