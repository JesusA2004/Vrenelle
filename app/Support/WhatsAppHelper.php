<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Centralized WhatsApp deep-link builder. Never hardcode the store's phone
 * number or message copy inside a component — always go through here so the
 * number stays configurable from the admin settings panel.
 */
class WhatsAppHelper
{
    public static function link(string $message): ?string
    {
        $number = preg_replace('/\D+/', '', (string) Settings::whatsappNumber());

        if ($number === '') {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    public static function forOutOfStockRequest(Product $product, ?ProductVariant $variant, int $quantity, string $url): ?string
    {
        $pieceName = $variant ? "{$product->name} — {$variant->name}" : $product->name;

        $message = "Hola, estoy interesado en solicitar {$quantity} ".($quantity === 1 ? 'unidad' : 'unidades')." de:\n"
            ."{$pieceName}\n"
            ."Referencia: {$product->sku}\n\n"
            ."URL del producto:\n{$url}\n\n"
            .'¿Podrían ayudarme con disponibilidad y tiempo estimado?';

        return static::link($message);
    }

    public static function forExceededQuantity(Product $product, ?ProductVariant $variant, int $requestedQuantity, string $url): ?string
    {
        return static::forOutOfStockRequest($product, $variant, $requestedQuantity, $url);
    }

    public static function forMadeToOrder(Product $product, ?ProductVariant $variant, string $url): ?string
    {
        $pieceName = $variant ? "{$product->name} — {$variant->name}" : $product->name;

        $message = "Hola, me interesa solicitar especialmente esta pieza:\n"
            ."{$pieceName}\n"
            ."Referencia: {$product->sku}\n\n"
            ."URL del producto:\n{$url}\n\n"
            .'¿Podrían confirmarme disponibilidad y tiempo estimado de entrega?';

        return static::link($message);
    }

    public static function forInquiryOnly(Product $product, string $url): ?string
    {
        $message = "Hola, quisiera más información sobre esta pieza:\n"
            ."{$product->name}\n"
            ."Referencia: {$product->sku}\n\n"
            ."URL del producto:\n{$url}\n\n"
            .'¿Podrían compartirme precio y disponibilidad?';

        return static::link($message);
    }

    public static function forGeneralInquiry(): ?string
    {
        return static::link('Hola, me gustaría recibir más información sobre sus piezas.');
    }
}
