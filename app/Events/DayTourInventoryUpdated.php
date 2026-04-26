<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DayTourInventoryUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return [
            new Channel('daytour-inventory'),
            new Channel('daytour-inventory.' . $this->data['day_tour_id'])
        ];
    }

    public function broadcastAs()
    {
        return 'daytour.inventory.updated';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
