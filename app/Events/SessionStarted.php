<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionName;
    public $status;
    public $message;

    /**
     * Cria uma nova instância de evento.
     *
     * @param  string  $sessionName
     * @param  string  $status
     * @param  string  $message
     * @return void
     */
    public function __construct($sessionName, $status, $message)
    {
        $this->sessionName = $sessionName;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Definir o canal de transmissão.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('session-channel');
    }

    /**
     * Definir os dados a serem transmitidos.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'sessionName' => $this->sessionName,
            'status' => $this->status,
            'message' => $this->message,
        ];
    }
}
