<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TravelRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int    $travelId,
        public readonly string $personelName,
        public readonly string $destination,
        public readonly string $departure,
        public readonly string $returnDate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'travel_request',
            'icon'         => '✈️',
            'title'        => 'Yeni Seyahat Talebi',
            'message'      => "{$this->personelName} — {$this->destination}",
            'subtitle'     => "{$this->departure} → {$this->returnDate}",
            'action_url'   => "/admin/travel/{$this->travelId}",
            'action_label' => 'Seyahati İncele',
            'model_id'     => $this->travelId,
            'model_type'   => 'travel_request',
            'color'        => 'cyan',
        ];
    }
}
