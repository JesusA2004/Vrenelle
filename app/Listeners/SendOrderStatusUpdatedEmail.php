<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderStatusUpdatedEmail implements ShouldQueue
{
    public function handle(OrderStatusUpdated $event): void
    {
        Notification::route('mail', $event->order->email)
            ->notify(new OrderStatusUpdatedNotification($event->order));
    }
}
