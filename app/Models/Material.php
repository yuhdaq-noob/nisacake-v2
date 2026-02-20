<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materials';

    protected $fillable = [
        'name',
        'unit',
        'base_unit',
        'price_per_unit',
        'price_per_base_unit',
        'current_stock',
        'min_stock_level',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'price_per_base_unit' => 'decimal:2',
        'current_stock' => 'integer',
        'min_stock_level' => 'integer',
    ];

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class)->orderBy('created_at', 'desc');
    }
}
