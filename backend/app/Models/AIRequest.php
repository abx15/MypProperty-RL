<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_type',
        'input_data',
        'output_data',
        'tokens_used',
        'error',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'tokens_used' => 'integer',
    ];

    /**
     * Get the user that owns the AI request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include price requests.
     */
    public function scopePrice($query)
    {
        return $query->where('request_type', 'price');
    }

    /**
     * Scope a query to only include description requests.
     */
    public function scopeDescription($query)
    {
        return $query->where('request_type', 'description');
    }

    /**
     * Scope a query to only include market requests.
     */
    public function scopeMarket($query)
    {
        return $query->where('request_type', 'market');
    }

    /**
     * Scope a query to only include enquiry requests.
     */
    public function scopeEnquiry($query)
    {
        return $query->where('request_type', 'enquiry');
    }

    /**
     * Scope a query to only include successful requests.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereNull('error');
    }

    /**
     * Scope a query to only include failed requests.
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error');
    }
}
