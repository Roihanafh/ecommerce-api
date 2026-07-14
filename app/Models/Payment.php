<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property string $provider
 * @property string|null $transaction_id
 * @property string|null $snap_token
 * @property string|null $redirect_url
 * @property float $amount
 * @property PaymentStatus $status
 * @property array|null $payload
 * @property string $idempotency_key
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'transaction_id',
        'snap_token',
        'redirect_url',
        'amount',
        'status',
        'payload',
        'idempotency_key',
    ];

    protected $casts = [
        'amount'  => 'float',
        'status'  => PaymentStatus::class,
        'payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
