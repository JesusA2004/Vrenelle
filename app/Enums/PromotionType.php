<?php

namespace App\Enums;

enum PromotionType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case SpecialPrice = 'special_price';
    case FreeShipping = 'free_shipping';
    case CollectionDiscount = 'collection_discount';
    case CategoryDiscount = 'category_discount';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Porcentaje',
            self::Fixed => 'Monto fijo',
            self::SpecialPrice => 'Precio especial',
            self::FreeShipping => 'Envío gratis',
            self::CollectionDiscount => 'Descuento por colección',
            self::CategoryDiscount => 'Descuento por categoría',
        };
    }
}
