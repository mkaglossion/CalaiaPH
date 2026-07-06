<?php

namespace App\Console\Commands;

use App\Helpers\VersionHelper;
use Illuminate\Console\Command;

class SyncAppVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write the resolved application version to storage for environments without git metadata';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = VersionHelper::syncStoredVersion();

        $this->info("Version synced to v{$version}");

        return self::SUCCESS;
    }
}
