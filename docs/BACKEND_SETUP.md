# Backend Setup Guide

This guide will help you set up the Laravel 12 backend API for the MyProperty Real Estate Management System.

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP**: Version 8.2 or higher
- **Composer**: Version 2.0 or higher
- **Database**: SQLite (default), MySQL 8.0+, or PostgreSQL 12+
- **Git**: For version control
- **Web Server**: Apache, Nginx, or PHP built-in server

## 🚀 Quick Setup (5 minutes)

If you want to get started quickly with SQLite, follow these steps:

```bash
# Navigate to backend directory
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Start the development server
php artisan serve
```

That's it! The API will be available at `http://localhost:8000`.

## 📖 Detailed Setup

### 1. Clone and Navigate

```bash
# If you haven't cloned the repository yet
git clone https://github.com/your-username/myproperty.git
cd myproperty/backend

# If you're already in the repository
cd backend
```

### 2. Install Dependencies

```bash
composer install
```

This will install all the required Laravel packages including:
- Laravel Framework 12
- Laravel Sanctum (API authentication)
- Laravel Tinker (interactive shell)
- PHPUnit (testing)
- And other dependencies

### 3. Environment Configuration

Copy the environment template and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
APP_NAME="MyProperty"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=myproperty
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
```

#### Environment Variables Explained

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | `MyProperty` |
| `APP_ENV` | Environment (local/production) | `local` |
| `APP_DEBUG` | Enable debug mode | `true` |
| `DB_CONNECTION` | Database driver | `sqlite` |
| `DB_DATABASE` | Database name | `database/database.sqlite` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_USERNAME` | Database username | `root` |
| `DB_PASSWORD` | Database password | - |

### 4. Generate Application Key

```bash
php artisan key:generate
```

This generates a unique encryption key for your application.

### 5. Database Setup

#### Option 1: SQLite (Recommended for Development)
SQLite is configured by default. Just create the database file:

```bash
touch database/database.sqlite
```

#### Option 2: MySQL
Update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myproperty
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:
```sql
CREATE DATABASE myproperty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Option 3: PostgreSQL
Update your `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=myproperty
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database:
```sql
CREATE DATABASE myproperty;
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

This will create all the necessary tables:
- Users, Roles, and Permissions
- Properties and Property Images
- Locations
- Enquiries
- Wishlists
- Notifications
- AI Requests
- Analytics Logs

### 7. Seed the Database

```bash
php artisan db:seed
```

This will populate your database with:
- Default roles (admin, agent, user)
- Sample locations
- Sample properties
- Default users for testing

### 8. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## 🏗️ Project Structure

Understanding the backend structure:

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # API Controllers
│   │   │   ├── AuthController.php
│   │   │   ├── PropertyController.php
│   │   │   ├── AgentController.php
│   │   │   ├── AdminController.php
│   │   │   └── AIController.php
│   │   ├── Middleware/         # Custom Middleware
│   │   │   ├── RoleMiddleware.php
│   │   │   └── RateLimiting.php
│   │   └── Requests/           # Form Request Validation
│   ├── Models/                 # Eloquent Models
│   │   ├── User.php
│   │   ├── Property.php
│   │   ├── Location.php
│   │   ├── Enquiry.php
│   │   └── ...
│   ├── Providers/              # Service Providers
│   └── Policies/               # Authorization Policies
├── database/
│   ├── migrations/             # Database Migrations
│   ├── seeders/                # Database Seeders
│   └── factories/              # Model Factories
├── routes/
│   ├── api.php                 # API Routes
│   └── api/
│       └── v1.php              # API v1 Routes
├── storage/                    # File Storage
│   ├── app/
│   └── framework/
├── tests/                      # Tests
├── .env.example                # Environment Variables Template
├── composer.json               # PHP Dependencies
└── artisan                     # Laravel Command Line Tool
```

## 🔧 Available Commands

Here are the most useful Laravel Artisan commands:

```bash
# Development
php artisan serve              # Start development server
php artisan tinker              # Interactive shell
php artisan queue:work         # Process queued jobs

# Database
php artisan migrate            # Run migrations
php artisan migrate:rollback   # Rollback migrations
php artisan migrate:fresh      # Fresh migration
php artisan db:seed            # Seed database
php artisan db:show            # Show database info

# Cache & Config
php artisan config:cache       # Cache configuration
php artisan route:cache        # Cache routes
php artisan view:cache         # Cache views
php artisan cache:clear        # Clear cache

# Testing
php artisan test                # Run all tests
php artisan test --filter UserTest  # Run specific test

# API
php artisan route:list         # List all routes
php artisan api:info           # Show API information
```

## 🔐 Authentication

The API uses Laravel Sanctum for token-based authentication:

### Registration
```bash
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "user", // or "agent"
  "phone": "+1234567890"
}
```

### Login
```bash
POST /api/v1/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### Using the Token
Include the token in the Authorization header:
```bash
Authorization: Bearer your_token_here
```

## 🚦 Rate Limiting

The API includes rate limiting to prevent abuse:

- **Authentication endpoints**: 5 requests per minute
- **AI endpoints**: 20 requests per minute
- **General API**: 60 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until limit resets (if exceeded)

## 🤖 AI Features

The backend includes AI-powered features:

### Price Suggestion
```bash
POST /api/v1/agent/ai/price-suggestion
Authorization: Bearer token

{
  "location_id": 1,
  "property_type": "sale",
  "category": "house",
  "bedrooms": 3,
  "bathrooms": 2,
  "area_sqft": 1500
}
```

### Description Generation
```bash
POST /api/v1/agent/ai/generate-description
Authorization: Bearer token

{
  "title": "Beautiful Family Home",
  "property_type": "sale",
  "category": "house",
  "bedrooms": 3,
  "bathrooms": 2,
  "area_sqft": 1500,
  "amenities": ["garage", "garden", "pool"],
  "tone": "professional"
}
```

## 📊 API Versioning

The API uses versioning to ensure backward compatibility:

- **Current Version**: `/api/v1`
- **API Info**: `/api/info`

Future versions will be added as `/api/v2`, `/api/v3`, etc.

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/PropertyTest.php

# Run specific test method
php artisan test --filter test_property_creation
```

### Writing Tests
Tests are located in the `tests/` directory:
- `Feature/` - Feature tests
- `Unit/` - Unit tests

### Test Database
Tests use a separate database to avoid affecting development data.

## 🔍 Debugging

### Enable Debug Mode
Set `APP_DEBUG=true` in your `.env` file.

### Error Logging
Check `storage/logs/laravel.log` for detailed error information.

### Database Queries
Enable query logging in your `.env`:
```env
DB_LOG=true
```

### API Testing
Use tools like Postman or Insomnia to test API endpoints.

## 🐛 Common Issues and Solutions

### Issue: "Class not found" errors
**Solution**: Run `composer install` or `composer dump-autoload`

### Issue: Database connection failed
**Solution**: 
1. Check database credentials in `.env`
2. Ensure database server is running
3. Verify database exists

### Issue: Migration failed
**Solution**: 
1. Check database permissions
2. Run `php artisan migrate:rollback` then `php artisan migrate`
3. Check migration files for syntax errors

### Issue: 404 errors
**Solution**: 
1. Check routes in `routes/api.php`
2. Ensure API versioning is correct
3. Verify HTTP method matches route definition

### Issue: Authentication errors
**Solution**: 
1. Check Sanctum configuration
2. Verify token is valid
3. Ensure user is active

## 📦 Production Deployment

### Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Optimization Commands
```bash
# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Security
1. Set appropriate file permissions
2. Configure web server (Apache/Nginx)
3. Set up SSL certificate
4. Configure firewall rules
5. Regular security updates

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

## 🆘 Getting Help

If you encounter issues:

1. Check this guide for solutions
2. Search existing [GitHub Issues](https://github.com/your-username/myproperty/issues)
3. Create a new issue with detailed information including:
   - Error messages
   - Steps to reproduce
   - Environment details
4. Join our [Discord](https://discord.gg/your-invite) (if available)

---

Happy coding! 🎉
