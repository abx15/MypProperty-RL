import axios from 'axios';
import type { AxiosInstance, AxiosResponse } from 'axios';
import type { 
  ApiResponse, 
  PaginatedResponse, 
  User, 
  Property, 
  Location, 
  Enquiry, 
  Wishlist,
  LoginRequest,
  RegisterRequest,
  AuthResponse,
  PropertyFormData,
  EnquiryFormData,
  PropertyFilters,
  UserDashboard,
  AgentDashboard,
  AdminDashboard,
  AIRequest,
  Analytics
} from '../types';

class ApiService {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    // Request interceptor
    this.client.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor
    this.client.interceptors.response.use(
      (response: AxiosResponse) => {
        return response;
      },
      (error) => {
        if (error.response?.status === 401) {
          // Token expired or invalid
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          window.location.href = '/login';
        }
        return Promise.reject(error);
      }
    );
  }

  // Auth endpoints
  async login(credentials: LoginRequest): Promise<ApiResponse<AuthResponse>> {
    const response = await this.client.post('/login', credentials);
    return response.data;
  }

  async register(userData: RegisterRequest): Promise<ApiResponse<AuthResponse>> {
    const response = await this.client.post('/register', userData);
    return response.data;
  }

  async logout(): Promise<ApiResponse> {
    const response = await this.client.post('/logout');
    return response.data;
  }

  async getCurrentUser(): Promise<ApiResponse<{ user: User }>> {
    const response = await this.client.get('/user');
    return response.data;
  }

  async updateProfile(userData: FormData): Promise<ApiResponse<{ user: User }>> {
    const response = await this.client.put('/profile', userData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async changePassword(passwords: { current_password: string; password: string; password_confirmation: string }): Promise<ApiResponse> {
    const response = await this.client.put('/password', passwords);
    return response.data;
  }

  // Property endpoints
  async getProperties(filters?: PropertyFilters): Promise<ApiResponse<{ properties: PaginatedResponse<Property>; filters: any }>> {
    const response = await this.client.get('/properties', { params: filters });
    return response.data;
  }

  async getProperty(slug: string): Promise<ApiResponse<{ property: Property }>> {
    const response = await this.client.get(`/properties/${slug}`);
    return response.data;
  }

  async createProperty(propertyData: FormData): Promise<ApiResponse<{ property: Property }>> {
    const response = await this.client.post('/agent/properties', propertyData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async updateProperty(id: number, propertyData: Partial<PropertyFormData>): Promise<ApiResponse<{ property: Property }>> {
    const response = await this.client.put(`/agent/properties/${id}`, propertyData);
    return response.data;
  }

  async deleteProperty(id: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/agent/properties/${id}`);
    return response.data;
  }

  async getAgentProperties(filters?: PropertyFilters): Promise<ApiResponse<PaginatedResponse<Property>>> {
    const response = await this.client.get('/agent/properties', { params: filters });
    return response.data;
  }

  async uploadPropertyImages(id: number, images: FormData): Promise<ApiResponse<{ images: any[] }>> {
    const response = await this.client.post(`/agent/properties/${id}/images`, images, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async deletePropertyImage(propertyId: number, imageId: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/agent/properties/${propertyId}/images/${imageId}`);
    return response.data;
  }

  async togglePropertyFeatured(id: number): Promise<ApiResponse<{ is_featured: boolean }>> {
    const response = await this.client.put(`/admin/properties/${id}/toggle-featured`);
    return response.data;
  }

  // Location endpoints
  async getLocations(): Promise<ApiResponse<Location[]>> {
    const response = await this.client.get('/locations');
    return response.data;
  }

  async getLocation(slug: string): Promise<ApiResponse<{ location: Location }>> {
    const response = await this.client.get(`/locations/${slug}`);
    return response.data;
  }

  async createLocation(locationData: any): Promise<ApiResponse<{ location: Location }>> {
    const response = await this.client.post('/admin/locations', locationData);
    return response.data;
  }

  async updateLocation(id: number, locationData: any): Promise<ApiResponse<{ location: Location }>> {
    const response = await this.client.put(`/admin/locations/${id}`, locationData);
    return response.data;
  }

  async deleteLocation(id: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/admin/locations/${id}`);
    return response.data;
  }

  async getLocationStatistics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/admin/locations/statistics');
    return response.data;
  }

  async toggleLocationStatus(id: number): Promise<ApiResponse> {
    const response = await this.client.put(`/admin/locations/${id}/toggle-status`);
    return response.data;
  }

  // Enquiry endpoints
  async createEnquiry(enquiryData: EnquiryFormData): Promise<ApiResponse<{ enquiry: Enquiry }>> {
    const response = await this.client.post('/enquiries', enquiryData);
    return response.data;
  }

  async getEnquiries(filters?: any): Promise<ApiResponse<{ enquiries: PaginatedResponse<Enquiry> }>> {
    const response = await this.client.get('/user/enquiries', { params: filters });
    return response.data;
  }

  async getEnquiry(id: number): Promise<ApiResponse<{ enquiry: Enquiry }>> {
    const response = await this.client.get(`/user/enquiries/${id}`);
    return response.data;
  }

  async updateEnquiry(id: number, data: { status?: string; agent_response?: string }): Promise<ApiResponse<{ enquiry: Enquiry }>> {
    const response = await this.client.put(`/agent/enquiries/${id}`, data);
    return response.data;
  }

  async deleteEnquiry(id: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/admin/enquiries/${id}`);
    return response.data;
  }

  async getAgentEnquiries(filters?: any): Promise<ApiResponse<PaginatedResponse<Enquiry>>> {
    const response = await this.client.get('/agent/enquiries', { params: filters });
    return response.data;
  }

  async getEnquiryStatistics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/agent/enquiries/statistics');
    return response.data;
  }

  // Wishlist endpoints
  async getWishlist(): Promise<ApiResponse<Wishlist[]>> {
    const response = await this.client.get('/wishlist');
    return response.data;
  }

  async toggleWishlist(propertyId: number): Promise<ApiResponse> {
    const response = await this.client.post(`/wishlist/${propertyId}/toggle`);
    return response.data;
  }

  async checkWishlist(propertyId: number): Promise<ApiResponse<{ is_wishlisted: boolean }>> {
    const response = await this.client.get(`/wishlist/${propertyId}/check`);
    return response.data;
  }

  async removeFromWishlist(propertyId: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/wishlist/${propertyId}`);
    return response.data;
  }

  async clearWishlist(): Promise<ApiResponse> {
    const response = await this.client.delete('/wishlist');
    return response.data;
  }

  async getWishlistStatistics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/wishlist/statistics');
    return response.data;
  }

  // Dashboard endpoints
  async getUserDashboard(): Promise<ApiResponse<UserDashboard>> {
    const response = await this.client.get('/user/dashboard');
    return response.data;
  }

  async getAgentDashboard(): Promise<ApiResponse<AgentDashboard>> {
    const response = await this.client.get('/agent/dashboard');
    return response.data;
  }

  async getAdminDashboard(): Promise<ApiResponse<AdminDashboard>> {
    const response = await this.client.get('/admin/dashboard');
    return response.data;
  }

  // Agent management endpoints
  async getAgents(): Promise<ApiResponse<PaginatedResponse<User>>> {
    const response = await this.client.get('/admin/agents');
    return response.data;
  }

  async getAgent(id: number): Promise<ApiResponse<{ agent: User }>> {
    const response = await this.client.get(`/admin/agents/${id}`);
    return response.data;
  }

  async toggleAgentStatus(id: number): Promise<ApiResponse> {
    const response = await this.client.put(`/admin/agents/${id}/toggle-status`);
    return response.data;
  }

  async deleteAgent(id: number): Promise<ApiResponse> {
    const response = await this.client.delete(`/admin/agents/${id}`);
    return response.data;
  }

  async getAgentProfile(): Promise<ApiResponse<{ agent: User }>> {
    const response = await this.client.get('/agent/profile');
    return response.data;
  }

  async updateAgentProfile(userData: FormData): Promise<ApiResponse<{ agent: User }>> {
    const response = await this.client.put('/agent/profile', userData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  // AI endpoints
  async getPriceSuggestion(data: any): Promise<ApiResponse<any>> {
    const response = await this.client.post('/agent/ai/price-suggestion', data);
    return response.data;
  }

  async generateDescription(data: any): Promise<ApiResponse<any>> {
    const response = await this.client.post('/agent/ai/generate-description', data);
    return response.data;
  }

  async getMarketInsights(data?: any): Promise<ApiResponse<any>> {
    const response = await this.client.post('/admin/ai/market-insights', data || {});
    return response.data;
  }

  async getAIRequests(): Promise<ApiResponse<PaginatedResponse<AIRequest>>> {
    const response = await this.client.get('/admin/ai/requests');
    return response.data;
  }

  async getAIStatistics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/admin/ai/statistics');
    return response.data;
  }

  // Analytics endpoints
  async getAnalytics(filters?: any): Promise<ApiResponse<Analytics>> {
    const response = await this.client.get('/admin/analytics', { params: filters });
    return response.data;
  }

  async getPropertyAnalytics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/admin/analytics/properties');
    return response.data;
  }

  async getUserAnalytics(): Promise<ApiResponse<any>> {
    const response = await this.client.get('/admin/analytics/users');
    return response.data;
  }
}

export const apiService = new ApiService();
export default apiService;
