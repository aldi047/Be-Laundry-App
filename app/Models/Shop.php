<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image',
        'name',
        'location',
        'city',
        'is_delivery',
        'whatsapp',
        'description',
        'price_per_kg',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_delivery' => 'boolean',
        'price_per_kg' => 'decimal:2',
    ];

    /**
     * Get the laundries for the shop.
     */
    public function laundries()
    {
        return $this->hasMany(Laundry::class);
    }

    /**
     * Get the promos for the shop.
     */
    public function promos()
    {
        return $this->hasMany(Promo::class);
    }

    /**
     * Scope a query to only include shops with delivery service.
     */
    public function scopeWithDelivery($query)
    {
        return $query->where('is_delivery', true);
    }

    /**
     * Scope a query to filter shops by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    /**
     * Get formatted price per kg attribute.
     */
    public function getFormattedPricePerKgAttribute()
    {
        return 'Rp ' . number_format($this->price_per_kg, 0, ',', '.');
    }
}