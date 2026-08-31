<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderReceivedEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        Notification::route('mail', $event->order->email)
            ->notify(new OrderReceivedNotification($event->order));
    }
}
