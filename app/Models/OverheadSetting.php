<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverheadSetting extends Model
{
    use HasFactory;

    protected $table = 'overhead_settings';

    protected $fillable = [
        'name',
        'key',
        'value',
        'unit',
    ];
}
