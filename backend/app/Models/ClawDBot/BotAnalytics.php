<?php

namespace App\Models\ClawDBot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BotAnalytics extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metric_name',
        'metric_value',
        'period',
        'date',
        'metadata',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metric_value' => 'decimal:2',
        'metadata' => 'array',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Scope to get analytics for a specific period
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope to get analytics for a specific date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get analytics for a specific metric
     */
    public function scopeForMetric($query, string $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * Get the latest analytics for a metric
     */
    public static function latestForMetric(string $metricName, string $period = 'daily')
    {
        return static::forMetric($metricName)
            ->forPeriod($period)
            ->latest('date')
            ->first();
    }

    /**
     * Get analytics trend for a metric
     */
    public static function getTrend(string $metricName, string $period = 'daily', int $days = 30)
    {
        return static::forMetric($metricName)
            ->forPeriod($period)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get();
    }
}
