<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CampaignContact;

class ContactStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contact;
    public $campaignId;
    public $status;
    public $metrics;

    /**
     * Create a new event instance.
     */
    public function __construct(CampaignContact $contact)
    {
        $this->contact = $contact;
        $this->campaignId = $contact->campaign_id;
        $this->status = $contact->status;
        $this->metrics = [
            'duration' => $contact->metrics->duration ?? null,
            'cause' => $contact->metrics->cause ?? null,
            'updated_at' => now()->toDateTimeString()
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('campaign.'.$this->campaignId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'contact.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'contact_id' => $this->contact->id,
            'phone_number' => $this->contact->phone_number,
            'status' => $this->status,
            'metrics' => $this->metrics,
        ];
    }
}