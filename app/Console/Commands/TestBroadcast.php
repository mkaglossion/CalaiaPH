<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:test {channel=public-test} {message=test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test broadcast event to the given channel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $channel = (string) $this->argument('channel');
        $message = (string) $this->argument('message');

        event(new \App\Events\BroadcastTest($message, $channel));

        $this->info("Broadcast test event sent to channel: {$channel}");

        return 0;
    }
}
