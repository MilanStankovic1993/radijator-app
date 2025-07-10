<?php

namespace App\Events\Customer;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CustomerCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $user;
    public string $customer;

    public function __construct(string $user, string $customer)
    {
        $this->user = $user;
        $this->customer = $customer;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('customer-updates');
    }

    public function broadcastAs(): string
    {
        return 'customer.created';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => $this->user,
            'customer' => $this->customer,
        ];
    }
}
