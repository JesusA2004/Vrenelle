<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
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
        $isRefund = $order->status === OrderStatus::Refunded;

        return (new MailMessage)
            ->subject($isRefund ? "Reembolso procesado — pedido {$order->order_number}" : "Pedido {$order->order_number} cancelado")
            ->greeting($isRefund ? 'Tu reembolso ha sido procesado.' : 'Tu pedido ha sido cancelado.')
            ->line($isRefund
                ? "Hemos procesado el reembolso de tu pedido {$order->order_number}."
                : "Tu pedido {$order->order_number} fue cancelado.")
            ->line('Si tienes alguna pregunta, con gusto te ayudamos.')
            ->action('Ver mi pedido', url("/mi-cuenta/pedidos/{$order->order_number}"));
    }
}
