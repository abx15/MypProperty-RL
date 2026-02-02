# 🏠️ MyProperty - Real Estate Management System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Node.js Version](https://img.shields.io/badge/node-%3E%3D20.0.0-brightgreen)](https://nodejs.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red)](https://laravel.com/)
[![React Version](https://img.shields.io/badge/react-19.x-blue)](https://reactjs.org/)
[![TypeScript](https://img.shields.io/badge/typescript-5.x-blue)](https://www.typescriptlang.org/)
[![Build Status](https://img.shields.io/github/actions/workflow/status/your-username/myproperty)](https://github.com/your-username/myproperty/actions)
[![Coverage](https://img.shields.io/codecov/c/github/your-username/myproperty)](https://codecov.io/gh/your-username/myproperty)
[![Last Commit](https://img.shields.io/github/last-commit/your-username/myproperty)](https://github.com/your-username/myproperty/commits)
[![Issues](https://img.shields.io/github/issues/your-username/myproperty)](https://github.com/your-username/myproperty/issues)
[![Pull Requests](https://img.shields.io/github/issues-pr/your-username/myproperty)](https://github.com/your-username/myproperty/pulls)
[![Sponsors](https://img.shields.io/github/sponsors/your-username/myproperty)](https://github.com/sponsors/your-username/myproperty)

A modern, full-stack Real Estate Property Management System built with Laravel 12 (Backend) and React 19 + TypeScript (Frontend). This platform enables property agents to manage listings, users to browse and inquire about properties, and administrators to oversee the entire ecosystem with AI-powered insights.

## ✨ Features

### 🏢 For Property Agents
- **Property Management**: Create, update, and manage property listings with images
- **Enquiry Management**: Handle customer inquiries and track communication
- **AI-Powered Tools**: Get price suggestions and generate property descriptions
- **Analytics Dashboard**: Track property views, inquiries, and performance metrics
- **Profile Management**: Professional agent profiles with company information

### 👥 For Regular Users
- **Property Browsing**: Search, filter, and discover properties
- **Advanced Search**: Filter by location, price, type, bedrooms, and more
- **Wishlist Management**: Save favorite properties for later
- **Enquiry System**: Contact agents directly through the platform
- **User Dashboard**: Track inquiries and saved properties

### 🛠️ For Administrators
- **User Management**: Manage agents and user accounts
- **Property Oversight**: Review and manage all property listings
- **Location Management**: Add and manage property locations
- **AI Analytics**: Market insights and trend analysis
- **System Analytics**: Comprehensive platform usage statistics

### 🤖 AI Features
- **Price Suggestions**: AI-powered property pricing recommendations
- **Description Generation**: Automatic property description creation
- **Market Insights**: Real-time market trend analysis
- **Analytics Dashboard**: Data-driven decision making tools

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Authentication**: Laravel Sanctum
- **API**: RESTful with API versioning (/api/v1)
- **Rate Limiting**: Built-in rate limiting middleware
- **Validation**: Form Requests and custom validators

### Frontend
- **Framework**: React 19 with TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with custom design system
- **State Management**: React Context + TanStack Query
- **Routing**: React Router v7 with role-based protection
- **Forms**: React Hook Form + Zod validation
- **UI Components**: Headless UI + Heroicons
- **Animations**: GSAP, Framer Motion, Lenis.js
- **Charts**: Recharts, Chart.js
- **HTTP Client**: Axios with interceptors

### Development Tools
- **Code Quality**: ESLint + Prettier
- **Type Safety**: Strict TypeScript configuration
- **Testing**: PHPUnit (Backend), Jest (Frontend planned)
- **CI/CD**: GitHub Actions workflow
- **Version Control**: Git with conventional commits

## 📁 Project Structure

```
MyProperty/
├── backend/                 # Laravel API Backend
│   ├── app/
│   │   ├── Http/Controllers/    # API Controllers
│   │   ├── Models/              # Eloquent Models
│   │   └── Middleware/          # Custom Middleware
│   ├── database/
│   │   ├── migrations/          # Database Migrations
│   │   └── seeders/             # Database Seeders
│   ├── routes/
│   │   └── api/
│   │       └── v1.php           # API v1 Routes
│   ├── .env.example             # Environment Variables Template
│   └── composer.json            # PHP Dependencies
├── frontend/                # React TypeScript Frontend
│   ├── src/
│   │   ├── components/          # Reusable Components
│   │   ├── contexts/            # React Contexts
│   │   ├── pages/               # Page Components
│   │   ├── layouts/             # Layout Components
│   │   ├── lib/                 # Utilities & API Service
│   │   └── types/               # TypeScript Type Definitions
│   ├── public/                  # Static Assets
│   ├── .env.example             # Environment Variables Template
│   └── package.json             # Node.js Dependencies
├── .github/                 # GitHub Configuration
│   └── workflows/
│       └── ci.yml              # CI/CD Pipeline
├── docs/                    # Documentation
│   ├── API_DOCUMENTATION.md
│   ├── ARCHITECTURE.md
│   ├── FRONTEND_SETUP.md
│   └── BACKEND_SETUP.md
├── README.md                # This file
├── LICENSE                  # MIT License
└── CONTRIBUTING.md          # Contribution Guidelines
```

## 🚀 Quick Start

### Prerequisites
- Node.js 20+ and npm
- PHP 8.2+ and Composer
- Git

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/myproperty.git
cd myproperty
```

### 2. Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```
The API will be available at `http://localhost:8000`

### 3. Frontend Setup
```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```
The frontend will be available at `http://localhost:5173`

### 4. Access the Application
- **Frontend**: http://localhost:5173
- **API Documentation**: http://localhost:8000/api/info
- **Default Admin**: admin@example.com / password
- **Default Agent**: agent@example.com / password
- **Default User**: user@example.com / password

## 📖 Detailed Setup Guides

For detailed step-by-step instructions, please refer to:
- [Backend Setup Guide](docs/BACKEND_SETUP.md)
- [Frontend Setup Guide](docs/FRONTEND_SETUP.md)
- [API Documentation](docs/API_DOCUMENTATION.md)
- [Architecture Overview](docs/ARCHITECTURE.md)

## 🔧 Environment Variables

### Backend (.env)
```env
APP_NAME=MyProperty
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

 Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api/v1
VITE_APP_NAME=MyProperty
VITE_ENABLE_AI_FEATURES=true
VITE_ENABLE_DARK_MODE=true
```

## 🎯 Role-Based Access

### 👤 User (Default Role)
- Browse properties
- Search and filter listings
- Save properties to wishlist
- Submit property inquiries
- View personal dashboard

### 🏢 Agent
- All User permissions
- Create and manage properties
- Upload property images
- Respond to inquiries
- View agent analytics
- Access AI tools for pricing and descriptions

### 🛡️ Admin
- All Agent permissions
- Manage users and agents
- Manage locations
- View platform analytics
- Access AI market insights
- System administration

## 🤖 AI Features

The platform includes several AI-powered features:

1. **Price Suggestion**: Get AI-recommended pricing for properties based on market data
2. **Description Generation**: Automatically generate compelling property descriptions
3. **Market Insights**: Analyze market trends and property performance
4. **Analytics Dashboard**: Data-driven insights for better decision making

For detailed information, see [AI Features Documentation](docs/AI_FEATURES.md).

## 📱 Screenshots & Demo

*(Add screenshots or demo video links here)*

### Property Listing Page
![Property Listing](docs/images/property-listing.png)

### Agent Dashboard
![Agent Dashboard](docs/images/agent-dashboard.png)

### Admin Analytics
![Admin Analytics](docs/images/admin-analytics.png)

## 🧪 Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## 📊 API Endpoints

The API follows RESTful conventions with versioning:

### Authentication
- `POST /api/v1/register` - User registration
- `POST /api/v1/login` - User login
- `POST /api/v1/logout` - User logout

### Properties
- `GET /api/v1/properties` - List properties with filters
- `GET /api/v1/properties/{slug}` - Get property details
- `POST /api/v1/agent/properties` - Create property (Agent)
- `PUT /api/v1/agent/properties/{id}` - Update property (Agent)

### Enquiries
- `POST /api/v1/enquiries` - Submit enquiry
- `GET /api/v1/user/enquiries` - Get user enquiries
- `GET /api/v1/agent/enquiries` - Get agent enquiries

For complete API documentation, see [API Documentation](docs/API_DOCUMENTATION.md).

## 🔄 Development Workflow

### Branching Strategy
- `main` - Production-ready code
- `develop` - Integration branch
- `feature/*` - New features
- `fix/*` - Bug fixes
- `hotfix/*` - Critical fixes

### Commit Messages
Follow conventional commits:
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `style:` - Code style changes
- `refactor:` - Code refactoring
- `test:` - Test additions
- `chore:` - Maintenance tasks

### Pull Request Process
1. Create feature branch from `develop`
2. Make changes with proper commits
3. Test your changes
4. Submit PR to `develop` branch
5. Code review and merge

## 🤝 Contributing

We welcome contributions! Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting pull requests.

### How to Contribute
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP Framework for Web Artisans
- [React](https://reactjs.org/) - A JavaScript library for building user interfaces
- [Tailwind CSS](https://tailwindcss.com/) - A utility-first CSS framework
- [Vite](https://vitejs.dev/) - Next Generation Frontend Tooling

## 📞 Support

If you have any questions or need help:
- Create an [Issue](https://github.com/your-username/myproperty/issues)
- Check our [Documentation](docs/)
- Join our [Discord](https://discord.gg/your-invite) (if available)

---

⭐ If this project helped you, please give it a star!
