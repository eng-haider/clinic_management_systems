<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Reservation;
class ReserverationAdded implements  ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   public $reserveration;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
       $this->reserveration=Reservation::first();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        return new Channel('reserveration');
       // return new PrivateChannel('channel-name');
    }
}
