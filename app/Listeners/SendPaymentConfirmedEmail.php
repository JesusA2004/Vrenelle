<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendPaymentConfirmedEmail implements ShouldQueue
{
    public function handle(PaymentConfirmed $event): void
    {
        Notification::route('mail', $event->order->email)
            ->notify(new PaymentConfirmedNotification($event->order));
    }
}
