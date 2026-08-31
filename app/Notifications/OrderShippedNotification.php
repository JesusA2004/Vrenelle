<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification
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

        $mail = (new MailMessage)
            ->subject("Tu pedido {$order->order_number} ha sido enviado")
            ->greeting('Tu pieza va en camino.')
            ->line("Tu pedido {$order->order_number} fue enviado".($order->shipping_company ? " a través de {$order->shipping_company}" : '').'.');

        if ($order->tracking_number) {
            $mail->line("Número de guía: {$order->tracking_number}");
        }

        if ($order->tracking_url) {
            $mail->action('Rastrear mi pedido', $order->tracking_url);
        } else {
            $mail->action('Ver mi pedido', url("/mi-cuenta/pedidos/{$order->order_number}"));
        }

        return $mail;
    }
}
