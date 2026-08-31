<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return (new MailMessage)
            ->subject("Pago confirmado — pedido {$order->order_number}")
            ->greeting('Tu pago ha sido confirmado.')
            ->line("Hemos confirmado el pago de tu pedido {$order->order_number} por ".Money::format($order->total, $order->currency).'.')
            ->line('Comenzaremos a preparar tu pieza con el máximo cuidado.')
            ->action('Ver mi pedido', url("/mi-cuenta/pedidos/{$order->order_number}"));
    }
}
