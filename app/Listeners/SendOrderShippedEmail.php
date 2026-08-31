<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Notifications\OrderShippedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderShippedEmail implements ShouldQueue
{
    public function handle(OrderShipped $event): void
    {
        Notification::route('mail', $event->order->email)
            ->notify(new OrderShippedNotification($event->order));
    }
}
