<?php

namespace App\Console\Commands;

use App\Http\Controllers\TwitchController;
use App\Models\TopStream;
use Illuminate\Console\Command;
use Carbon\Carbon;

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
        $this->info(now());
        // Fetch data
        $this->info('Fetching data');
        $streams = TwitchController::getTopStreams();

        // Shuffle data
        $this->info('Shuffling data');
        shuffle($streams);

        // Format data for insert
        $records = [];
        $this->info('Formatting data');
        foreach ($streams as $stream) {
            $records[] = [
                'stream_id' => $stream['id'],
                'channel_name' => $stream['user_name'],
                'stream_title' => $stream['title'],
                'game_name' => $stream['game_name'],
                'viewers' => $stream['viewer_count'],
                'tags' => join(', ', $stream['tag_ids']),
                'start_time' => Carbon::parse($stream['started_at']),
            ];
        }

        // Truncate table
        $this->info('Truncating table');
        TopStream::truncate();

        // Insert new data
        $this->info('Inserting data');
        TopStream::insert($records);

        $this->info(collect($records)->count() . ' records inserted');
    }
}
