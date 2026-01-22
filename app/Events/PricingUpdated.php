<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PricingUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $unitType; // null means all unit types affected

    public function __construct($unitType = null)
    {
        $this->unitType = $unitType;
    }

    public function broadcastOn()
    {
        return new Channel('pricing');
    }

    public function broadcastAs()
    {
        return 'pricing.updated';
    }
}