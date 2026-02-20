<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'selling_price',
        'production_cost',
        'overhead_cost_per_unit',
        'description',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'production_cost' => 'decimal:2',
        'overhead_cost_per_unit' => 'decimal:2',
    ];

    /**
     * Relasi Bill of Materials (BOM)
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }
}
