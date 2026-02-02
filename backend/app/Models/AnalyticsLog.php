<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the property that owns the analytics log.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user that owns the analytics log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include view actions.
     */
    public function scopeViews($query)
    {
        return $query->where('action', 'view');
    }

    /**
     * Scope a query to only include enquiry actions.
     */
    public function scopeEnquiries($query)
    {
        return $query->where('action', 'enquiry');
    }

    /**
     * Scope a query to only include wishlist actions.
     */
    public function scopeWishlists($query)
    {
        return $query->where('action', 'wishlist');
    }
}
