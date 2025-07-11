<?php

namespace App\Events\Customer;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class CustomerUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $name;
    public int $userId;
    public string $customerName;

    public function __construct(string $name, int $userId, string $customerName)
    {
        $this->name = $name;
        $this->userId = $userId;
        $this->customerName = $customerName;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('customer-updates');
    }

    public function broadcastAs(): string
    {
        return 'customer.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => $this->name,
            'customer' => $this->customerName,
        ];
    }
}

