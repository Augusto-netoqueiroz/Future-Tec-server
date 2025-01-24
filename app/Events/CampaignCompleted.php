<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Campaign;

class CampaignCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $campaign;
    public $summary;

    /**
     * Create a new event instance.
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->summary = [
            'total_contacts' => $campaign->contacts()->count(),
            'answered_calls' => $campaign->contacts()->where('status', 'answered')->count(),
            'failed_calls' => $campaign->contacts()->where('status', 'not_answered')->count(),
            'completion_time' => now()->toDateTimeString(),
            'duration' => now()->diffInMinutes($campaign->updated_at)
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
            new Channel('campaign.'.$this->campaign->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'campaign.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'campaign_id' => $this->campaign->id,
            'name' => $this->campaign->name,
            'summary' => $this->summary
        ];
    }
}