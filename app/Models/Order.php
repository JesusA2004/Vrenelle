<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property int|null $shipping_method_id
 * @property int|null $coupon_id
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property FulfillmentStatus $fulfillment_status
 * @property int $subtotal
 * @property int $discount_total
 * @property int $shipping_total
 * @property int $tax_total
 * @property int $total
 * @property string $currency
 * @property string $customer_name
 * @property string $email
 * @property string $phone
 * @property array<string, mixed>|null $billing_address
 * @property array<string, mixed>|null $shipping_address
 * @property string|null $payment_method
 * @property int|null $installment_months
 * @property string|null $notes
 * @property string|null $idempotency_key
 * @property string|null $tracking_number
 * @property string|null $shipping_company
 * @property string|null $tracking_url
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'shipping_method_id', 'coupon_id',
        'status', 'payment_status', 'fulfillment_status',
        'subtotal', 'discount_total', 'shipping_total', 'tax_total', 'total', 'currency',
        'customer_name', 'email', 'phone',
        'billing_address', 'shipping_address',
        'payment_method', 'installment_months',
        'notes', 'idempotency_key',
        'tracking_number', 'shipping_company', 'tracking_url',
        'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= static::generateOrderNumber();
        });
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'VRN-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    /**
     * @return HasMany<PaymentTransaction, $this>
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
