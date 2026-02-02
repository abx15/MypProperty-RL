<?php

namespace App\Console\Commands\ClawDBot;

use App\Jobs\ClawDBot\ProcessAnalyticsData;
use App\Models\ClawDBot\BotTask;
use App\Models\ClawDBot\BotAnalytics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clawdbot:analytics 
                            {--process : Process analytics data}
                            {--generate : Generate analytics reports}
                            {--cleanup : Clean up old analytics data}
                            {--date= : Specific date to process (YYYY-MM-DD)}
                            {--period= : Period to process (daily|weekly|monthly)}
                            {--preview : Show analytics without processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ClawDBot: Process and generate analytics data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 ClawDBot Analytics Processing Started');
        $this->line('=======================================');

        $processData = $this->option('process');
        $generateReports = $this->option('generate');
        $cleanupData = $this->option('cleanup');
        $specificDate = $this->option('date');
        $period = $this->option('period') ?? 'daily';
        $preview = $this->option('preview');

        // If no specific options, process and generate
        if (!$processData && !$generateReports && !$cleanupData) {
            $processData = $generateReports = true;
        }

        $targetDate = $specificDate ? Carbon::parse($specificDate) : Carbon::yesterday();

        $this->line("📊 Processing analytics for: {$targetDate->format('Y-m-d')} ({$period})");

        try {
            // Start bot task logging
            $botTask = BotTask::create([
                'command' => 'clawdbot:analytics',
                'status' => 'running',
                'started_at' => now(),
                'parameters' => json_encode([
                    'process' => $processData,
                    'generate' => $generateReports,
                    'cleanup' => $cleanupData,
                    'target_date' => $targetDate->toDateString(),
                    'period' => $period,
                    'preview' => $preview
                ])
            ]);

            $results = [
                'processed_records' => 0,
                'generated_reports' => 0,
                'cleaned_records' => 0,
                'analytics_data' => []
            ];

            // Process analytics data
            if ($processData) {
                $processingResults = $this->processAnalyticsData($targetDate, $period, $preview);
                $results['processed_records'] = $processingResults['records_processed'];
                $results['analytics_data'] = $processingResults['data'];
            }

            // Generate reports
            if ($generateReports) {
                $results['generated_reports'] = $this->generateAnalyticsReports($targetDate, $period, $preview);
            }

            // Clean up old data
            if ($cleanupData) {
                $results['cleaned_records'] = $this->cleanupOldAnalyticsData();
            }

            // Display preview if requested
            if ($preview) {
                $this->displayAnalyticsPreview($results, $targetDate, $period);
            }

            // Update bot task status
            $botTask->update([
                'status' => 'completed',
                'completed_at' => now(),
                'result' => json_encode($results)
            ]);

            $this->info('✅ Analytics processing completed successfully');
            $this->line('📊 Summary:');
            $this->line("   - Records processed: {$results['processed_records']}");
            $this->line("   - Reports generated: {$results['generated_reports']}");
            $this->line("   - Records cleaned: {$results['cleaned_records']}");

            return 0;

        } catch (\Exception $e) {
            Log::error('ClawDBot Analytics Error: ' . $e->getMessage(), [
                'exception' => $e,
                'command' => 'clawdbot:analytics',
                'target_date' => $targetDate->toDateString()
            ]);

            if (isset($botTask)) {
                $botTask->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage()
                ]);
            }

            $this->error('❌ Analytics processing failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process analytics data
     */
    private function processAnalyticsData(Carbon $date, string $period, bool $preview): array
    {
        $this->line('🔄 Processing analytics data...');

        $data = [];
        $recordsProcessed = 0;

        try {
            switch ($period) {
                case 'daily':
                    $data = $this->processDailyAnalytics($date);
                    break;
                case 'weekly':
                    $data = $this->processWeeklyAnalytics($date);
                    break;
                case 'monthly':
                    $data = $this->processMonthlyAnalytics($date);
                    break;
            }

            if (!$preview) {
                // Store analytics data
                foreach ($data as $metric => $value) {
                    BotAnalytics::updateOrCreate([
                        'metric_name' => $metric,
                        'period' => $period,
                        'date' => $date->toDateString()
                    ], [
                        'metric_value' => $value,
                        'metadata' => json_encode([
                            'processed_at' => now()->toISOString(),
                            'source' => 'clawdbot'
                        ])
                    ]);
                }

                // Dispatch background processing job
                ProcessAnalyticsData::dispatch($data, $date, $period);
            }

            $recordsProcessed = count($data);

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Analytics processing failed: {$e->getMessage()}");
        }

        return [
            'records_processed' => $recordsProcessed,
            'data' => $data
        ];
    }

    /**
     * Process daily analytics
     */
    private function processDailyAnalytics(Carbon $date): array
    {
        return [
            'properties_created' => \App\Models\Property::whereDate('created_at', $date)->count(),
            'properties_active' => \App\Models\Property::whereDate('created_at', $date)->where('status', 'active')->count(),
            'users_registered' => \App\Models\User::whereDate('created_at', $date)->count(),
            'enquiries_received' => \App\Models\Enquiry::whereDate('created_at', $date)->count(),
            'enquiries_responded' => \App\Models\Enquiry::whereDate('created_at', $date)->where('status', 'responded')->count(),
            'average_property_price' => \App\Models\Property::whereDate('created_at', $date)->avg('price') ?? 0,
            'total_property_value' => \App\Models\Property::whereDate('created_at', $date)->sum('price'),
            'page_views' => $this->getPageViews($date),
            'unique_visitors' => $this->getUniqueVisitors($date),
            'conversion_rate' => $this->calculateConversionRate($date),
        ];
    }

    /**
     * Process weekly analytics
     */
    private function processWeeklyAnalytics(Carbon $date): array
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return [
            'properties_created_weekly' => \App\Models\Property::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'users_registered_weekly' => \App\Models\User::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'enquiries_received_weekly' => \App\Models\Enquiry::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'average_response_time' => $this->calculateAverageResponseTime($startOfWeek, $endOfWeek),
            'top_categories' => $this->getTopCategories($startOfWeek, $endOfWeek),
            'top_locations' => $this->getTopLocations($startOfWeek, $endOfWeek),
            'user_engagement_score' => $this->calculateUserEngagement($startOfWeek, $endOfWeek),
        ];
    }

    /**
     * Process monthly analytics
     */
    private function processMonthlyAnalytics(Carbon $date): array
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        return [
            'properties_created_monthly' => \App\Models\Property::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'users_registered_monthly' => \App\Models\User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'enquiries_received_monthly' => \App\Models\Enquiry::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'monthly_revenue' => $this->calculateMonthlyRevenue($startOfMonth, $endOfMonth),
            'user_retention_rate' => $this->calculateUserRetention($startOfMonth, $endOfMonth),
            'property_performance_score' => $this->calculatePropertyPerformance($startOfMonth, $endOfMonth),
        ];
    }

    /**
     * Generate analytics reports
     */
    private function generateAnalyticsReports(Carbon $date, string $period, bool $preview): int
    {
        $this->line('📈 Generating analytics reports...');
        $reportsGenerated = 0;

        try {
            $reportTypes = ['summary', 'detailed', 'trends'];

            foreach ($reportTypes as $reportType) {
                if (!$preview) {
                    // Generate report file
                    $reportPath = storage_path("app/clawdbot/reports/{$reportType}_{$period}_{$date->format('Y-m-d')}.json");
                    
                    $reportData = $this->generateReportData($reportType, $date, $period);
                    
                    file_put_contents($reportPath, json_encode($reportData, JSON_PRETTY_PRINT));
                    
                    $reportsGenerated++;
                } else {
                    $this->line("   📋 Would generate {$reportType} report for {$period} {$date->format('Y-m-d')}");
                    $reportsGenerated++;
                }
            }

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Report generation failed: {$e->getMessage()}");
        }

        return $reportsGenerated;
    }

    /**
     * Clean up old analytics data
     */
    private function cleanupOldAnalyticsData(): int
    {
        $this->line('🧹 Cleaning up old analytics data...');
        $cleanedRecords = 0;

        try {
            // Keep data for 1 year
            $cutoffDate = now()->subYear();

            $cleanedRecords = BotAnalytics::where('date', '<', $cutoffDate)->delete();

        } catch (\Exception $e) {
            $this->warn("   ⚠️  Cleanup failed: {$e->getMessage()}");
        }

        return $cleanedRecords;
    }

    /**
     * Display analytics preview
     */
    private function displayAnalyticsPreview(array $results, Carbon $date, string $period): void
    {
        $this->line("\n📊 ANALYTICS PREVIEW - {$period} {$date->format('Y-m-d')}");
        $this->line(str_repeat('=', 50));

        if (!empty($results['analytics_data'])) {
            $this->line("\n📈 Key Metrics:");
            foreach ($results['analytics_data'] as $metric => $value) {
                $formattedValue = is_numeric($value) ? number_format($value, 2) : $value;
                $this->line("   " . ucwords(str_replace('_', ' ', $metric)) . ": {$formattedValue}");
            }
        }

        $this->line("\n📋 Processing Summary:");
        $this->line("   Records to process: {$results['processed_records']}");
        $this->line("   Reports to generate: {$results['generated_reports']}");
        $this->line("   Records to clean: {$results['cleaned_records']}");

        $this->line("\n" . str_repeat('=', 50));
    }

    /**
     * Helper methods for analytics calculations
     */
    private function getPageViews(Carbon $date): int
    {
        // This would integrate with your analytics system
        return rand(100, 1000); // Placeholder
    }

    private function getUniqueVisitors(Carbon $date): int
    {
        // This would integrate with your analytics system
        return rand(50, 500); // Placeholder
    }

    private function calculateConversionRate(Carbon $date): float
    {
        $enquiries = \App\Models\Enquiry::whereDate('created_at', $date)->count();
        $visitors = $this->getUniqueVisitors($date);
        
        return $visitors > 0 ? round(($enquiries / $visitors) * 100, 2) : 0;
    }

    private function calculateAverageResponseTime(Carbon $start, Carbon $end): float
    {
        // This would calculate actual response times
        return 2.5; // Placeholder in hours
    }

    private function getTopCategories(Carbon $start, Carbon $end): array
    {
        return \App\Models\Property::whereBetween('created_at', [$start, $end])
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'category')
            ->toArray();
    }

    private function getTopLocations(Carbon $start, Carbon $end): array
    {
        return \App\Models\Property::whereBetween('created_at', [$start, $end])
            ->with('location')
            ->selectRaw('location_id, COUNT(*) as count')
            ->groupBy('location_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->location ? $item->location->name : 'Unknown' => $item->count];
            })
            ->toArray();
    }

    private function calculateUserEngagement(Carbon $start, Carbon $end): float
    {
        // This would calculate actual engagement metrics
        return 75.5; // Placeholder score
    }

    private function calculateMonthlyRevenue(Carbon $start, Carbon $end): float
    {
        // This would calculate actual revenue
        return 15000.00; // Placeholder
    }

    private function calculateUserRetention(Carbon $start, Carbon $end): float
    {
        // This would calculate actual retention rate
        return 85.2; // Placeholder percentage
    }

    private function calculatePropertyPerformance(Carbon $start, Carbon $end): float
    {
        // This would calculate actual property performance
        return 78.9; // Placeholder score
    }

    private function generateReportData(string $reportType, Carbon $date, string $period): array
    {
        // Generate different types of reports
        return [
            'type' => $reportType,
            'period' => $period,
            'date' => $date->toDateString(),
            'generated_at' => now()->toISOString(),
            'data' => $this->processDailyAnalytics($date) // Simplified
        ];
    }
}
