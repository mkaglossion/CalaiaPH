<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $staycationId;
    public $date;
    public $availableQuantity;

    public function __construct($staycationId, $date, $availableQuantity)
    {
        $this->staycationId = $staycationId;
        $this->date = $date;
        $this->availableQuantity = $availableQuantity;
    }

    public function broadcastOn()
    {
        return new Channel('inventory');
    }

    public function broadcastAs()
    {
        return 'inventory.updated';
    }
}
