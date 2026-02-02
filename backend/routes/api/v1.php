<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AIController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public property listings (no auth required)
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{slug}', [PropertyController::class, 'show']);
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{slug}', [LocationController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);
    
    // Enquiry routes
    Route::post('/enquiries', [EnquiryController::class, 'store']);
    
    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{property_id}/toggle', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/{property_id}/check', [WishlistController::class, 'check']);
    Route::delete('/wishlist/{property_id}', [WishlistController::class, 'remove']);
    Route::delete('/wishlist', [WishlistController::class, 'clear']);
    Route::get('/wishlist/statistics', [WishlistController::class, 'statistics']);
    
    // User protected routes
    Route::prefix('/user')->middleware('role:user')->group(function () {
        Route::get('/dashboard', [EnquiryController::class, 'dashboard']);
        Route::get('/enquiries', [EnquiryController::class, 'index']);
        Route::get('/enquiries/{id}', [EnquiryController::class, 'show']);
        Route::put('/enquiries/{id}', [EnquiryController::class, 'update']);
        Route::delete('/enquiries/{id}', [EnquiryController::class, 'destroy']);
        
        Route::get('/notifications', function () {
            return response()->json(['message' => 'User notifications - coming soon']);
        });
        
        Route::put('/notifications/{id}/read', function ($id) {
            return response()->json(['message' => "Mark notification {$id} as read - coming soon"]);
        });
    });
    
    // Agent protected routes
    Route::prefix('/agent')->middleware('role:agent')->group(function () {
        Route::get('/dashboard', [AgentController::class, 'dashboard']);
        Route::get('/profile', [AgentController::class, 'profile']);
        Route::put('/profile', [AgentController::class, 'updateProfile']);
        
        Route::get('/properties', [PropertyController::class, 'agentProperties']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::put('/properties/{id}', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
        Route::post('/properties/{id}/images', [PropertyController::class, 'uploadImages']);
        Route::delete('/properties/{id}/images/{image_id}', [PropertyController::class, 'deleteImage']);
        
        Route::get('/enquiries', [EnquiryController::class, 'agentEnquiries']);
        Route::get('/enquiries/{id}', [EnquiryController::class, 'show']);
        Route::put('/enquiries/{id}', [EnquiryController::class, 'update']);
        Route::get('/enquiries/statistics', [EnquiryController::class, 'statistics']);
        
        // AI features for agents
        Route::post('/ai/price-suggestion', [AIController::class, 'priceSuggestion']);
        Route::post('/ai/generate-description', [AIController::class, 'generateDescription']);
    });
    
    // Admin protected routes
    Route::prefix('/admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        
        // Agent management
        Route::get('/agents', [AgentController::class, 'index']);
        Route::get('/agents/{id}', [AgentController::class, 'show']);
        Route::put('/agents/{id}/toggle-status', [AgentController::class, 'toggleStatus']);
        Route::delete('/agents/{id}', [AgentController::class, 'destroy']);
        
        // Property management
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::put('/properties/{id}/toggle-featured', [PropertyController::class, 'toggleFeatured']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
        
        // Location management
        Route::get('/locations', [LocationController::class, 'index']);
        Route::post('/locations', [LocationController::class, 'store']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
        Route::get('/locations/statistics', [LocationController::class, 'statistics']);
        Route::put('/locations/{id}/toggle-status', [LocationController::class, 'toggleStatus']);
        
        // Enquiry management
        Route::get('/enquiries', [EnquiryController::class, 'index']);
        Route::get('/enquiries/{id}', [EnquiryController::class, 'show']);
        Route::delete('/enquiries/{id}', [EnquiryController::class, 'destroy']);
        Route::get('/enquiries/statistics', [EnquiryController::class, 'statistics']);
        
        // AI features for admin
        Route::post('/ai/market-insights', [AIController::class, 'marketInsights']);
        Route::get('/ai/requests', [AIController::class, 'requests']);
        Route::get('/ai/statistics', [AIController::class, 'statistics']);
        
        // Analytics
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::get('/analytics/properties', [AdminController::class, 'propertyAnalytics']);
        Route::get('/analytics/users', [AdminController::class, 'userAnalytics']);
    });
});
