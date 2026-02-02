# Architecture Documentation

This document provides an in-depth overview of the MyProperty Real Estate Management System architecture, including design patterns, technology choices, and system components.

## 🏗️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    MyProperty System                         │
├─────────────────────────────────────────────────────────────┤
│  Frontend (React + TypeScript)                              │
│  ├── User Interface (Tailwind CSS)                          │
│  ├── State Management (React Context + TanStack Query)      │
│  ├── Routing (React Router)                                 │
│  └── API Client (Axios)                                     │
├─────────────────────────────────────────────────────────────┤
│  API Layer (Laravel 12)                                     │
│  ├── RESTful API (Versioned)                               │
│  ├── Authentication (Sanctum)                              │
│  ├── Rate Limiting                                          │
│  └── Request Validation                                     │
├─────────────────────────────────────────────────────────────┤
│  Business Logic (Laravel)                                   │
│  ├── Controllers                                            │
│  ├── Models (Eloquent)                                      │
│  ├── Services                                              │
│  └── AI Integration                                         │
├─────────────────────────────────────────────────────────────┤
│  Data Layer                                                 │
│  ├── Database (SQLite/MySQL/PostgreSQL)                     │
│  ├── File Storage (Local/S3)                               │
│  └── Cache (Redis/File)                                    │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Design Principles

### 1. Separation of Concerns
- **Frontend**: User interface and client-side logic
- **Backend**: Business logic, data management, and API
- **Database**: Data persistence and relationships
- **Storage**: File management and media handling

### 2. Scalability
- **Horizontal Scaling**: Stateless API design
- **Database Optimization**: Proper indexing and query optimization
- **Caching Strategy**: Multi-level caching for performance
- **CDN Ready**: Static asset optimization

### 3. Security
- **Authentication**: Token-based with Sanctum
- **Authorization**: Role-based access control
- **Data Validation**: Input sanitization and validation
- **Rate Limiting**: API protection against abuse

### 4. Maintainability
- **Clean Code**: Following SOLID principles
- **Documentation**: Comprehensive code and API documentation
- **Testing**: Unit and integration tests
- **Version Control**: Git with conventional commits

## 🔧 Technology Stack

### Backend Technologies

#### Core Framework
- **Laravel 12**: Modern PHP framework
  - Eloquent ORM for database operations
  - Blade templating (for emails, notifications)
  - Artisan CLI for task automation
  - Built-in queue system

#### Database & Storage
- **Database**: SQLite (dev), MySQL/PostgreSQL (prod)
- **ORM**: Eloquent with relationships
- **Migrations**: Version-controlled schema changes
- **File Storage**: Local/S3 compatible
- **Cache**: Redis/File driver

#### API & Authentication
- **RESTful API**: Resource-oriented design
- **API Versioning**: `/api/v1` structure
- **Authentication**: Laravel Sanctum
- **Rate Limiting**: Custom middleware
- **CORS**: Proper cross-origin configuration

#### Development Tools
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint
- **Debugging**: Laravel Telescope (optional)
- **Queue Management**: Redis/Database

### Frontend Technologies

#### Core Framework
- **React 19**: Modern component-based UI
- **TypeScript**: Type-safe development
- **Vite**: Fast build tool and dev server

#### State Management
- **React Context**: Global state (auth, theme)
- **TanStack Query**: Server state management
- **Local State**: Component-level state

#### UI & Styling
- **Tailwind CSS**: Utility-first CSS framework
- **Headless UI**: Accessible component primitives
- **Heroicons**: Consistent icon set
- **Custom Design System**: Brand colors and components

#### Routing & Navigation
- **React Router v7**: Client-side routing
- **Route Guards**: Protected and role-based routes
- **Lazy Loading**: Code splitting for performance

#### Data Handling
- **Axios**: HTTP client with interceptors
- **React Hook Form**: Form management
- **Zod**: Schema validation
- **Date-fns**: Date manipulation

#### Animations & Interactions
- **Framer Motion**: Component animations
- **GSAP**: Advanced animations
- **Lenis**: Smooth scrolling
- **Swiper**: Carousel/slider components

#### Charts & Visualization
- **Recharts**: React chart library
- **Chart.js**: Additional charting options
- **React Chart.js 2**: Chart.js React wrapper

## 📊 Database Architecture

### Entity Relationship Diagram

```
Users (1) -----> (1) Roles
  |
  | (1:N)
  |
├─ Properties (1:N) ──> Property Images (1:N)
│     |
│     | (1:N)
│     |
├─ Enquiries
│
├─ Wishlists (N:M) ──> Properties
│
├─ Notifications
│
├─ AI Requests
│
└─ Analytics Logs

Locations (1:N) ──> Properties
```

### Table Structure

#### Users Table
```sql
- id (Primary Key)
- name (String)
- email (Unique)
- password (Hashed)
- role_id (Foreign Key)
- phone (String, Nullable)
- avatar (String, Nullable)
- is_active (Boolean)
- last_login_at (Timestamp, Nullable)
- created_at / updated_at (Timestamps)
```

#### Properties Table
```sql
- id (Primary Key)
- agent_id (Foreign Key to Users)
- title (String)
- slug (Unique String)
- description (Text, Nullable)
- price (Decimal)
- location_id (Foreign Key)
- property_type (Enum: sale/rent)
- category (Enum: house/apartment/commercial/land)
- bedrooms (Integer, Nullable)
- bathrooms (Integer, Nullable)
- area_sqft (Integer)
- year_built (Integer, Nullable)
- amenities (JSON)
- status (Enum: active/pending/sold/rented)
- is_featured (Boolean)
- views_count (Integer)
- ai_price_suggestion (Decimal, Nullable)
- ai_description_generated (Boolean)
- latitude/longitude (Decimal, Nullable)
- address (String)
- created_at / updated_at (Timestamps)
```

#### Enquiries Table
```sql
- id (Primary Key)
- property_id (Foreign Key)
- user_id (Foreign Key)
- message (Text)
- phone (String)
- email (String)
- status (Enum: new/contacted/closed)
- agent_response (Text, Nullable)
- created_at / updated_at (Timestamps)
```

### Database Relationships

#### User Relationships
- **One-to-One**: User → Role
- **One-to-Many**: User → Properties (as Agent)
- **One-to-Many**: User → Enquiries
- **One-to-Many**: User → Wishlists
- **One-to-Many**: User → Notifications

#### Property Relationships
- **Many-to-One**: Property → User (Agent)
- **Many-to-One**: Property → Location
- **One-to-Many**: Property → Images
- **One-to-Many**: Property → Enquiries
- **Many-to-Many**: Property → Users (via Wishlists)

## 🔄 API Architecture

### Request Flow

```
Client Request
    ↓
Rate Limiting Middleware
    ↓
Authentication Middleware
    ↓
Role-Based Authorization
    ↓
Request Validation
    ↓
Controller Action
    ↓
Business Logic (Service Layer)
    ↓
Database Operations
    ↓
Response Formatting (API Resources)
    ↓
Client Response
```

### API Design Patterns

#### 1. Resource Controllers
- **Index**: List resources with filtering/pagination
- **Show**: Single resource details
- **Store**: Create new resource
- **Update**: Modify existing resource
- **Destroy**: Delete resource

#### 2. API Resources
Transform Eloquent models into consistent API responses:
```php
// Example Property Resource
class PropertyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'agent' => new UserResource($this->whenLoaded('agent')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
```

#### 3. Request Validation
Form Request classes for validation:
```php
class StorePropertyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
            // ... other validation rules
        ];
    }
}
```

## 🎨 Frontend Architecture

### Component Hierarchy

```
App
├── Providers (Context Providers)
│   ├── AuthProvider
│   ├── ThemeProvider
│   └── ToastProvider
├── Router
│   ├── Public Routes
│   ├── Auth Routes
│   └── Protected Routes
└── Layouts
    ├── PublicLayout
    ├── AuthLayout
    └── DashboardLayout
```

### State Management Strategy

#### 1. Global State (React Context)
```typescript
// Auth Context
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

// Theme Context
interface ThemeState {
  theme: 'light' | 'dark' | 'system';
  toggleTheme: () => void;
}
```

#### 2. Server State (TanStack Query)
```typescript
// API Queries
const useProperties = (filters: PropertyFilters) => {
  return useQuery({
    queryKey: ['properties', filters],
    queryFn: () => apiService.getProperties(filters),
  });
};
```

#### 3. Local State (useState/useReducer)
```typescript
// Form State
const [formData, setFormData] = useState<PropertyFormData>({
  title: '',
  price: 0,
  // ... other fields
});
```

### Component Architecture

#### 1. Atomic Design Principles
- **Atoms**: Basic UI elements (Button, Input, Badge)
- **Molecules**: Component combinations (Card, Form, Modal)
- **Organisms**: Complex sections (Header, Sidebar, Table)
- **Templates**: Page layouts
- **Pages**: Complete page implementations

#### 2. Custom Hooks
```typescript
// Example custom hook
export const useAuth = () => {
  const { user, isAuthenticated, login, logout } = useContext(AuthContext);
  
  const hasRole = useCallback((role: UserRole) => {
    return user?.role?.name === role;
  }, [user]);

  return { user, isAuthenticated, hasRole, login, logout };
};
```

## 🔐 Security Architecture

### Authentication Flow

```
1. User submits credentials
2. Backend validates credentials
3. Backend generates Sanctum token
4. Token stored in frontend (localStorage)
5. Token included in API requests
6. Backend validates token on each request
7. Token refresh/revocation handling
```

### Authorization Layers

#### 1. Route-Level Protection
```typescript
// Protected Route Component
<ProtectedRoute>
  <DashboardLayout />
</ProtectedRoute>
```

#### 2. Role-Based Access
```typescript
// Role-Based Route Component
<RoleBasedRoute roles={['admin', 'agent']}>
  <PropertyManagement />
</RoleBasedRoute>
```

#### 3. Backend Authorization
```php
// Middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin')->group(function () {
        // Admin-only routes
    });
});
```

### Security Best Practices

#### 1. Input Validation
- Frontend: Zod schema validation
- Backend: Form Request validation
- Sanitization: Escape outputs

#### 2. Data Protection
- Password hashing (bcrypt)
- Token-based authentication
- HTTPS enforcement (production)
- CORS configuration

#### 3. Rate Limiting
- Authentication endpoints: 5/minute
- AI endpoints: 20/minute
- General API: 60/minute

## 🚀 Performance Optimization

### Frontend Optimization

#### 1. Code Splitting
```typescript
// Lazy loading components
const PropertyDetailPage = lazy(() => import('./pages/PropertyDetailPage'));

// Route-based splitting
const AdminDashboard = lazy(() => import('./pages/admin/AdminDashboard'));
```

#### 2. Image Optimization
- WebP format support
- Lazy loading images
- Responsive images
- Image compression

#### 3. Bundle Optimization
- Tree shaking
- Minification
- Gzip compression
- CDN delivery

### Backend Optimization

#### 1. Database Optimization
- Proper indexing
- Query optimization
- Eager loading relationships
- Database connection pooling

#### 2. Caching Strategy
```php
// Route caching
Route::get('/properties', [PropertyController::class, 'index'])
    ->middleware('cache:300'); // 5 minutes

// Query caching
$properties = Cache::remember('properties.featured', 3600, function () {
    return Property::featured()->get();
});
```

#### 3. Queue System
- Email notifications
- Image processing
- AI request processing
- Analytics logging

## 🔄 CI/CD Pipeline

### GitHub Actions Workflow

```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup PHP
      - Install dependencies
      - Run tests
      - Upload coverage

  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup Node.js
      - Install dependencies
      - Run linting
      - Build application

  security-scan:
    runs-on: ubuntu-latest
    steps:
      - Security audit (backend)
      - Security audit (frontend)
```

### Deployment Strategy

#### 1. Development
- Feature branches
- Pull requests
- Automated testing
- Manual deployment to staging

#### 2. Production
- Main branch protection
- Automated testing
- Staging validation
- Automated deployment

## 📈 Monitoring & Analytics

### Application Monitoring

#### 1. Error Tracking
- Laravel error logging
- Frontend error boundaries
- User feedback collection

#### 2. Performance Monitoring
- API response times
- Database query performance
- Frontend bundle size

#### 3. User Analytics
- Page views and interactions
- Feature usage tracking
- Conversion funnels

### Business Intelligence

#### 1. Dashboard Analytics
- Property views and inquiries
- User engagement metrics
- Agent performance tracking

#### 2. AI Insights
- Market trend analysis
- Price optimization suggestions
- Demand forecasting

## 🔮 Future Architecture Considerations

### Scalability Enhancements

#### 1. Microservices Migration
- Property Service
- User Service
- Notification Service
- Analytics Service

#### 2. Event-Driven Architecture
- Event sourcing
- Message queues
- Event streaming

#### 3. Advanced Caching
- Redis clustering
- CDN edge caching
- Database query caching

### Technology Evolution

#### 1. Frontend Framework Updates
- React 19+ features
- Next.js integration
- Progressive Web App

#### 2. Backend Enhancements
- Laravel 12+ features
- GraphQL API
- Real-time updates (WebSockets)

#### 3. Infrastructure Improvements
- Containerization (Docker)
- Kubernetes orchestration
- Cloud-native deployment

---

This architecture documentation serves as a guide for understanding the system's design decisions and provides a foundation for future development and scaling efforts.
