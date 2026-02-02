# API Documentation

This document provides comprehensive information about the MyProperty REST API endpoints, authentication, and usage examples.

## 🌐 Base URL

```
Development: http://localhost:8000/api/v1
Production: https://your-domain.com/api/v1
```

## 🔐 Authentication

The API uses token-based authentication via Laravel Sanctum.

### Getting a Token

#### Register a New User
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "user", // "user" or "agent"
  "phone": "+1234567890"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": {
      "id": 3,
      "name": "user"
    },
    "created_at": "2024-01-01T12:00:00.000000Z"
  },
  "token": "1|abc123def456...",
  "token_type": "Bearer"
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": {
      "id": 3,
      "name": "user"
    }
  },
  "token": "1|abc123def456...",
  "token_type": "Bearer"
}
```

### Using the Token

Include the token in the Authorization header for all protected requests:

```http
Authorization: Bearer 1|abc123def456...
```

## 📚 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/register` | Register new user | No |
| POST | `/login` | Login user | No |
| POST | `/logout` | Logout user | Yes |
| GET | `/user` | Get current user | Yes |
| PUT | `/profile` | Update profile | Yes |
| PUT | `/password` | Change password | Yes |

### Properties

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/properties` | List properties | No | - |
| GET | `/properties/{slug}` | Get property details | No | - |
| POST | `/agent/properties` | Create property | Yes | Agent |
| PUT | `/agent/properties/{id}` | Update property | Yes | Agent/Owner |
| DELETE | `/agent/properties/{id}` | Delete property | Yes | Agent/Owner |
| POST | `/agent/properties/{id}/images` | Upload images | Yes | Agent/Owner |
| DELETE | `/agent/properties/{id}/images/{image_id}` | Delete image | Yes | Agent/Owner |
| PUT | `/admin/properties/{id}/toggle-featured` | Toggle featured | Yes | Admin |

### Locations

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/locations` | List locations | No | - |
| GET | `/locations/{slug}` | Get location details | No | - |
| POST | `/admin/locations` | Create location | Yes | Admin |
| PUT | `/admin/locations/{id}` | Update location | Yes | Admin |
| DELETE | `/admin/locations/{id}` | Delete location | Yes | Admin |
| GET | `/admin/locations/statistics` | Location statistics | Yes | Admin |
| PUT | `/admin/locations/{id}/toggle-status` | Toggle status | Yes | Admin |

### Enquiries

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| POST | `/enquiries` | Submit enquiry | Yes | User |
| GET | `/user/enquiries` | Get user enquiries | Yes | User |
| GET | `/user/enquiries/{id}` | Get enquiry details | Yes | User/Owner |
| GET | `/agent/enquiries` | Get agent enquiries | Yes | Agent |
| GET | `/agent/enquiries/{id}` | Get enquiry details | Yes | Agent/Owner |
| PUT | `/agent/enquiries/{id}` | Update enquiry | Yes | Agent/Owner |
| GET | `/agent/enquiries/statistics` | Enquiry statistics | Yes | Agent |
| GET | `/admin/enquiries` | Get all enquiries | Yes | Admin |
| GET | `/admin/enquiries/{id}` | Get enquiry details | Yes | Admin |
| DELETE | `/admin/enquiries/{id}` | Delete enquiry | Yes | Admin |
| GET | `/admin/enquiries/statistics` | Enquiry statistics | Yes | Admin |

### Wishlist

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/wishlist` | Get wishlist | Yes |
| POST | `/wishlist/{property_id}/toggle` | Toggle wishlist | Yes |
| GET | `/wishlist/{property_id}/check` | Check if in wishlist | Yes |
| DELETE | `/wishlist/{property_id}` | Remove from wishlist | Yes |
| DELETE | `/wishlist` | Clear wishlist | Yes |
| GET | `/wishlist/statistics` | Wishlist statistics | Yes |

### Agents

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/admin/agents` | List agents | Yes | Admin |
| GET | `/admin/agents/{id}` | Get agent details | Yes | Admin |
| PUT | `/admin/agents/{id}/toggle-status` | Toggle agent status | Yes | Admin |
| DELETE | `/admin/agents/{id}` | Delete agent | Yes | Admin |
| GET | `/agent/profile` | Get agent profile | Yes | Agent |
| PUT | `/agent/profile` | Update agent profile | Yes | Agent |

### AI Features

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| POST | `/agent/ai/price-suggestion` | Get price suggestion | Yes | Agent |
| POST | `/agent/ai/generate-description` | Generate description | Yes | Agent |
| POST | `/admin/ai/market-insights` | Get market insights | Yes | Admin |
| GET | `/admin/ai/requests` | Get AI requests | Yes | Admin |
| GET | `/admin/ai/statistics` | AI usage statistics | Yes | Admin |

### Analytics

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/admin/analytics` | Get analytics data | Yes | Admin |
| GET | `/admin/analytics/properties` | Property analytics | Yes | Admin |
| GET | `/admin/analytics/users` | User analytics | Yes | Admin |

### User Dashboard

| Method | Endpoint | Description | Auth Required | Role |
|--------|----------|-------------|---------------|------|
| GET | `/user/dashboard` | Get user dashboard | Yes | User |
| GET | `/agent/dashboard` | Get agent dashboard | Yes | Agent |
| GET | `/admin/dashboard` | Get admin dashboard | Yes | Admin |

## 📋 Request/Response Examples

### Get Properties with Filters

```http
GET /api/v1/properties?property_type=sale&category=house&min_price=100000&max_price=500000&bedrooms=3&per_page=12
Authorization: Bearer 1|abc123def456...
```

**Response:**
```json
{
  "properties": {
    "data": [
      {
        "id": 1,
        "title": "Beautiful Family Home",
        "slug": "beautiful-family-home-123456789",
        "description": "A lovely family home with 3 bedrooms...",
        "price": 350000.00,
        "property_type": "sale",
        "category": "house",
        "bedrooms": 3,
        "bathrooms": 2,
        "area_sqft": 1500,
        "status": "active",
        "is_featured": true,
        "views_count": 125,
        "address": "123 Main St, City, State",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "agent": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com",
          "phone": "+1234567890"
        },
        "location": {
          "id": 1,
          "name": "Downtown",
          "city": "New York",
          "state": "NY"
        },
        "primary_image": {
          "id": 1,
          "image_url": "properties/property1_image1.jpg",
          "is_primary": true
        },
        "is_wishlisted": false
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 48,
    "from": 1,
    "to": 12
  },
  "filters": {
    "property_types": ["sale", "rent"],
    "categories": ["house", "apartment", "commercial", "land"],
    "price_range": {
      "min": 50000,
      "max": 2500000
    },
    "area_range": {
      "min": 500,
      "max": 5000
    }
  }
}
```

### Create Property

```http
POST /api/v1/agent/properties
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "title": "Modern Apartment",
  "description": "A beautiful modern apartment in the heart of the city",
  "price": 450000,
  "location_id": 1,
  "property_type": "sale",
  "category": "apartment",
  "bedrooms": 2,
  "bathrooms": 2,
  "area_sqft": 1200,
  "year_built": 2020,
  "amenities": ["parking", "gym", "pool", "security"],
  "latitude": 40.7128,
  "longitude": -74.0060,
  "address": "456 Park Ave, New York, NY"
}
```

**Response:**
```json
{
  "message": "Property created successfully",
  "property": {
    "id": 10,
    "title": "Modern Apartment",
    "slug": "modern-apartment-123456789",
    "description": "A beautiful modern apartment in the heart of the city",
    "price": 450000.00,
    "property_type": "sale",
    "category": "apartment",
    "bedrooms": 2,
    "bathrooms": 2,
    "area_sqft": 1200,
    "year_built": 2020,
    "amenities": ["parking", "gym", "pool", "security"],
    "status": "active",
    "is_featured": false,
    "views_count": 0,
    "latitude": 40.7128,
    "longitude": -74.0060,
    "address": "456 Park Ave, New York, NY",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "agent": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "location": {
      "id": 1,
      "name": "Downtown",
      "city": "New York",
      "state": "NY"
    },
    "images": []
  }
}
```

### Submit Enquiry

```http
POST /api/v1/enquiries
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "property_id": 1,
  "message": "I'm interested in this property. When can I schedule a viewing?",
  "phone": "+1234567890",
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "message": "Enquiry sent successfully",
  "enquiry": {
    "id": 5,
    "message": "I'm interested in this property. When can I schedule a viewing?",
    "phone": "+1234567890",
    "email": "john@example.com",
    "status": "new",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "property": {
      "id": 1,
      "title": "Beautiful Family Home",
      "slug": "beautiful-family-home-123456789"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

### AI Price Suggestion

```http
POST /api/v1/agent/ai/price-suggestion
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "location_id": 1,
  "property_type": "sale",
  "category": "house",
  "bedrooms": 3,
  "bathrooms": 2,
  "area_sqft": 1500,
  "year_built": 2015,
  "amenities": ["garage", "garden", "pool"]
}
```

**Response:**
```json
{
  "suggested_price": 375000.00,
  "confidence": 0.85,
  "factors": {
    "location": "High demand area",
    "size": "1500 sqft",
    "bedrooms": "3",
    "property_type": "sale"
  },
  "request_id": 123
}
```

## 🔍 Query Parameters

### Properties Endpoint

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `property_type` | string | Filter by property type | `sale`, `rent` |
| `category` | string | Filter by category | `house`, `apartment` |
| `location_id` | integer | Filter by location ID | `1` |
| `min_price` | decimal | Minimum price | `100000` |
| `max_price` | decimal | Maximum price | `500000` |
| `bedrooms` | integer | Minimum bedrooms | `3` |
| `bathrooms` | integer | Minimum bathrooms | `2` |
| `min_area` | integer | Minimum area in sqft | `1000` |
| `max_area` | integer | Maximum area in sqft | `3000` |
| `search` | string | Search in title/description | `modern` |
| `sort_by` | string | Sort field | `price`, `created_at` |
| `sort_order` | string | Sort order | `asc`, `desc` |
| `per_page` | integer | Items per page | `12` |

### Pagination

All list endpoints support pagination:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Current page |
| `per_page` | integer | 15 | Items per page (max: 50) |

**Pagination Response Structure:**
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 75,
  "from": 1,
  "to": 15
}
```

## 🚨 Error Handling

The API returns standard HTTP status codes and consistent error responses.

### Error Response Format

```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

### Common HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

### Rate Limiting

Rate limiting headers are included in responses:

| Header | Description |
|--------|-------------|
| `X-RateLimit-Limit` | Total requests allowed |
| `X-RateLimit-Remaining` | Remaining requests |
| `Retry-After` | Seconds until limit resets |

## 🔄 API Versioning

The API uses URL versioning to ensure backward compatibility.

### Current Version
- **Base URL**: `/api/v1`

### Version Information
```http
GET /api/info
```

**Response:**
```json
{
  "name": "MyProperty",
  "version": "1.0.0",
  "status": "active",
  "endpoints": {
    "v1": "http://localhost:8000/api/v1"
  }
}
```

## 🧪 Testing the API

### Using cURL

```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Get properties
curl -X GET http://localhost:8000/api/v1/properties \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Create property
curl -X POST http://localhost:8000/api/v1/agent/properties \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Property","price":250000,"location_id":1,"property_type":"sale","category":"house","bedrooms":2,"bathrooms":1,"area_sqft":1000,"address":"123 Test St"}'
```

### Using Postman

1. Import the API collection (if available)
2. Set base URL to `http://localhost:8000/api/v1`
3. Configure authentication:
   - Type: Bearer Token
   - Token: Your auth token
4. Test endpoints

## 📝 Data Models

### User Model
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "avatar": "avatars/user1.jpg",
  "is_active": true,
  "last_login_at": "2024-01-01T12:00:00.000000Z",
  "created_at": "2024-01-01T12:00:00.000000Z",
  "role": {
    "id": 3,
    "name": "user"
  }
}
```

### Property Model
```json
{
  "id": 1,
  "title": "Beautiful Family Home",
  "slug": "beautiful-family-home-123456789",
  "description": "A lovely family home...",
  "price": 350000.00,
  "property_type": "sale",
  "category": "house",
  "bedrooms": 3,
  "bathrooms": 2,
  "area_sqft": 1500,
  "year_built": 2010,
  "amenities": ["garage", "garden", "pool"],
  "status": "active",
  "is_featured": true,
  "views_count": 125,
  "address": "123 Main St, City, State",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "created_at": "2024-01-01T12:00:00.000000Z",
  "agent": {...},
  "location": {...},
  "images": [...],
  "is_wishlisted": false
}
```

## 🔧 SDK and Libraries

While we don't provide official SDKs, here are some helpful libraries:

### JavaScript/TypeScript
```javascript
// Using Axios
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});

// Get properties
const properties = await api.get('/properties');
```

### Python
```python
import requests

headers = {
    'Content-Type': 'application/json',
    'Authorization': f'Bearer {token}'
}

# Get properties
response = requests.get('http://localhost:8000/api/v1/properties', headers=headers)
```

## 📞 Support

For API-related issues:

1. Check this documentation first
2. Review the [GitHub Issues](https://github.com/your-username/myproperty/issues)
3. Create a new issue with:
   - Endpoint being called
   - Request payload
   - Response received
   - Expected behavior

---

Happy coding! 🎉
