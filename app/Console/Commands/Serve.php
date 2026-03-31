<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class Serve extends BaseServeCommand
{
    /**
     * Get the port for the command.
     *
     * Override default port to 80 so `php artisan serve` uses http://127.0.0.1
     *
     * @return string
     */
    protected function port()
    {
        $port = $this->input->getOption('port');

        if (is_null($port)) {
            [, $port] = $this->getHostAndPort();
        }

        // Default to 80 instead of Laravel's 8000
        $port = $port ?: 80;

        return $port + $this->portOffset;
    }
}
