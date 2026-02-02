# ClawDBot Laravel Folder Structure

```
MyProperty/
├── backend/
│   ├── app/
│   │   ├── Console/
│   │   │   ├── Kernel.php                    # 🕐 Task Scheduler (Updated)
│   │   │   └── Commands/
│   │   │       ├── ClawDBot/                 # 🤖 Bot Commands
│   │   │       │   ├── PropertyCleanupCommand.php
│   │   │       │   ├── DailySummaryCommand.php
│   │   │       │   ├── WeeklyReportCommand.php
│   │   │       │   ├── ExpiryNotifierCommand.php
│   │   │       │   ├── SystemMaintenanceCommand.php
│   │   │       │   ├── AnalyticsCommand.php
│   │   │       │   ├── BotStatusCommand.php
│   │   │       │   └── ManualTriggerCommand.php
│   │   │       └── ... (existing commands)
│   │   ├── Jobs/
│   │   │   ├── ClawDBot/                     # 📋 Background Jobs
│   │   │   │   ├── ProcessPropertyExpiry.php
│   │   │   │   ├── SendDailyDigest.php
│   │   │   │   ├── GenerateWeeklyReport.php
│   │   │   │   ├── CleanupExpiredListings.php
│   │   │   │   ├── NotifyPropertyOwners.php
│   │   │   │   ├── UpdatePropertyStatus.php
│   │   │   │   ├── ProcessAnalyticsData.php
│   │   │   │   ├── SendPriceChangeAlerts.php
│   │   │   │   ├── ValidateListings.php
│   │   │   │   └── GenerateSuggestions.php
│   │   │   └── ... (existing jobs)
│   │   ├── Services/
│   │   │   ├── ClawDBot/                     # 🔧 Bot Services
│   │   │   │   ├── PropertyManagementService.php
│   │   │   │   ├── NotificationService.php
│   │   │   │   ├── AnalyticsService.php
│   │   │   │   ├── ValidationService.php
│   │   │   │   ├── ReportService.php
│   │   │   │   ├── SuggestionService.php
│   │   │   │   ├── MaintenanceService.php
│   │   │   │   └── AILogsService.php         # AI-ready hooks
│   │   │   └── ... (existing services)
│   │   ├── Notifications/
│   │   │   ├── ClawDBot/                     # 📧 Bot Notifications
│   │   │   │   ├── PropertyExpiringSoon.php
│   │   │   │   ├── PropertyExpired.php
│   │   │   │   ├── DailyPropertyDigest.php
│   │   │   │   ├── WeeklyAnalyticsReport.php
│   │   │   │   ├── ListingRemoved.php
│   │   │   │   ├── PriceChangeAlert.php
│   │   │   │   ├── SuspiciousListingAlert.php
│   │   │   │   ├── SystemMaintenance.php
│   │   │   │   └── BotStatusAlert.php
│   │   │   └── ... (existing notifications)
│   │   ├── Observers/
│   │   │   ├── PropertyObserver.php          # 👁️ Property Events
│   │   │   ├── UserObserver.php              # User Events
│   │   │   ├── EnquiryObserver.php           # Enquiry Events
│   │   │   └── ListingObserver.php            # Listing Events
│   │   ├── Models/
│   │   │   ├── ClawDBot/                     # 📊 Bot Models
│   │   │   │   ├── BotTask.php               # Task execution logs
│   │   │   │   ├── BotNotification.php       # Notification history
│   │   │   │   ├── BotAnalytics.php          # Analytics data
│   │   │   │   ├── BotSchedule.php           # Schedule configuration
│   │   │   │   └── BotSetting.php            # Bot settings
│   │   │   ├── Property.php                  # (existing, updated)
│   │   │   ├── User.php                      # (existing, updated)
│   │   │   └── ... (existing models)
│   │   ├── Http/Controllers/
│   │   │   ├── API/
│   │   │   │   ├── ClawDBotController.php    # 🌐 Bot API Endpoints
│   │   │   │   └── AnalyticsController.php   # 📈 Analytics API
│   │   │   └── ... (existing controllers)
│   │   ├── Http/Requests/
│   │   │   ├── ClawDBot/                     # 📝 Bot Requests
│   │   │   │   ├── BotTriggerRequest.php
│   │   │   │   ├── AnalyticsRequest.php
│   │   │   │   └── ScheduleRequest.php
│   │   │   └── ... (existing requests)
│   │   ├── Events/
│   │   │   ├── ClawDBot/                     # 🎉 Bot Events
│   │   │   │   ├── PropertyProcessed.php
│   │   │   │   ├── NotificationSent.php
│   │   │   │   ├── AnalyticsGenerated.php
│   │   │   │   └── SystemMaintenance.php
│   │   │   └── ... (existing events)
│   │   ├── Listeners/
│   │   │   ├── ClawDBot/                     # 🎧 Bot Event Listeners
│   │   │   │   ├── LogBotActivity.php
│   │   │   │   ├── UpdateBotMetrics.php
│   │   │   │   └── SendBotAlerts.php
│   │   │   └── ... (existing listeners)
│   │   └── Providers/
│   │       ├── ClawDBotServiceProvider.php  # 🚀 Bot Service Provider
│   │       └── ... (existing providers)
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── 2024_01_01_000001_create_bot_tasks_table.php
│   │   │   ├── 2024_01_01_000002_create_bot_notifications_table.php
│   │   │   ├── 2024_01_01_000003_create_bot_analytics_table.php
│   │   │   ├── 2024_01_01_000004_create_bot_schedules_table.php
│   │   │   ├── 2024_01_01_000005_create_bot_settings_table.php
│   │   │   └── ... (existing migrations)
│   │   ├── seeders/
│   │   │   ├── ClawDBotSeeder.php            # 🌱 Bot Data Seeder
│   │   │   └── ... (existing seeders)
│   │   └── factories/
│   │       ├── BotTaskFactory.php
│   │       ├── BotNotificationFactory.php
│   │       └── ... (existing factories)
│   ├── resources/
│   │   ├── views/
│   │   │   ├── emails/
│   │   │   │   ├── clawdbot/                  # 📧 Email Templates
│   │   │   │   │   ├── property-expiring.blade.php
│   │   │   │   │   ├── property-expired.blade.php
│   │   │   │   │   ├── daily-digest.blade.php
│   │   │   │   │   ├── weekly-report.blade.php
│   │   │   │   │   ├── price-change.blade.php
│   │   │   │   │   └── maintenance.blade.php
│   │   │   │   └── ... (existing email templates)
│   │   │   └── ... (existing views)
│   │   └── ... (existing resources)
│   ├── routes/
│   │   ├── api.php                            # 🌐 API Routes (Updated)
│   │   ├── web.php                            # 🌐 Web Routes (Updated)
│   │   └── console.php                        # 💻 Console Routes (Updated)
│   ├── config/
│   │   ├── clawdbot.php                       # ⚙️ Bot Configuration
│   │   └── ... (existing config)
│   ├── storage/
│   │   ├── app/
│   │   │   └── clawdbot/                      # 📁 Bot Storage
│   │   │       ├── logs/                      # Bot logs
│   │   │       ├── reports/                   # Generated reports
│   │   │       └── cache/                     # Bot cache
│   │   └── ... (existing storage)
│   └── ... (existing backend files)
└── ... (other project files)
```

## Key Components Explained

### 🤖 Commands (`app/Console/Commands/ClawDBot/`)
- **PropertyCleanupCommand**: Clean up expired/inactive properties
- **DailySummaryCommand**: Generate daily property summaries
- **WeeklyReportCommand**: Create weekly analytics reports
- **ExpiryNotifierCommand**: Notify owners of expiring listings
- **SystemMaintenanceCommand**: Perform system maintenance tasks
- **AnalyticsCommand**: Process analytics data
- **BotStatusCommand**: Check bot system health
- **ManualTriggerCommand**: Manually trigger bot operations

### 📋 Jobs (`app/Jobs/ClawDBot/`)
- **ProcessPropertyExpiry**: Handle property expiration logic
- **SendDailyDigest**: Send daily email digests
- **GenerateWeeklyReport**: Create weekly reports
- **CleanupExpiredListings**: Remove expired listings
- **NotifyPropertyOwners**: Send notifications to owners
- **UpdatePropertyStatus**: Update property statuses
- **ProcessAnalyticsData**: Process analytics calculations
- **SendPriceChangeAlerts**: Alert on price changes
- **ValidateListings**: Validate listing data
- **GenerateSuggestions**: Generate AI-ready suggestions

### 🔧 Services (`app/Services/ClawDBot/`)
- **PropertyManagementService**: Core property management logic
- **NotificationService**: Centralized notification handling
- **AnalyticsService**: Analytics data processing
- **ValidationService**: Data validation logic
- **ReportService**: Report generation
- **SuggestionService**: AI-ready suggestion hooks
- **MaintenanceService**: System maintenance tasks
- **AILogsService**: AI integration logging

### 📧 Notifications (`app/Notifications/ClawDBot/`)
- **PropertyExpiringSoon**: Warning before expiry
- **PropertyExpired**: Expiry confirmation
- **DailyPropertyDigest**: Daily summary emails
- **WeeklyAnalyticsReport**: Weekly analytics
- **ListingRemoved**: Listing removal notice
- **PriceChangeAlert**: Price change notifications
- **SuspiciousListingAlert**: Admin alerts for issues
- **SystemMaintenance**: Maintenance notifications
- **BotStatusAlert**: Bot health alerts

### 👁️ Observers (`app/Observers/`)
- **PropertyObserver**: Handle property model events
- **UserObserver**: Handle user model events
- **EnquiryObserver**: Handle enquiry model events
- **ListingObserver**: Handle listing model events

### 📊 Models (`app/Models/ClawDBot/`)
- **BotTask**: Track task execution history
- **BotNotification**: Log notification history
- **BotAnalytics**: Store analytics data
- **BotSchedule**: Schedule configuration
- **BotSetting**: Bot configuration settings

### 🌐 API (`app/Http/Controllers/API/`)
- **ClawDBotController**: Bot control endpoints
- **AnalyticsController**: Analytics data endpoints

This structure follows Laravel conventions and best practices while providing a comprehensive automation system for your real estate platform.
