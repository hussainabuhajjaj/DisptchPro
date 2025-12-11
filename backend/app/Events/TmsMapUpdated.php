<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TmsMapUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public ?int $loadId;
    public array $payload;

    public function __construct(string $type = 'refresh', ?int $loadId = null, array $payload = [])
    {
        $this->type = $type;
        $this->loadId = $loadId;
        $this->payload = $payload;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('tms-loads');
    }

    public function broadcastAs(): string
    {
        return 'MapUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'load_id' => $this->loadId,
            'payload' => $this->payload,
        ];
    }
}
