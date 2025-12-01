<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image',
        'shop_id',
        'old_price',
        'new_price',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'shop_id' => 'integer',
    ];

    /**
     * Get the shop that owns the promo.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Scope a query to only include active promos.
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('new_price')
                    ->where('new_price', '>', 0);
    }

    /**
     * Scope a query to order by latest.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get formatted old price attribute.
     */
    public function getFormattedOldPriceAttribute()
    {
        return 'Rp ' . number_format($this->old_price, 0, ',', '.');
    }

    /**
     * Get formatted new price attribute.
     */
    public function getFormattedNewPriceAttribute()
    {
        return 'Rp ' . number_format($this->new_price, 0, ',', '.');
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->old_price > 0) {
            return round((($this->old_price - $this->new_price) / $this->old_price) * 100, 1);
        }
        return 0;
    }

    /**
     * Get savings amount.
     */
    public function getSavingsAttribute()
    {
        return $this->old_price - $this->new_price;
    }

    /**
     * Get formatted savings attribute.
     */
    public function getFormattedSavingsAttribute()
    {
        return 'Rp ' . number_format($this->savings, 0, ',', '.');
    }
}