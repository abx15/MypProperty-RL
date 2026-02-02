<?php

namespace App\Jobs\ClawDBot;

use App\Models\ClawDBot\BotAnalytics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessAnalyticsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $analyticsData,
        public Carbon $targetDate,
        public string $period = 'daily'
    ) {
        $this->onQueue('clawdbot-reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('ClawDBot: Processing analytics data', [
                'date' => $this->targetDate->toDateString(),
                'period' => $this->period,
                'data_points' => count($this->analyticsData)
            ]);

            $this->storeAnalyticsData();
            $this->generateInsights();
            $this->updateCache();

            Log::info('ClawDBot: Analytics data processing completed');

        } catch (\Exception $e) {
            Log::error('ClawDBot: Failed to process analytics data', [
                'date' => $this->targetDate->toDateString(),
                'period' => $this->period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Store analytics data in database
     */
    private function storeAnalyticsData(): void
    {
        foreach ($this->analyticsData as $metric => $value) {
            BotAnalytics::updateOrCreate([
                'metric_name' => $metric,
                'period' => $this->period,
                'date' => $this->targetDate->toDateString()
            ], [
                'metric_value' => is_numeric($value) ? $value : 0,
                'metadata' => json_encode([
                    'processed_at' => now()->toISOString(),
                    'source' => 'clawdbot',
                    'data_type' => gettype($value)
                ])
            ]);
        }
    }

    /**
     * Generate insights from analytics data
     */
    private function generateInsights(): void
    {
        // Compare with previous period
        $previousPeriod = $this->getPreviousPeriod();
        $insights = [];

        foreach ($this->analyticsData as $metric => $value) {
            if (is_numeric($value)) {
                $previousValue = $this->getPreviousValue($metric, $previousPeriod);
                
                if ($previousValue !== null) {
                    $change = $this->calculateChange($value, $previousValue);
                    $insights[$metric] = [
                        'current_value' => $value,
                        'previous_value' => $previousValue,
                        'change_percentage' => $change,
                        'trend' => $this->determineTrend($change)
                    ];
                }
            }
        }

        // Store insights
        $this->storeInsights($insights);
    }

    /**
     * Update cache with latest analytics
     */
    private function updateCache(): void
    {
        $cacheKey = "clawdbot_analytics_{$this->period}_" . $this->targetDate->format('Y-m-d');
        
        cache()->put($cacheKey, [
            'data' => $this->analyticsData,
            'generated_at' => now()->toISOString(),
            'period' => $this->period,
            'date' => $this->targetDate->toDateString()
        ], 86400); // Cache for 24 hours
    }

    /**
     * Get previous period date
     */
    private function getPreviousPeriod(): Carbon
    {
        return match($this->period) {
            'daily' => $this->targetDate->copy()->subDay(),
            'weekly' => $this->targetDate->copy()->subWeek(),
            'monthly' => $this->targetDate->copy()->subMonth(),
            default => $this->targetDate->copy()->subDay()
        };
    }

    /**
     * Get previous value for a metric
     */
    private function getPreviousValue(string $metric, Carbon $previousPeriod): ?float
    {
        $previousAnalytics = BotAnalytics::where('metric_name', $metric)
            ->where('period', $this->period)
            ->where('date', $previousPeriod->toDateString())
            ->first();

        return $previousAnalytics ? $previousAnalytics->metric_value : null;
    }

    /**
     * Calculate percentage change
     */
    private function calculateChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Determine trend based on change
     */
    private function determineTrend(float $change): string
    {
        if ($change > 5) return 'increasing';
        if ($change < -5) return 'decreasing';
        return 'stable';
    }

    /**
     * Store insights in database
     */
    private function storeInsights(array $insights): void
    {
        foreach ($insights as $metric => $insight) {
            BotAnalytics::updateOrCreate([
                'metric_name' => $metric . '_insight',
                'period' => $this->period,
                'date' => $this->targetDate->toDateString()
            ], [
                'metric_value' => $insight['change_percentage'],
                'metadata' => json_encode($insight)
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ClawDBot: Process analytics data job failed', [
            'date' => $this->targetDate->toDateString(),
            'period' => $this->period,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the unique identifier for the job.
     */
    public function uniqueId(): string
    {
        return 'process-analytics-' . $this->period . '-' . $this->targetDate->toDateString();
    }
}
