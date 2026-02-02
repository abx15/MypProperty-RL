# ClawDBot Folder Structure

```
MyProperty/
├── backend/                     # Laravel Backend (Existing)
│   ├── app/
│   ├── database/
│   └── ...
├── frontend/                    # React Frontend (Existing)
│   ├── src/
│   └── ...
├── bot/                         # 🤖 ClawDBot System (NEW)
│   ├── package.json             # Bot dependencies
│   ├── .env.example             # Environment template
│   ├── .gitignore               # Git ignore rules
│   ├── README.md                # Bot-specific documentation
│   ├── server.js                # Main bot server entry point
│   ├── config/                  # Configuration files
│   │   ├── database.js          # MongoDB connection
│   │   ├── redis.js             # Redis connection
│   │   ├── email.js             # Email service config
│   │   ├── ai.js                # AI service config
│   │   └── index.js             # Config loader
│   ├── core/                    # 🎯 Core Bot System
│   │   ├── BotEngine.js         # Main bot orchestrator
│   │   ├── EventSystem.js       # Event handling
│   │   ├── TaskManager.js       # Task management
│   │   ├── APIConnector.js      # Laravel API connector
│   │   └── Logger.js            # Logging system
│   ├── scheduler/               # ⏰ Task Scheduler
│   │   ├── CronManager.js       # Cron job manager
│   │   ├── TaskQueue.js         # Background task queue
│   │   ├── jobs/                # Individual job handlers
│   │   │   ├── DailyDigest.js   # Daily property summary
│   │   │   ├── ExpiredListings.js # Remove expired listings
│   │   │   ├── PriceChangeNotifier.js # Price change alerts
│   │   │   ├── MarketAnalysis.js # Market trend analysis
│   │   │   ├── UserEngagement.js # User activity reports
│   │   │   └── SystemMaintenance.js # System cleanup
│   │   └── templates/           # Job templates
│   ├── notifications/           # 📧 Notification Service
│   │   ├── NotificationService.js # Main notification handler
│   │   ├── EmailService.js      # Email sending
│   │   ├── InAppService.js      # In-app notifications
│   │   ├── SMSService.js        # SMS notifications
│   │   ├── PushService.js       # Push notifications
│   │   ├── templates/           # Message templates
│   │   │   ├── emails/          # Email templates
│   │   │   │   ├── daily-digest.html
│   │   │   │   ├── price-change.html
│   │   │   │   ├── expired-listing.html
│   │   │   │   └── welcome.html
│   │   │   ├── sms/             # SMS templates
│   │   │   └── in-app/          # In-app templates
│   │   └── channels/            # Channel handlers
│   ├── ai/                      # 🧠 AI Integration
│   │   ├── AIService.js         # Main AI service
│   │   ├── OpenAIConnector.js   # OpenAI API integration
│   │   ├── SmartSuggestions.js  # Property recommendations
│   │   ├── AutoReply.js         # Intelligent responses
│   │   ├── MarketAnalyzer.js    # Market trend analysis
│   │   ├── ContentGenerator.js  # Description generation
│   │   └── models/              # AI model configurations
│   ├── analytics/               # 📊 Analytics Service
│   │   ├── AnalyticsService.js  # Main analytics handler
│   │   ├── DataCollector.js     # Data gathering
│   │   ├── ReportGenerator.js   # Report creation
│   │   ├── DashboardProvider.js # Dashboard data
│   │   ├── TrendAnalyzer.js     # Trend analysis
│   │   └── metrics/             # Metric definitions
│   │       ├── propertyMetrics.js
│   │       ├── userMetrics.js
│   │       └── systemMetrics.js
│   ├── webhooks/                # 🎣 Webhook System
│   │   ├── WebhookManager.js    # Main webhook handler
│   │   ├── EventProcessor.js    # Event processing
│   │   ├── handlers/            # Specific webhook handlers
│   │   │   ├── PropertyWebhook.js
│   │   │   ├── UserWebhook.js
│   │   │   ├── EnquiryWebhook.js
│   │   │   └── SystemWebhook.js
│   │   └── middleware/          # Webhook middleware
│   ├── api/                     # 🌐 API Endpoints
│   │   ├── routes/              # Express routes
│   │   │   ├── bot.js           # Bot control endpoints
│   │   │   ├── analytics.js     # Analytics endpoints
│   │   │   ├── notifications.js # Notification endpoints
│   │   │   ├── scheduler.js     # Scheduler endpoints
│   │   │   └── webhooks.js       # Webhook receivers
│   │   ├── controllers/         # Route controllers
│   │   │   ├── BotController.js
│   │   │   ├── AnalyticsController.js
│   │   │   ├── NotificationController.js
│   │   │   └── SchedulerController.js
│   │   ├── middleware/          # API middleware
│   │   │   ├── auth.js          # Authentication
│   │   │   ├── validation.js    # Input validation
│   │   │   ├── rateLimit.js     # Rate limiting
│   │   │   └── cors.js          # CORS handling
│   │   └── validators/         # Input validators
│   ├── models/                  # 📋 MongoDB Models
│   │   ├── BotTask.js           # Task records
│   │   ├── Notification.js      # Notification logs
│   │   ├── Analytics.js         # Analytics data
│   │   ├── WebhookLog.js        # Webhook logs
│   │   ├── JobSchedule.js       # Job schedules
│   │   └── BotConfig.js         # Bot configuration
│   ├── services/                # 🔧 Utility Services
│   │   ├── DatabaseService.js   # Database operations
│   │   ├── CacheService.js      # Cache operations
│   │   ├── QueueService.js      # Queue operations
│   │   ├── SecurityService.js   # Security utilities
│   │   └── ValidationService.js # Validation utilities
│   ├── utils/                   # 🛠️ Helper Utilities
│   │   ├── dateUtils.js         # Date manipulation
│   │   ├── stringUtils.js       # String utilities
│   │   ├── mathUtils.js         # Math utilities
│   │   ├── fileUtils.js         # File operations
│   │   └── constants.js         # Application constants
│   ├── tests/                   # 🧪 Test Suite
│   │   ├── unit/                # Unit tests
│   │   │   ├── core/            # Core system tests
│   │   │   ├── scheduler/       # Scheduler tests
│   │   │   ├── notifications/   # Notification tests
│   │   │   ├── ai/              # AI service tests
│   │   │   └── analytics/       # Analytics tests
│   │   ├── integration/         # Integration tests
│   │   │   ├── api/             # API endpoint tests
│   │   │   ├── webhooks/        # Webhook tests
│   │   │   └── database/        # Database tests
│   │   ├── fixtures/            # Test data
│   │   ├── helpers/             # Test helpers
│   │   └── setup.js             # Test setup
│   ├── docs/                    # 📚 Documentation
│   │   ├── API.md               # API documentation
│   │   ├── DEPLOYMENT.md        # Deployment guide
│   │   ├── CONFIGURATION.md     # Configuration guide
│   │   ├── TROUBLESHOOTING.md   # Troubleshooting guide
│   │   └── EXAMPLES.md          # Usage examples
│   └── scripts/                 # 📜 Utility Scripts
│       ├── setup.js             # Initial setup
│       ├── migrate.js           # Database migration
│       ├── seed.js              # Data seeding
│       ├── backup.js            # Data backup
│       └── deploy.js            # Deployment helper
├── docker-compose.yml           # Docker configuration
├── .env.example                 # Global environment template
└── README.md                    # Updated main README
```

## Key Components Explained

### `/bot/core/` - Core Bot System
- **BotEngine.js**: Main orchestrator that coordinates all bot services
- **EventSystem.js**: Handles internal and external events
- **TaskManager.js**: Manages task execution and scheduling
- **APIConnector.js**: Communicates with Laravel backend

### `/bot/scheduler/` - Automated Tasks
- **CronManager.js**: Manages cron-based scheduling
- **TaskQueue.js**: Handles background job processing
- **jobs/**: Individual task implementations

### `/bot/notifications/` - Multi-Channel Notifications
- **NotificationService.js**: Central notification coordinator
- **EmailService.js**: SMTP email handling
- **InAppService.js**: Real-time in-app notifications
- **templates/**: Reusable message templates

### `/bot/ai/` - AI Integration
- **AIService.js**: Main AI service coordinator
- **SmartSuggestions.js**: Property recommendation engine
- **AutoReply.js**: Intelligent response generation
- **MarketAnalyzer.js**: Market trend analysis

### `/bot/analytics/` - Data Analytics
- **AnalyticsService.js**: Main analytics coordinator
- **DataCollector.js**: Gathers data from various sources
- **ReportGenerator.js**: Creates automated reports
- **DashboardProvider.js**: Supplies dashboard data

### `/bot/webhooks/` - Event Handling
- **WebhookManager.js**: Manages incoming webhooks
- **EventProcessor.js**: Processes webhook events
- **handlers/**: Specific event type handlers

### `/bot/api/` - REST API
- **routes/**: Express route definitions
- **controllers/**: Request handling logic
- **middleware/**: Authentication, validation, rate limiting
- **validators/**: Input validation schemas

### `/bot/models/` - MongoDB Models
- **BotTask.js**: Task execution records
- **Notification.js**: Notification history
- **Analytics.js**: Analytics data storage
- **WebhookLog.js**: Webhook event logs

### `/bot/tests/` - Test Suite
- **unit/**: Individual component tests
- **integration/**: Cross-component tests
- **fixtures/**: Test data and helpers

This structure provides a complete, modular, and scalable ClawDBot system that integrates seamlessly with your existing Laravel backend while maintaining clean separation of concerns.
