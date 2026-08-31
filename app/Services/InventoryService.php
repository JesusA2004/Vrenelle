<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single source of truth for stock changes. Every mutation goes through
 * here so it is always paired with an inventory_movements row and runs
 * inside a locked transaction — this is what prevents overselling when two
 * customers race for the last piece.
 */
class InventoryService
{
    /**
     * Atomically decrement stock for a sale. Throws if insufficient stock.
     * Must be called from within checkout while the cart/order is already
     * inside a DB transaction.
     */
    public function decrementForSale(Product $product, ?ProductVariant $variant, int $quantity, ?Model $reference = null): void
    {
        $this->assertTransaction();

        /** @var Product $lockedProduct */
        $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

        if ($variant) {
            /** @var ProductVariant $lockedVariant */
            $lockedVariant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);

            if ($lockedVariant->stock < $quantity) {
                throw new InsufficientStockException($product, $variant, $quantity, $lockedVariant->stock);
            }

            $previous = $lockedVariant->stock;
            $lockedVariant->decrement('stock', $quantity);

            $this->logMovement($product, $variant, InventoryMovementType::Sale, -$quantity, $previous, $previous - $quantity, $reference);

            return;
        }

        if ($lockedProduct->stock < $quantity) {
            throw new InsufficientStockException($product, null, $quantity, $lockedProduct->stock);
        }

        $previous = $lockedProduct->stock;
        $lockedProduct->decrement('stock', $quantity);

        $this->logMovement($product, null, InventoryMovementType::Sale, -$quantity, $previous, $previous - $quantity, $reference);
    }

    /**
     * Return stock to inventory (order cancelled / refunded).
     */
    public function restock(Product $product, ?ProductVariant $variant, int $quantity, InventoryMovementType $type = InventoryMovementType::ReturnStock, ?Model $reference = null): void
    {
        $this->assertTransaction();

        if ($variant) {
            /** @var ProductVariant $lockedVariant */
            $lockedVariant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $previous = $lockedVariant->stock;
            $lockedVariant->increment('stock', $quantity);
            $this->logMovement($product, $variant, $type, $quantity, $previous, $previous + $quantity, $reference);

            return;
        }

        /** @var Product $lockedProduct */
        $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
        $previous = $lockedProduct->stock;
        $lockedProduct->increment('stock', $quantity);
        $this->logMovement($product, null, $type, $quantity, $previous, $previous + $quantity, $reference);
    }

    /**
     * Manual adjustment made from the admin panel (physical count correction, etc.).
     */
    public function adjust(Product $product, ?ProductVariant $variant, int $newStock, string $reason): InventoryMovement
    {
        return DB::transaction(function () use ($product, $variant, $newStock, $reason) {
            if ($variant) {
                /** @var ProductVariant $lockedVariant */
                $lockedVariant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
                $previous = $lockedVariant->stock;
                $lockedVariant->update(['stock' => $newStock]);

                return $this->logMovement($product, $variant, InventoryMovementType::Adjustment, $newStock - $previous, $previous, $newStock, null, $reason);
            }

            /** @var Product $lockedProduct */
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $previous = $lockedProduct->stock;
            $lockedProduct->update(['stock' => $newStock]);

            return $this->logMovement($product, null, InventoryMovementType::Adjustment, $newStock - $previous, $previous, $newStock, null, $reason);
        });
    }

    /**
     * Available quantity a customer may currently add to their cart, without
     * revealing the exact figure anywhere in the UI.
     */
    public function availableQuantity(Product $product, ?ProductVariant $variant): int
    {
        return $variant ? $variant->stock : $product->stock;
    }

    protected function logMovement(
        Product $product,
        ?ProductVariant $variant,
        InventoryMovementType $type,
        int $delta,
        int $previousStock,
        int $newStock,
        ?Model $reference = null,
        ?string $reason = null,
    ): InventoryMovement {
        return InventoryMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'type' => $type,
            'quantity' => $delta,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'reason' => $reason,
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    protected function assertTransaction(): void
    {
        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('InventoryService::decrementForSale/restock must run inside a DB transaction.');
        }
    }
}
