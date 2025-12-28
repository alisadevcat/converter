<?php

namespace Modules\Currency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeRate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exchange_rate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'base_code',
        'target_code',
        'rate',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:8',
        'date' => 'date',
    ];

    /**
     * Get the base currency code.
     *
     * @return string
     */
    public function getBaseCurrencyAttribute(): string
    {
        return $this->base_code;
    }

    /**
     * Get the target currency code.
     *
     * @return string
     */
    public function getTargetCurrencyAttribute(): string
    {
        return $this->target_code;
    }
}

