<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

class ClawDBotServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ClawDBot services
        $this->app->singleton(\App\Services\ClawDBot\PropertyManagementService::class);
        $this->app->singleton(\App\Services\ClawDBot\NotificationService::class);
        $this->app->singleton(\App\Services\ClawDBot\ValidationService::class);
        $this->app->singleton(\App\Services\ClawDBot\AnalyticsService::class);
        $this->app->singleton(\App\Services\ClawDBot\ReportService::class);
        $this->app->singleton(\App\Services\ClawDBot\SuggestionService::class);
        $this->app->singleton(\App\Services\ClawDBot\MaintenanceService::class);
        $this->app->singleton(\App\Services\ClawDBot\AILogsService::class);

        // Register configuration
        $this->mergeConfigFrom(
            module_path(__DIR__.'/../config/clawdbot.php'),
            'clawdbot'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        $this->registerObservers();

        // Register middleware
        $this->registerMiddleware();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        // Log service registration
        Log::info('ClawDBot service provider loaded successfully');
    }

    /**
     * Register model observers
     */
    protected function registerObservers(): void
    {
        \App\Models\Property::observe(\App\Observers\PropertyObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Enquiry::observe(\App\Observers\EnquiryObserver::class);
        
        // Register listing observer (alias for property)
        \App\Models\Property::observe(\App\Observers\ListingObserver::class);
    }

    /**
     * Register middleware
     */
    protected function registerMiddleware(): void
    {
        // Register ClawDBot middleware if needed
        $this->app['router']->aliasMiddleware('clawdbot.auth', \App\Http\Middleware\ClawDBotAuth::class);
        $this->app['router']->aliasMiddleware('clawdbot.rate-limit', \App\Http\Middleware\ClawDBotRateLimit::class);
    }

    /**
     * Register console commands
     */
    protected function registerCommands(): void
    {
        if (config('clawdbot.enabled', true)) {
            $this->commands([
                \App\Console\Commands\ClawDBot\PropertyCleanupCommand::class,
                \App\Console\Commands\ClawDBot\DailySummaryCommand::class,
                \App\Console\Commands\ClawDBot\WeeklyReportCommand::class,
                \App\Console\Commands\ClawDBot\ExpiryNotifierCommand::class,
                \App\Console\Commands\ClawDBot\SystemMaintenanceCommand::class,
                \App\Console\Commands\ClawDBot\AnalyticsCommand::class,
                \App\Console\Commands\ClawDBot\BotStatusCommand::class,
                \App\Console\Commands\ClawDBot\ManualTriggerCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            \App\Services\ClawDBot\PropertyManagementService::class,
            \App\Services\ClawDBot\NotificationService::class,
            \App\Services\ClawDBot\ValidationService::class,
            \App\Services\ClawDBot\AnalyticsService::class,
            \App\Services\ClawDBot\ReportService::class,
            \App\Services\ClawDBot\SuggestionService::class,
            \App\Services\ClawDBot\MaintenanceService::class,
            \App\Services\ClawDBot\AILogsService::class,
        ];
    }
}
