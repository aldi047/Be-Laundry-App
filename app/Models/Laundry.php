<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'claim_code',
        'user_id',
        'shop_id',
        'weight',
        'total',
        'is_delivery',
        'delivery_address',
        'service_type',
        'status',
        'pickup_date',
        'delivery_date',
        'notes',
        'claimed_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weight' => 'decimal:2',
        'total' => 'decimal:2',
        'is_delivery' => 'boolean',
        'user_id' => 'integer',
        'shop_id' => 'integer',
        'pickup_date' => 'date',
        'delivery_date' => 'date',
        'claimed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Service type constants
     */
    const SERVICE_WASH = 'wash';
    const SERVICE_DRY_CLEAN = 'dry_clean';
    const SERVICE_IRON = 'iron';
    const SERVICE_WASH_IRON = 'wash_iron';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user that owns the laundry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop that owns the laundry.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Scope a query to only include laundries for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include laundries with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include unclaimed laundries.
     */
    public function scopeUnclaimed($query)
    {
        return $query->where('user_id', 0);
    }

    /**
     * Get formatted total attribute.
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Check if laundry is claimed.
     */
    public function isClaimed()
    {
        return $this->user_id != 0;
    }

    /**
     * Check if laundry can be claimed.
     */
    public function canBeClaimed()
    {
        return !$this->isClaimed() && in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get all available service types.
     */
    public static function getServiceTypes()
    {
        return [
            self::SERVICE_WASH,
            self::SERVICE_DRY_CLEAN,
            self::SERVICE_IRON,
            self::SERVICE_WASH_IRON,
        ];
    }
}