<?php

namespace App\Models\ClawDBot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BotTask extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'command',
        'status',
        'started_at',
        'completed_at',
        'parameters',
        'result',
        'error_message',
        'execution_time',
        'memory_usage',
        'processed_items',
        'failed_items',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'parameters' => 'json',
        'result' => 'json',
        'execution_time' => 'float',
        'memory_usage' => 'integer',
        'processed_items' => 'integer',
        'failed_items' => 'integer',
    ];

    /**
     * Get the duration of the task execution.
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);
        
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Check if the task is currently running.
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if the task completed successfully.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the task failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope a query to only include running tasks.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope a query to only include completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed tasks.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to get tasks from the last N hours.
     */
    public function scopeLastHours($query, int $hours)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to get tasks from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get the formatted status with emoji.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'running' => '🔄 Running',
            'completed' => '✅ Completed',
            'failed' => '❌ Failed',
            default => '❓ Unknown'
        };
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRateAttribute(): ?float
    {
        if ($this->processed_items === 0) {
            return null;
        }

        $successfulItems = $this->processed_items - $this->failed_items;
        return round(($successfulItems / $this->processed_items) * 100, 2);
    }

    /**
     * Mark task as completed.
     */
    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'result' => $result,
            'execution_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : 0,
            'memory_usage' => memory_get_usage(true),
        ]);
    }

    /**
     * Mark task as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'execution_time' => $this->started_at ? now()->diffInSeconds($this->started_at) : 0,
            'memory_usage' => memory_get_usage(true),
        ]);
    }

    /**
     * Update progress information.
     */
    public function updateProgress(int $processed, int $failed = 0): void
    {
        $this->update([
            'processed_items' => $processed,
            'failed_items' => $failed,
        ]);
    }
}
