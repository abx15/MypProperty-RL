# 🏠️ MyProperty-RL - Complete Real Estate Platform

## 📋 Table of Contents

- [🌟 Overview](#-overview)
- [🚀 Quick Start](#-quick-start)
- [🏗️ Architecture](#architecture)
- [📁 Project Structure](#project-structure)
- [🔧 Prerequisites](#prerequisites)
- [🚀 Installation](#installation)
- [🌐️ Frontend Setup](#frontend-setup)
- [🔧 Backend Setup](#backend-setup)
- [🤖 ClawDBot Integration](#clawdbot-integration)
- [🗄️ Database Setup](#database-setup)
- [🔧 Configuration](#configuration)
- [🚀 Running the Application](#running-the-application)
- [📊 Available Features](#available-features)
- [🔧 API Documentation](#api-documentation)
- [🧪 Testing](#testing)
- [🚀 Deployment](#deployment)
- [🤝 Troubleshooting](#troubleshooting)
- [📚 Contributing](#contributing)

## 🌟 Overview

MyProperty-RL is a comprehensive real estate platform built with Laravel (backend) and React (frontend). It allows users to browse, list, search, and manage property listings with advanced features including automated bot management, analytics, and AI-ready architecture.

### 🎯 Key Features

- **Property Management**: Create, edit, and manage property listings
- **Advanced Search**: Filter by location, price, category, and more
- **User Management**: Role-based access control (Admin, Agent, User)
- **Enquiry System**: Contact property owners and track communications
- **Wishlist**: Save favorite properties for later viewing
- **Image Management**: Upload and manage property photos
- **🤖 ClawDBot**: Automated backend bot for property management
- **Analytics Dashboard**: Comprehensive reporting and insights
- **Multi-channel Notifications**: Email and in-app notifications
- **AI-Ready**: Architecture prepared for AI integration

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer
- npm or yarn
- Git

### Installation Steps

1. **Clone the Repository**
```bash
git clone https://github.com/abx15/MypProperty-RL.git
cd MypProperty-RL
```

2. **Backend Setup**
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

3. **Frontend Setup**
```bash
cd frontend
npm install
npm run build
```

4. **Start the Applications**

```bash
# Backend (Terminal 1)
cd backend
php artisan serve

# Frontend (Terminal 2)
cd frontend
npm start
```

5. **Access the Application**
- Frontend: `http://localhost:3000`
- Backend API: `http://localhost:8000/api`
- Admin Panel: `http://localhost:3000/admin`

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        🌐 Frontend (React)                              │
├─────────────────────────────────────────────────────────────────────────────┤
│  📱 Components                🎨 State Management (Redux)                     │
│  ├── Property Listings           ├── User Authentication                    │
│  ├── Search & Filters            ├── Property Management                 │
│  ├── User Dashboard            ├── Analytics Dashboard                 │
│  ├── Admin Panel               ├── Notification System                │
│  └── Enquiry Management        └── Wishlist Management                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    🌐 API Layer (REST)
                                    │
┌─────────────────────────────────────────────────────────────────────────────┐
│                        🔧 Backend (Laravel)                              │
├─────────────────────────────────────────────────────────────────────────────┤
│  📋 Controllers              🎨 Models & Relationships                    │
│  ├── PropertyController        ├── Property (Properties)               │
│  ├── UserController           ├── User (Users)                           │
│  ├── EnquiryController         ├── Enquiry (Enquiries)                 │
│  ├── AnalyticsController       ├── Location (Locations)                 │
│  ├── NotificationController    ├── Notification (Notifications)           │
│  └── ClawDBotController      ├── Wishlist (Wishlists)                 │
│  📋 Jobs & Queues             🤖 ClawDBot Automation System              │
│  ├── Background Jobs            ├── Commands (Artisan)                     │
│  ├── Queue Management         ├── Observers (Event Handlers)          │
│  ├── Scheduler (Cron Jobs)      ├── Services (Business Logic)               │
│  └── Notifications            ├── Models (Data Layer)                   │
│  📊 Database                 🗄️ MySQL Database                         │
│  ├── Properties Table         ├── Users Table                         │
│  ├── Enquiries Table          ├── Locations Table                     │
│  ├── Notifications Table      ├── Wishlists Table                     │
│  └── Bot Tables              └── Analytics Tables                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 📁 Project Structure

```
MyProperty-RL/
├── backend/                          # 🌐 Laravel Backend
│   ├── app/
│   │   ├── Console/Commands/ClawDBot/    # 🤖 Bot Commands
│   │   ├── Jobs/ClawDBot/               # 📋 Background Jobs
│   │   ├── Services/ClawDBot/            # 🔧 Bot Services
│   │   ├── Notifications/ClawDBot/        # 📧 Bot Notifications
│   │   ├── Models/ClawDBot/               # 📊 Bot Models
│   │   ├── Observers/                   # 👁️ Event Handlers
│   │   ├── Http/Controllers/API/          # 🌐 API Controllers
│   │   └── Http/Requests/ClawDBot/       # 📝 Request Validation
│   ├── config/clawdbot.php             # ⚙️ Bot Configuration
│   ├── database/migrations/             # 🗄️ Database Migrations
│   └── routes/                        # 🛣️ Route Definitions
├── frontend/                         # 🌐 React Frontend
│   ├── src/
│   │   ├── components/                 # 📱 React Components
│   │   ├── pages/                     # 📄 Page Components
│   │   ├── hooks/                      # 🎣 Custom Hooks
│   │   ├── services/                   # 🔧 API Services
│   │   ├── store/                     # 🎨 Redux Store
│   │   └── utils/                      # 🛠️ Utility Functions
│   ├── public/                         # 📦 Build Output
│   └── package.json                   # 📦 Dependencies
├── CLAWDBOT_STRUCTURE_CLEAN.md         # 📋 Structure Documentation
└── README.md                         # 📚 This File
```

## 🔧 Prerequisites

### Backend Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **MySQL**: 8.0 or higher
- **Node.js**: 18.0 or higher (for frontend build tools)
- **Git**: For version control

### Frontend Requirements
- **Node.js**: 18.0 or higher
- **npm** or **yarn**: Latest version
- **React**: 18.0 or higher
- **Vite**: Latest version

### System Requirements
- **RAM**: Minimum 4GB (8GB recommended)
- **Storage**: Minimum 10GB free space
- **OS**: Windows, macOS, or Linux

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/abx15/MypProperty-RL.git
cd MypProperty-RL
```

### 2. Backend Setup

#### Install Dependencies
```bash
cd backend
composer install
```

#### Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` file and configure:
```env
APP_NAME="MyProperty-RL"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_password=your_database_password

# ClawDBot Configuration
CLAWDBOT_ENABLED=true
CLAWDBOT_LOG_LEVEL=info
CLAWDBOT_QUEUE_CONNECTION=database
CLAWDBOT_EMAIL_ENABLED=true
CLAWDBOT_SCHEDULER_ENABLED=true
```

#### Database Setup
```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### 3. Frontend Setup

#### Install Dependencies
```bash
cd frontend
npm install
```

#### Environment Configuration
```bash
cp .env.example .env.local
```

Edit `.env.local` and configure:
```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=MyProperty-RL
```

#### Build Application
```bash
npm run build
```

## 🌐️ Frontend Setup

### Development Server
```bash
cd frontend
npm start
```
The frontend will be available at `http://localhost:3000`

### Production Build
```bash
npm run build
```
The build output will be in the `frontend/public` directory.

### Key Frontend Features
- **React 18** with Hooks
- **Redux Toolkit** for state management
- **React Router** for navigation
- **Axios** for API communication
- **Tailwind CSS** for styling
- **Vite** for fast development

### Frontend Structure
```
src/
├── components/
│   ├── common/           # Reusable components
│   ├── property/          # Property-related components
│   ├── user/              # User management
│   └── admin/              # Admin panel components
├── pages/
│   ├── Home.jsx            # Homepage
│   ├── Properties.jsx       # Property listings
│   ├── PropertyDetail.jsx    # Individual property view
│   ├── Dashboard.jsx        # User dashboard
│   └── AdminDashboard.jsx   # Admin panel
├── services/
│   ├── api.js              # API configuration
│   ├── auth.js             # Authentication service
│   ├── property.js          # Property API calls
│   └── analytics.js         # Analytics API calls
├── store/
│   ├── index.js            # Redux store configuration
│   ├── authSlice.js        # Authentication state
│   ├── propertySlice.js     # Property state
│   └── uiSlice.js          # UI state
└── utils/
    ├── constants.js        # Application constants
    ├── helpers.js          # Helper functions
    └── validators.js        # Form validation
```

## 🔧 Backend Setup

### Development Server
```bash
cd backend
php artisan serve
```
The backend API will be available at `http://localhost:8000`

### Queue Worker Setup
```bash
php artisan queue:work --queue=clawdbot-notifications,clawdbot-reports,clawdbot-maintenance
```

### Scheduler Setup
Add to your crontab:
```bash
* * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Key Backend Features
- **Laravel 12** with PHP 8.2+
- **MySQL** database with Eloquent ORM
- **Queue System** for background processing
- **Scheduler** for automated tasks
- **ClawDBot** automation system
- **RESTful API** for frontend communication
- **Authentication** with Sanctum
- **File Uploads** for property images

### Backend Structure
```
app/
├── Console/
│   └── Commands/ClawDBot/     # 🤖 Bot Commands
├── Jobs/
│   └── ClawDBot/               # 📋 Background Jobs
├── Services/
│   └── ClawDBot/               # 🔧 Business Logic
├── Notifications/
│   └── ClawDBot/               # 📧 Notifications
├── Models/
│   └── ClawDBot/               # 📊 Data Models
├── Observers/                   # 👁️ Event Handlers
├── Http/
│   ├── Controllers/API/          # 🌐 API Endpoints
│   └── Requests/ClawDBot/       # 📝 Validation
└── Providers/
    └── ClawDBotServiceProvider.php  # 🚀 Service Provider
```

## 🤖 ClawDBot Integration

### What is ClawDBot?
ClawDBot is an automated backend bot system built entirely within Laravel that handles:
- **Property Management**: Automatic expiry detection and status updates
- **Notifications**: Multi-channel alerts (Email + Database)
- **Analytics**: Daily/weekly reports and insights
- **Maintenance**: System health monitoring and cleanup
- **AI-Ready**: Architecture prepared for AI integration

### ClawDBot Commands
```bash
# Check bot status
php artisan clawdbot:status

# Process daily summary
php artisan clawdbot:daily-summary

# Generate weekly report
php artisan clawdbot:weekly-report

# Clean up expired properties
php artisan clawdbot:property-cleanup

# Send expiry notifications
php artisan clawdbot:expiry-notifier

# System maintenance
php artisan clawdbot:system-maintenance

# Generate analytics
php artisan clawdbot:analytics

# Manual trigger
php artisan clawdbot:manual-trigger suggestions
```

### ClawDBot Scheduler
The bot runs automatically with the following schedule:
- **8:00 AM Daily** - Property summary reports
- **9:00 AM Daily** - 7-day expiry warnings
- **10:00 AM Daily** - Critical expiry alerts (3 days)
- **11:00 PM Daily** - Property cleanup
- **12:00 AM Daily** - Expired property notifications
- **8:00 AM Mondays** - Weekly analytics reports
- **2:00 AM Sundays** - System maintenance
- **Every 6 hours** - Bot health checks
- **Every 2 hours** - Analytics processing

## 🗄️ Database Setup

### Database Migrations
```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2024_01_01_000001_create_bot_tasks_table.php
```

### Database Tables
- **properties** - Property listings
- **users** - User accounts
- **enquiries** - Property enquiries
- **locations** - Property locations
- **wishlists** - User wishlists
- **notifications** - System notifications
- **bot_tasks** - Bot task execution logs
- **bot_analytics** - Analytics data
- **bot_schedules** - Schedule configuration
- **bot_settings** - Bot configuration

### Database Seeding
```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=ClawDBotSeeder
```

## 🔧 Configuration

### Environment Variables
Key environment variables for ClawDBot:

```env
# ClawDBot Configuration
CLAWDBOT_ENABLED=true
CLAWDBOT_LOG_LEVEL=info
CLAWDBOT_QUEUE_CONNECTION=database
CLAWDBOT_EMAIL_ENABLED=true
CLAWDBOT_SCHEDULER_ENABLED=true

# Performance Settings
CLAWDBOT_BATCH_SIZE=100
CLAWDBOT_TIMEOUT=300
CLAWDBOT_MEMORY_LIMIT=512M

# AI Integration
CLAWDBOT_AI_ENABLED=false
CLAWDBOT_AI_SERVICE=openai
CLAWDBOT_AI_API_KEY=your_api_key_here
CLAWDBOT_AI_MODEL=gpt-3.5-turbo
```

### Configuration Files
- `config/clawdbot.php` - ClawDBot configuration
- `config/queue.php` - Queue configuration
- `config/logging.php` - Logging configuration
- `config/services.php` - Service container configuration

## 🚀 Running the Application

### Development Mode
```bash
# Terminal 1: Backend
cd backend
php artisan serve

# Terminal 2: Frontend
cd frontend
npm start
```

### Production Mode
```bash
# Build frontend
cd frontend
npm run build

# Start backend
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

### Access Points
- **Frontend**: `http://localhost:3000`
- **Backend API**: `http://localhost:8000/api`
- **Admin Panel**: `http://localhost:3000/admin`
- **API Documentation**: `http://localhost:8000/api/documentation`

### Queue Workers
```bash
# Start queue worker
php artisan queue:work

# Start multiple workers
php artisan queue:work --queue=clawdbot-notifications
php artisan queue:work --queue=clawdbot-reports
php artisan queue:work --queue=clawdbot-maintenance
```

## 📊 Available Features

### For Users
- **Browse Properties**: Search and filter property listings
- **Property Details**: View comprehensive property information
- **Contact Owners**: Send enquiries to property owners
- **Wishlist**: Save favorite properties
- **User Dashboard**: Manage personal listings and enquiries
- **Email Notifications**: Receive updates about saved properties

### For Agents
- **Property Management**: Create and manage property listings
- **Lead Management**: Track and respond to enquiries
- **Analytics Dashboard**: View performance metrics
- **Email Templates**: Customize notification emails
- **Bulk Operations**: Manage multiple properties

### For Admins
- **User Management**: Manage users and roles
- **Property Approval**: Approve or reject property listings
- **Analytics Dashboard**: Comprehensive system analytics
- **Bot Management**: Control ClawDBot automation
- **System Settings**: Configure platform settings
- **Security Monitoring**: Track suspicious activities
- **Report Generation**: Generate detailed reports

### ClawDBot Features
- **Automated Property Cleanup**: Remove expired and inactive listings
- **Smart Notifications**: Multi-channel alert system
- **Analytics Processing**: Generate insights and reports
- **System Maintenance**: Health monitoring and optimization
- **AI Integration**: Ready for AI service integration
- **Queue Processing**: Reliable background task execution

## 🔧 API Documentation

### Authentication Endpoints
```http://localhost:8000/api/
├── POST /auth/login              # User login
├── POST /auth/register           # User registration
├── POST /auth/logout             # User logout
├── POST /auth/refresh           # Refresh token
```

### Property Endpoints
```http://localhost:8000/api/
├── GET /properties           # Get all properties
├── GET /properties/{id}      # Get single property
├── POST /properties          # Create property
├── PUT /properties/{id}      # Update property
├── DELETE /properties/{id}   # Delete property
├── GET /properties/search     # Search properties
├── GET /properties/{id}/enquiries # Get property enquiries
```

### User Endpoints
```http://localhost:8000/api/
├── GET /users                 # Get all users
├── GET /users/{id}            # Get user details
├── PUT /users/{id}            # Update user
├── GET /users/{id}/properties   # Get user properties
├── GET /users/{id}/enquiries    # Get user enquiries
```

### ClawDBot Endpoints
```http://localhost:8000/api/clawdbot/
├── GET /status                # Get bot status
├── GET /analytics             # Get analytics data
├── POST /trigger/{command}    # Trigger bot command
├── GET /tasks                # Get task history
├── GET /health               # Get system health
├── GET /statistics           # Get bot statistics
```

## 🧪 Testing

### Backend Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ClawDBot

# Run specific test file
php artisan test tests/Unit/Services/ClawDBot/PropertyManagementServiceTest.php
```

### Frontend Tests
```bash
# Run all tests
npm test

# Run specific test
npm test --testPathPattern="components/PropertyCard.test.js"
```

### Testing ClawDBot
```bash
# Test bot commands
php artisan clawdbot:status --dry-run
php artisan clawdbot:daily-summary --preview
php artisan clawdbot:property-cleanup --dry-run

# Test queue processing
php artisan queue:work --queue=clawdbot-notifications --test
```

## 🚀 Deployment

### Environment Setup
1. **Server Requirements**: Ensure server meets prerequisites
2. **Database Setup**: Configure MySQL database
3. **Environment Variables**: Set up production environment
4. **SSL Certificate**: Install SSL certificate
5. **Domain Configuration**: Configure domain and DNS

### Backend Deployment
```bash
# Install dependencies
composer install --optimize-autoloader

# Optimize for production
php artisan config:cache:clear
php artisan config:config:cache
php artisan route:cache
php artisan view:clear
php artisan config:optimize

# Run database migrations
php artisan migrate --force

# Start production server
php artisan serve --host=your-domain.com --port=80
```

### Frontend Deployment
```bash
# Install dependencies
npm ci

# Build for production
npm run build

# Deploy build files
rsync -avz build/ user@your-server.com:/var/www/html/
```

### Queue Configuration
```bash
# Install supervisor for queue management
sudo apt-get install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/clawdbot-worker.conf

# Add worker configuration
[program:clawdbot-worker]
process_user=www-data
process_group=www-data
command=php artisan queue:work --queue=clawdbot-notifications,clawdbot-reports,clawdbot-maintenance
autostart=true
autorestart=true
numprocs=3
redirect_stderr=/var/log/clawdbot-worker.log
stdout_logfile=/var/log/clawdbot-worker.log

# Save and restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start clawdbot-worker
```

## 🤝 Troubleshooting

### Common Issues

#### Backend Issues
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Reset permissions
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear

# Check logs
tail -f storage/logs/laravel.log
tail -f storage/logs/clawdbot.log
```

#### Frontend Issues
```bash
# Clear node modules
rm -rf node_modules
npm install

# Clear build cache
npm run build --emptyCacheDir

# Check for errors
npm run build 2>&1 | grep ERROR
```

#### Database Issues
```bash
# Check database connection
php artisan tinker
DB::connection()->getPdo()

# Reset database
php artisan migrate:fresh --seed
```

#### Queue Issues
```bash
# Clear failed jobs
php artisan queue:flush

# Restart queue workers
php artisan queue:restart

# Check queue status
php artisan queue:monitor
```

### Getting Help
- **Documentation**: Check the README files
- **Laravel Docs**: [https://laravel.com/docs](https://laravel.com/docs)
- **ClawDBot Docs**: Check CLAWDBOT_STRUCTURE_CLEAN.md
- **GitHub Issues**: [Create an issue](https://github.com/abx15/MypProperty-RL/issues)

### Debug Mode
```bash
# Enable debug mode
APP_DEBUG=true

# Enable verbose logging
CLAWDBOT_DEBUG=true
CLAWDBOT_VERBOSE=true

# Run with preview mode
php artisan clawdbot:daily-summary --preview
```

## 📚 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test your changes
5. Submit a pull request

### Code Standards
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write tests for new features
- Follow Laravel conventions

### Submitting Pull Requests
1. Describe your changes clearly
2. Include screenshots if applicable
3. Test your changes thoroughly
4. Ensure all tests pass
5. Update documentation if needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Support

For support, questions, or contributions:
- 📧 **Documentation**: Check the README files
- 🐛 **GitHub Issues**: [Create an issue](https://github.com/abx15/MypProperty-RL/issues)
- 📧 **Email**: support@myproperty.com
- 💬 **Discord**: [Join our community](https://discord.gg/your-server)

---

**🏠 MyProperty-RL** - Your Complete Real Estate Platform 🏠

Built with ❤️ using Laravel & React
