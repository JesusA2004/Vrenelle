<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly ?ProductVariant $variant,
        public readonly int $requestedQuantity,
        public readonly int $availableQuantity,
    ) {
        parent::__construct('La cantidad seleccionada supera la disponibilidad inmediata de esta pieza.');
    }
}
