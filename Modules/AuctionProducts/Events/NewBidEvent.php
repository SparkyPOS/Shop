<?php

namespace Modules\AuctionProducts\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewBidEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->message = $data;

    }

    public function broadcastAs () {
        return 'auction_event_'.$this->message['id'];
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastWith () {
        return [
            'message' => $this->message['message'],
        ];
    }

    public function broadcastOn () {
        return new Channel('auction_bid_'.$this->message['id']);
    }
}
