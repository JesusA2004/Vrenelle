<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReceivedNotification extends Notification
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
            ->subject("Hemos recibido tu pedido {$order->order_number}")
            ->greeting("Gracias por tu compra, {$order->customer_name}.")
            ->line('Hemos recibido tu pedido y comenzaremos a prepararlo en cuanto se confirme el pago.')
            ->line("Número de pedido: {$order->order_number}")
            ->line('Total: '.Money::format($order->total, $order->currency))
            ->action('Ver mi pedido', url("/mi-cuenta/pedidos/{$order->order_number}"))
            ->line('Si tienes alguna duda, nuestro equipo está disponible para ayudarte.');
    }
}
