# ClawDBot - Laravel Native Automation System

## Overview

ClawDBot is a comprehensive automation system built entirely within Laravel using native Laravel components. It provides intelligent property management, automated notifications, analytics processing, and system maintenance for your MyProperty-RL real estate platform.

## 🚀 Quick Start

### 1. Setup Database Tables
```bash
php artisan migrate
```

### 2. Run Queue Worker
```bash
php artisan queue:work --queue=clawdbot-notifications,clawdbot-reports,clawdbot-maintenance
```

### 3. Start Scheduler
```bash
# Add to your crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Test Bot Commands
```bash
# Check bot status
php artisan clawdbot:status

# Test daily summary (preview)
php artisan clawdbot:daily-summary --preview

# Test property cleanup (dry run)
php artisan clawdbot:property-cleanup --dry-run
```

## 📋 Available Commands

### Property Management
- `clawdbot:property-cleanup` - Clean up expired and inactive properties
- `clawdbot:expiry-notifier` - Send expiry notifications to property owners

### Reports & Analytics
- `clawdbot:daily-summary` - Generate daily property summary
- `clawdbot:weekly-report` - Generate comprehensive weekly analytics report
- `clawdbot:analytics` - Process analytics data

### System Management
- `clawdbot:status` - Display system status and health information
- `clawdbot:system-maintenance` - Perform system maintenance tasks

## ⏰ Automated Schedule

The ClawDBot scheduler runs automatically with the following schedule:

- **8:00 AM Daily** - Property summary reports
- **9:00 AM Daily** - 7-day expiry warnings
- **10:00 AM Daily** - Critical expiry alerts (3 days)
- **11:00 PM Daily** - Property cleanup
- **12:00 AM Daily** - Expired property notifications
- **8:00 AM Mondays** - Weekly analytics reports
- **2:00 AM Sundays** - System maintenance
- **Every 6 hours** - Bot health checks
- **Every 2 hours** - Analytics processing
- **Every 30 minutes** - Queue monitoring

## 📊 Features

### Property Automation
- ✅ Automatic expiry detection and status updates
- ✅ Inactive property identification and management
- ✅ Owner notifications before expiry (7 days, 3 days, expired)
- ✅ Property validation and data integrity checks
- ✅ Bulk property reactivation capabilities

### Analytics & Reporting
- ✅ Daily property summaries with key metrics
- ✅ Comprehensive weekly analytics reports
- ✅ Real-time dashboard data providers
- ✅ Trend analysis and market insights
- ✅ Performance metrics and KPI tracking

### Notification System
- ✅ Multi-channel notifications (Email + Database)
- ✅ Targeted alerts for different user roles
- ✅ Customizable notification templates
- ✅ Bot status and health alerts
- ✅ Queue-based delivery for reliability

### System Health
- ✅ Comprehensive health checks
- ✅ Queue monitoring and failure handling
- ✅ Database and storage monitoring
- ✅ Performance metrics tracking
- ✅ Automatic error reporting

## 🔧 Configuration

### Environment Variables
Add these to your `.env` file:

```env
# ClawDBot Configuration
CLAWDBOT_ENABLED=true
CLAWDBOT_LOG_LEVEL=info
CLAWDBOT_QUEUE_CONNECTION=database

# Notification Settings
CLAWDBOT_EMAIL_ENABLED=true
CLAWDBOT_SMS_ENABLED=false
CLAWDBOT_PUSH_ENABLED=true

# Scheduler Settings
CLAWDBOT_SCHEDULER_ENABLED=true
CLAWDBOT_MAINTENANCE_WINDOW=02:00-04:00

# Performance Settings
CLAWDBOT_BATCH_SIZE=100
CLAWDBOT_TIMEOUT=300
CLAWDBOT_MEMORY_LIMIT=512M
```

### Queue Configuration
Add to `config/queues.php`:

```php
'clawdbot-notifications' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'clawdbot-notifications',
    'retry_after' => 90,
    'after_commit' => false,
],

'clawdbot-reports' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'clawdbot-reports',
    'retry_after' => 90,
    'after_commit' => false,
],

'clawdbot-maintenance' => [
    'driver' => 'database',
    'table' => 'jobs',
    'queue' => 'clawdbot-maintenance',
    'retry_after' => 90,
    'after_commit' => false,
],
```

## 📈 Dashboard Integration

### API Endpoints
Add these routes to your API:

```php
// ClawDBot API Routes
Route::prefix('clawdbot')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/status', [ClawDBotController::class, 'status']);
    Route::get('/analytics', [ClawDBotController::class, 'analytics']);
    Route::post('/trigger/{command}', [ClawDBotController::class, 'trigger']);
    Route::get('/tasks', [ClawDBotController::class, 'tasks']);
    Route::get('/health', [ClawDBotController::class, 'health']);
});
```

### Dashboard Widgets
Use these helper queries in your dashboard:

```php
// Get quick stats
$stats = app(AnalyticsService::class)->getDashboardAnalytics();

// Get recent bot activity
$recentTasks = BotTask::latest()->limit(10)->get();

// Get system health
$health = app(AnalyticsService::class)->getSystemAnalytics();
```

## 🧪 Testing

### Run Tests
```bash
# Run all ClawDBot tests
php artisan test --filter="ClawDBot"

# Run specific test
php artisan test tests/Unit/Services/ClawDBot/PropertyManagementServiceTest.php
```

### Test Commands
```bash
# Test with dry run
php artisan clawdbot:property-cleanup --dry-run

# Preview notifications
php artisan clawdbot:expiry-notifier --preview

# Test specific date
php artisan clawdbot:daily-summary --date=2024-01-15 --preview
```

## 🔍 Monitoring & Debugging

### Log Files
ClawDBot logs are stored in:
- `storage/logs/clawdbot.log` - Bot-specific logs
- `storage/logs/laravel.log` - General application logs

### Health Monitoring
```bash
# Check bot health
php artisan clawdbot:status --health

# Monitor queues
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

### Debug Mode
Enable debug mode in `.env`:
```env
CLAWDBOT_DEBUG=true
CLAWDBOT_LOG_LEVEL=debug
```

## 🚨 Troubleshooting

### Common Issues

1. **Queue Not Processing**
   ```bash
   php artisan queue:restart
   php artisan queue:work --queue=clawdbot-notifications,clawdbot-reports,clawdbot-maintenance
   ```

2. **Scheduler Not Running**
   ```bash
   # Check crontab
   crontab -l
   
   # Test scheduler manually
   php artisan schedule:run
   ```

3. **Memory Issues**
   ```bash
   # Increase PHP memory limit
   php -d memory_limit=512M artisan clawdbot:property-cleanup
   ```

4. **Database Connection Issues**
   ```bash
   # Test database connection
   php artisan clawdbot:status --health
   ```

### Error Recovery
```bash
# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# Reset bot tasks
php artisan clawdbot:reset-tasks
```

## 🔄 Best Practices

### Performance
- Use queue workers for background processing
- Implement proper error handling and retries
- Monitor memory usage and batch sizes
- Schedule heavy tasks during low-traffic hours

### Security
- Restrict bot commands to admin users only
- Validate all input parameters
- Use secure notification channels
- Monitor for suspicious activity

### Maintenance
- Regular health checks and monitoring
- Log rotation and cleanup
- Database optimization
- Regular backup verification

## 📚 API Reference

### Commands Reference
| Command | Description | Options |
|---------|-------------|---------|
| `clawdbot:property-cleanup` | Clean up properties | `--dry-run`, `--force`, `--days=30` |
| `clawdbot:daily-summary` | Daily summary | `--date=`, `--email=`, `--preview` |
| `clawdbot:weekly-report` | Weekly report | `--week=`, `--year=`, `--preview` |
| `clawdbot:expiry-notifier` | Expiry notifications | `--days=7`, `--type=warning`, `--preview` |
| `clawdbot:status` | System status | `--detailed`, `--health` |

### Services Reference
- `PropertyManagementService` - Property automation logic
- `AnalyticsService` - Data processing and analytics
- `NotificationService` - Multi-channel notifications
- `MaintenanceService` - System maintenance tasks

## 🤝 Contributing

When contributing to ClawDBot:
1. Follow Laravel coding standards
2. Add comprehensive tests
3. Update documentation
4. Test with dry-run options first
5. Consider performance implications

## 📄 License

ClawDBot is part of the MyProperty-RL project and follows the same MIT License.

---

**ClawDBot** - Your intelligent Laravel automation companion for real estate management! 🤖🏠
