<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Property;
use App\Models\AnalyticsLog;

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::with(['property.agent', 'property.location', 'property.primaryImage'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('property');

        return response()->json([
            'wishlist' => $wishlist,
            'count' => $wishlist->count()
        ]);
    }

    /**
     * Toggle property in user's wishlist.
     */
    public function toggle(Request $request, $propertyId)
    {
        $user = $request->user();
        
        // Check if property exists and is active
        $property = Property::active()->findOrFail($propertyId);

        // Check if property is already in wishlist
        $existingWishlist = Wishlist::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();

        if ($existingWishlist) {
            // Remove from wishlist
            $existingWishlist->delete();
            
            return response()->json([
                'message' => 'Property removed from wishlist',
                'is_wishlisted' => false,
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count()
            ]);
        } else {
            // Add to wishlist
            $wishlist = Wishlist::create([
                'user_id' => $user->id,
                'property_id' => $propertyId,
            ]);

            // Log analytics
            AnalyticsLog::create([
                'property_id' => $propertyId,
                'user_id' => $user->id,
                'action' => 'wishlist',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Property added to wishlist',
                'is_wishlisted' => true,
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count()
            ], 201);
        }
    }

    /**
     * Check if property is in user's wishlist.
     */
    public function check(Request $request, $propertyId)
    {
        $user = $request->user();
        
        $isWishlisted = Wishlist::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->exists();

        return response()->json([
            'is_wishlisted' => $isWishlisted
        ]);
    }

    /**
     * Remove property from wishlist.
     */
    public function remove(Request $request, $propertyId)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('property_id', $propertyId)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Property not found in wishlist'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Property removed from wishlist',
            'wishlist_count' => Wishlist::where('user_id', $user->id)->count()
        ]);
    }

    /**
     * Clear entire wishlist.
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        
        $deletedCount = Wishlist::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Wishlist cleared successfully',
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Get wishlist statistics.
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        
        // Get price statistics
        $wishlistProperties = Wishlist::with('property')
            ->where('user_id', $user->id)
            ->get()
            ->pluck('property');

        if ($wishlistProperties->isNotEmpty()) {
            $minPrice = $wishlistProperties->min('price');
            $maxPrice = $wishlistProperties->max('price');
            $avgPrice = round($wishlistProperties->avg('price'), 2);
            $totalValue = $wishlistProperties->sum('price');
        } else {
            $minPrice = 0;
            $maxPrice = 0;
            $avgPrice = 0;
            $totalValue = 0;
        }

        // Category distribution
        $categoryDistribution = $wishlistProperties->groupBy('category')
            ->map(function ($group) {
                return $group->count();
            });

        // Property type distribution
        $typeDistribution = $wishlistProperties->groupBy('property_type')
            ->map(function ($group) {
                return $group->count();
            });

        return response()->json([
            'summary' => [
                'total_properties' => $wishlistCount,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'avg_price' => $avgPrice,
                'total_value' => $totalValue,
            ],
            'distributions' => [
                'categories' => $categoryDistribution,
                'property_types' => $typeDistribution,
            ]
        ]);
    }
}
