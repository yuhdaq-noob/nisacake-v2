<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialPriceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'user_id',
        'old_price_per_unit',
        'new_price_per_unit',
        'old_price_per_base_unit',
        'new_price_per_base_unit',
        'base_unit',
    ];

    protected $casts = [
        'old_price_per_unit' => 'decimal:2',
        'new_price_per_unit' => 'decimal:2',
        'old_price_per_base_unit' => 'decimal:2',
        'new_price_per_base_unit' => 'decimal:2',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
