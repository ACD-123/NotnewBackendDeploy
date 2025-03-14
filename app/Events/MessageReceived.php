<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user;
    private $chat_id;
    private $product_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $chat_id, $product_id)
    {
        $this->user = $user;
        $this->chat_id = $chat_id;
        $this->product_id = $product_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('messages.' . $this->user->id . '.' . $this->product_id);
    }

    public function broadcastWith()
    {
        return ['messages' => Message::select('*')
        ->where("product_id", $this->product_id)
        ->where("chat_id", $this->chat_id)
        ->orderBy('created_at')
        ->get()];
    }

    public function broadcastAs()
    {
        return 'MessageReceived';
    }

    public static function trigger(User $user, $chat_id, $product_id)
    {
        // event(new self($user, $chat_id, $product_id));
//        try {
//            event(new self($user));
//        } catch (\Exception $ex) {
//            Log::error(__CLASS__);
//        }
    }
}
