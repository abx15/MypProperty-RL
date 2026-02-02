<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'title',
        'slug',
        'description',
        'price',
        'location_id',
        'property_type',
        'category',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'year_built',
        'amenities',
        'status',
        'is_featured',
        'views_count',
        'ai_price_suggestion',
        'ai_description_generated',
        'latitude',
        'longitude',
        'address',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'ai_price_suggestion' => 'decimal:2',
        'amenities' => 'array',
        'is_featured' => 'boolean',
        'ai_description_generated' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'views_count' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'area_sqft' => 'integer',
        'year_built' => 'integer',
    ];

    /**
     * Get the agent that owns the property.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the location that owns the property.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the images for the property.
     */
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Get the primary image for the property.
     */
    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    /**
     * Get the enquiries for the property.
     */
    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * Get the wishlist items for the property.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the analytics logs for the property.
     */
    public function analyticsLogs()
    {
        return $this->hasMany(AnalyticsLog::class);
    }

    /**
     * Get the users that have wishlisted this property.
     */
    public function wishlistUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    /**
     * Scope a query to only include active properties.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include featured properties.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include properties for sale.
     */
    public function scopeForSale($query)
    {
        return $query->where('property_type', 'sale');
    }

    /**
     * Scope a query to only include properties for rent.
     */
    public function scopeForRent($query)
    {
        return $query->where('property_type', 'rent');
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }
}
