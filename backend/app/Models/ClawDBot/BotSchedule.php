<?php

namespace App\Models\ClawDBot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BotSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'command',
        'schedule_expression',
        'description',
        'is_active',
        'last_run_at',
        'next_run_at',
        'parameters',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'parameters' => 'array',
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
     * Scope to get active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schedules that should run now
     */
    public function scopeShouldRun($query)
    {
        return $query->active()
            ->where('next_run_at', '<=', now());
    }

    /**
     * Scope to get schedules for a specific command
     */
    public function scopeForCommand($query, string $command)
    {
        return $query->where('command', $command);
    }

    /**
     * Mark schedule as run
     */
    public function markAsRun()
    {
        $this->update([
            'last_run_at' => now(),
            'next_run_at' => $this->calculateNextRun()
        ]);
    }

    /**
     * Calculate next run time based on schedule expression
     */
    private function calculateNextRun()
    {
        // This would parse the cron expression and calculate next run time
        // For now, add 24 hours as placeholder
        return now()->addDay();
    }

    /**
     * Get human readable schedule
     */
    public function getHumanSchedule(): string
    {
        return match($this->schedule_expression) {
            '0 8 * * *' => 'Daily at 8:00 AM',
            '0 9 * * *' => 'Daily at 9:00 AM',
            '0 10 * * *' => 'Daily at 10:00 AM',
            '0 23 * * *' => 'Daily at 11:00 PM',
            '0 8 * * 1' => 'Weekly on Monday at 8:00 AM',
            '0 2 * * 0' => 'Weekly on Sunday at 2:00 AM',
            default => $this->schedule_expression
        };
    }
}
