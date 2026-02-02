<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'message',
        'phone',
        'email',
        'status',
        'agent_response',
        'ai_summary',
    ];

    /**
     * Get the property that owns the enquiry.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user that owns the enquiry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include new enquiries.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope a query to only include contacted enquiries.
     */
    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    /**
     * Scope a query to only include closed enquiries.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
