<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastTest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public string $channelName;

    /**
     * Create a new event instance.
     */
    public function __construct(string $message = 'test', string $channel = 'public-test')
    {
        $this->message = $message;
        $this->channelName = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel
    {
        // only supports public channels for this test command
        return new Channel($this->channelName);
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }

    /**
     * Optional: custom event name.
     */
    public function broadcastAs(): string
    {
        return 'broadcast.test';
    }
}
