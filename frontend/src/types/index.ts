// User types
export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
  role_id: number;
  is_active: boolean;
  last_login_at?: string;
  created_at: string;
  updated_at: string;
  role: Role;
}

export interface Role {
  id: number;
  name: 'admin' | 'agent' | 'user';
  permissions?: string[];
  created_at: string;
  updated_at: string;
}

// Property types
export interface Property {
  id: number;
  agent_id: number;
  title: string;
  slug: string;
  description?: string;
  price: number;
  location_id: number;
  property_type: 'sale' | 'rent';
  category: 'house' | 'apartment' | 'commercial' | 'land';
  bedrooms?: number;
  bathrooms?: number;
  area_sqft: number;
  year_built?: number;
  amenities?: string[];
  status: 'active' | 'pending' | 'sold' | 'rented';
  is_featured: boolean;
  views_count: number;
  ai_price_suggestion?: number;
  ai_description_generated: boolean;
  latitude?: number;
  longitude?: number;
  address: string;
  created_at: string;
  updated_at: string;
  agent: User;
  location: Location;
  images: PropertyImage[];
  primary_image?: PropertyImage;
  is_wishlisted?: boolean;
}

export interface PropertyImage {
  id: number;
  property_id: number;
  image_url: string;
  is_primary: boolean;
  order: number;
  created_at: string;
  updated_at: string;
}

// Location types
export interface Location {
  id: number;
  name: string;
  slug: string;
  description?: string;
  city: string;
  state: string;
  country: string;
  postal_code?: string;
  latitude?: number;
  longitude?: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  properties_count?: number;
}

// Enquiry types
export interface Enquiry {
  id: number;
  property_id: number;
  user_id: number;
  message: string;
  phone: string;
  email: string;
  status: 'new' | 'contacted' | 'closed';
  agent_response?: string;
  created_at: string;
  updated_at: string;
  property: Property;
  user: User;
}

// Wishlist types
export interface Wishlist {
  id: number;
  user_id: number;
  property_id: number;
  created_at: string;
  updated_at: string;
  property: Property;
}

// Notification types
export interface Notification {
  id: number;
  user_id: number;
  type: string;
  title: string;
  message: string;
  data?: Record<string, any>;
  is_read: boolean;
  created_at: string;
  updated_at: string;
}

// AI Request types
export interface AIRequest {
  id: number;
  user_id: number;
  type: 'price_suggestion' | 'description_generation' | 'market_insights';
  input_data: Record<string, any>;
  response_data?: Record<string, any>;
  status: 'processing' | 'completed' | 'failed';
  error_message?: string;
  created_at: string;
  updated_at: string;
  user: User;
}

// API Response types
export interface ApiResponse<T = any> {
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

// Auth types
export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  role: 'user' | 'agent';
}

export interface AuthResponse {
  user: User;
  token: string;
  token_type: string;
}

// Form types
export interface PropertyFormData {
  title: string;
  description?: string;
  price: number;
  location_id: number;
  property_type: 'sale' | 'rent';
  category: 'house' | 'apartment' | 'commercial' | 'land';
  bedrooms?: number;
  bathrooms?: number;
  area_sqft: number;
  year_built?: number;
  amenities?: string[];
  latitude?: number;
  longitude?: number;
  address: string;
  images?: File[];
}

export interface EnquiryFormData {
  property_id: number;
  message: string;
  phone: string;
  email: string;
}

export interface ProfileFormData {
  name: string;
  phone?: string;
  avatar?: File;
  bio?: string;
  company?: string;
  license?: string;
}

// Filter types
export interface PropertyFilters {
  property_type?: 'sale' | 'rent';
  category?: 'house' | 'apartment' | 'commercial' | 'land';
  location_id?: number;
  min_price?: number;
  max_price?: number;
  bedrooms?: number;
  bathrooms?: number;
  min_area?: number;
  max_area?: number;
  search?: string;
  sort_by?: 'price' | 'created_at' | 'views_count' | 'area_sqft';
  sort_order?: 'asc' | 'desc';
  per_page?: number;
}

// Dashboard types
export interface UserDashboard {
  user: User;
  stats: {
    total_enquiries: number;
    pending_enquiries: number;
    contacted_enquiries: number;
    wishlist_count: number;
    recent_enquiries: Enquiry[];
  };
}

export interface AgentDashboard {
  agent: User;
  stats: {
    total_properties: number;
    active_properties: number;
    total_enquiries: number;
    pending_enquiries: number;
    recent_properties: Property[];
    recent_enquiries: Enquiry[];
  };
}

export interface AdminDashboard {
  stats: {
    total_users: number;
    total_agents: number;
    total_properties: number;
    active_properties: number;
    total_enquiries: number;
    pending_enquiries: number;
    total_locations: number;
    ai_requests_today: number;
  };
  recent_activity: {
    recent_users: User[];
    recent_properties: Property[];
    recent_enquiries: Enquiry[];
  };
}

// Analytics types
export interface Analytics {
  user_growth: Array<{ date: string; count: number }>;
  property_stats: Array<{ date: string; count: number }>;
  enquiry_trends: Array<{ date: string; count: number }>;
  popular_locations: Array<{ id: number; name: string; properties_count: number }>;
  ai_usage: Array<{ date: string; count: number }>;
}

// UI State types
export type Theme = 'light' | 'dark' | 'system';

export interface UIState {
  theme: Theme;
  sidebarOpen: boolean;
  loading: boolean;
}

// Error types
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status?: number;
}

// Utility types
export type UserRole = 'admin' | 'agent' | 'user';
export type PropertyType = 'sale' | 'rent';
export type PropertyCategory = 'house' | 'apartment' | 'commercial' | 'land';
export type EnquiryStatus = 'new' | 'contacted' | 'closed';
export type PropertyStatus = 'active' | 'pending' | 'sold' | 'rented';
