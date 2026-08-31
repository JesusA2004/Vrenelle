<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
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

        $body = match ($order->status) {
            OrderStatus::Confirmed => 'Tu pedido ha sido confirmado y pronto comenzará su preparación.',
            OrderStatus::Preparing => 'Nuestro equipo está preparando tu pieza con el máximo cuidado.',
            OrderStatus::Delivered => 'Tu pedido ha sido entregado. Esperamos que disfrutes tu nueva pieza.',
            default => 'El estado de tu pedido se ha actualizado.',
        };

        return (new MailMessage)
            ->subject("Actualización de tu pedido {$order->order_number}")
            ->greeting($order->status->label())
            ->line($body)
            ->line("Número de pedido: {$order->order_number}")
            ->action('Ver mi pedido', url("/mi-cuenta/pedidos/{$order->order_number}"));
    }
}
