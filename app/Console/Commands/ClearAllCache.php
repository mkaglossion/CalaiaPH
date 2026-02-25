<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearAllCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all Laravel caches (view, cache, config, route, event) and rebuild for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Clearing all caches...');

        // Clear all caches
        $this->info('Clearing view cache...');
        Artisan::call('view:clear');
        
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');
        
        $this->info('Clearing config cache...');
        Artisan::call('config:clear');
        
        $this->info('Clearing route cache...');
        Artisan::call('route:clear');
        
        $this->info('Clearing event cache...');
        Artisan::call('event:clear');

        $this->info('✅ All caches cleared!');

        // Ask if user wants to rebuild cache for production
        if ($this->confirm('Do you want to rebuild cache for production?', true)) {
            $this->info('⚡ Rebuilding caches for production...');
            
            Artisan::call('config:cache');
            $this->info('✓ Config cached');
            
            Artisan::call('route:cache');
            $this->info('✓ Routes cached');
            
            Artisan::call('view:cache');
            $this->info('✓ Views cached');
            
            Artisan::call('event:cache');
            $this->info('✓ Events cached');

            $this->info('🎉 All caches rebuilt for production!');
        }

        return Command::SUCCESS;
    }
}
