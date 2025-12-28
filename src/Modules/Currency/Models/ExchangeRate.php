<?php

namespace App\Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_code',
        'target_code',
        'rate',
        'date',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

}
