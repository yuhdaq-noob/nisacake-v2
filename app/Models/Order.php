<?php

// FIXME: PERHITUNGAN

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_name',
        'order_date',
        'status',
        'total_price',
        'total_hpp',
        'scheduled_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'order_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'total_price' => 'decimal:2',
        'total_hpp' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
