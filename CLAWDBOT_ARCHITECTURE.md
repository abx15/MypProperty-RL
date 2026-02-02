# ClawDBot Architecture Overview

## High-Level Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Laravel App   │    │   ClawDBot      │    │   MongoDB      │
│   (PHP/SQLite)  │◄──►│   (Node.js)     │◄──►│   (Bot Data)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   MySQL/SQLite  │    │   Redis Cache   │    │   External APIs │
│   (Main DB)     │    │   (Queue/Sched) │    │   (AI/Email)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Components Overview

### 1. **ClawDBot Core** (`bot/core/`)
- **Bot Engine**: Main bot orchestrator
- **Event System**: Handles triggers and webhooks
- **Task Manager**: Manages scheduled and background tasks
- **API Connector**: Communicates with Laravel backend

### 2. **Scheduler Service** (`bot/scheduler/`)
- **Cron Jobs**: Time-based automated tasks
- **Task Queue**: Background job processing
- **Job Handlers**: Specific task implementations

### 3. **Notification Service** (`bot/notifications/`)
- **Email Service**: SMTP email sending
- **In-App Alerts**: Real-time notifications
- **SMS Service**: Optional SMS notifications
- **Push Notifications**: Web/mobile push alerts

### 4. **AI Integration** (`bot/ai/`)
- **Smart Suggestions**: Property recommendations
- **Auto-Reply**: Intelligent response generation
- **Market Analysis**: Price trend analysis
- **Content Generation**: Property descriptions

### 5. **Analytics Service** (`bot/analytics/`)
- **Data Collection**: Gather usage metrics
- **Report Generation**: Daily/weekly reports
- **Dashboard Data**: Real-time analytics
- **Trend Analysis**: Market insights

### 6. **Webhook System** (`bot/webhooks/`)
- **Event Handlers**: Process Laravel events
- **API Endpoints**: Receive external triggers
- **Data Processing**: Transform and store data

## Data Flow

1. **Laravel → ClawDBot**: Webhooks trigger bot actions
2. **ClawDBot → MongoDB**: Store bot-specific data
3. **ClawDBot → Laravel**: API calls for data sync
4. **ClawDBot → External**: AI/Email services integration
5. **Scheduler → Tasks**: Automated execution
6. **Notifications → Users**: Multi-channel delivery

## Technology Stack

- **Runtime**: Node.js 20+
- **Framework**: Express.js
- **Database**: MongoDB (bot data)
- **Cache**: Redis (queues/sessions)
- **Scheduler**: node-cron
- **Email**: Nodemailer
- **AI**: OpenAI API
- **Testing**: Jest
- **Documentation**: JSDoc

## Security Features

- **API Authentication**: JWT tokens
- **Rate Limiting**: Express-rate-limit
- **Input Validation**: Joi validation
- **Environment Variables**: Secure config
- **CORS**: Cross-origin protection
- **Helmet**: Security headers

## Deployment Options

- **Development**: Docker Compose
- **Production**: Railway/Render/Heroku
- **Monitoring**: PM2 process manager
- **Logging**: Winston logger
- **Health Checks**: /health endpoint
