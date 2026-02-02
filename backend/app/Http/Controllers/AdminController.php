<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Enquiry;
use App\Models\Location;
use App\Models\AnalyticsLog;
use App\Models\AIRequest;

class AdminController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function dashboard(Request $request)
    {
        $stats = [
            'total_users' => User::count(),
            'total_agents' => User::whereHas('role', fn($q) => $q->where('name', 'agent'))->count(),
            'total_properties' => Property::count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'total_enquiries' => Enquiry::count(),
            'pending_enquiries' => Enquiry::where('status', 'pending')->count(),
            'total_locations' => Location::count(),
            'ai_requests_today' => AIRequest::whereDate('created_at', today())->count(),
        ];

        $recentActivity = [
            'recent_users' => User::with('role')->latest()->take(5)->get(),
            'recent_properties' => Property::with(['agent', 'location'])->latest()->take(5)->get(),
            'recent_enquiries' => Enquiry::with(['user', 'property', 'agent'])->latest()->take(5)->get(),
        ];

        return response()->json([
            'stats' => $stats,
            'recent_activity' => $recentActivity
        ]);
    }

    /**
     * Get general analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        
        $analytics = [
            'user_growth' => $this->getUserGrowth($period),
            'property_stats' => $this->getPropertyStats($period),
            'enquiry_trends' => $this->getEnquiryTrends($period),
            'popular_locations' => $this->getPopularLocations(),
            'ai_usage' => $this->getAIUsageStats($period),
        ];

        return response()->json($analytics);
    }

    /**
     * Get property-specific analytics
     */
    public function propertyAnalytics(Request $request)
    {
        $analytics = [
            'properties_by_type' => Property::selectRaw('property_type, count(*) as count')
                ->groupBy('property_type')
                ->get(),
            'properties_by_category' => Property::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->get(),
            'price_ranges' => $this->getPriceRanges(),
            'featured_properties' => Property::where('is_featured', true)->count(),
            'most_viewed' => Property::orderBy('views_count', 'desc')->take(10)->get(['id', 'title', 'views_count']),
        ];

        return response()->json($analytics);
    }

    /**
     * Get user-specific analytics
     */
    public function userAnalytics(Request $request)
    {
        $analytics = [
            'users_by_role' => User::join('roles', 'users.role_id', '=', 'roles.id')
                ->selectRaw('roles.name as role, count(*) as count')
                ->groupBy('roles.name')
                ->get(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'top_agents' => User::whereHas('role', fn($q) => $q->where('name', 'agent'))
                ->withCount('properties')
                ->orderBy('properties_count', 'desc')
                ->take(10)
                ->get(['id', 'name', 'properties_count']),
        ];

        return response()->json($analytics);
    }

    /**
     * Get user growth data
     */
    private function getUserGrowth($period)
    {
        return User::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get property statistics
     */
    private function getPropertyStats($period)
    {
        return Property::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get enquiry trends
     */
    private function getEnquiryTrends($period)
    {
        return Enquiry::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get popular locations
     */
    private function getPopularLocations()
    {
        return Location::withCount('properties')
            ->orderBy('properties_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'properties_count']);
    }

    /**
     * Get AI usage statistics
     */
    private function getAIUsageStats($period)
    {
        return AIRequest::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get price ranges
     */
    private function getPriceRanges()
    {
        $ranges = [
            '0-100000' => Property::where('price', '<', 100000)->count(),
            '100000-250000' => Property::whereBetween('price', [100000, 250000])->count(),
            '250000-500000' => Property::whereBetween('price', [250000, 500000])->count(),
            '500000-1000000' => Property::whereBetween('price', [500000, 1000000])->count(),
            '1000000+' => Property::where('price', '>', 1000000)->count(),
        ];

        return collect($ranges)->map(function ($count, $range) {
            return [
                'range' => $range,
                'count' => $count,
            ];
        })->values();
    }
}
