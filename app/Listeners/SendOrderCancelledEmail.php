<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderCancelledEmail implements ShouldQueue
{
    public function handle(OrderCancelled $event): void
    {
        Notification::route('mail', $event->order->email)
            ->notify(new OrderCancelledNotification($event->order));
    }
}
