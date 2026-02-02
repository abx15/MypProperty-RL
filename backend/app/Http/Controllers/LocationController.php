<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    /**
     * Display a listing of active locations.
     */
    public function index(Request $request)
    {
        $query = Location::active()->orderBy('city');

        // Search by city, state, or country
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $locations = $query->get();

        // Add property counts for each location
        $locations->transform(function ($location) {
            $location->properties_count = Property::where('location_id', $location->id)
                ->active()
                ->count();
            return $location;
        });

        return response()->json([
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created location (admin only).
     */
    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if location already exists
        $existingLocation = Location::where('city', $request->city)
            ->where('state', $request->state)
            ->where('country', $request->country)
            ->first();

        if ($existingLocation) {
            return response()->json([
                'message' => 'Location already exists',
                'location' => $existingLocation
            ], 422);
        }

        $location = Location::create([
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'slug' => Str::slug($request->city . '-' . $request->state . '-' . $request->country),
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'message' => 'Location created successfully',
            'location' => $location
        ], 201);
    }

    /**
     * Display the specified location with properties.
     */
    public function show(string $slug)
    {
        $location = Location::where('slug', $slug)
            ->with(['properties' => function ($query) {
                $query->active()
                    ->with(['agent', 'primaryImage'])
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'desc');
            }])
            ->active()
            ->firstOrFail();

        // Add statistics
        $location->properties_count = $location->properties->count();
        $location->featured_properties_count = $location->properties->where('is_featured', true)->count();
        
        // Price statistics
        $prices = $location->properties->pluck('price');
        if ($prices->isNotEmpty()) {
            $location->min_price = $prices->min();
            $location->max_price = $prices->max();
            $location->avg_price = round($prices->avg(), 2);
        } else {
            $location->min_price = 0;
            $location->max_price = 0;
            $location->avg_price = 0;
        }

        return response()->json([
            'location' => $location
        ]);
    }

    /**
     * Update the specified location (admin only).
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $location = Location::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'country' => 'sometimes|required|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['city', 'state', 'country', 'is_active']);

        // Update slug if city, state, or country changed
        if ($request->hasAny(['city', 'state', 'country'])) {
            $city = $request->get('city', $location->city);
            $state = $request->get('state', $location->state);
            $country = $request->get('country', $location->country);
            $updateData['slug'] = Str::slug($city . '-' . $state . '-' . $country);
        }

        $location->update($updateData);

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => $location->fresh()
        ]);
    }

    /**
     * Remove the specified location (admin only).
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $location = Location::findOrFail($id);

        // Check if there are any properties in this location
        $propertiesCount = Property::where('location_id', $location->id)->count();
        if ($propertiesCount > 0) {
            return response()->json([
                'message' => 'Cannot delete location with existing properties',
                'properties_count' => $propertiesCount
            ], 422);
        }

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ]);
    }

    /**
     * Get location statistics (admin only).
     */
    public function statistics(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $locations = Location::withCount(['properties' => function ($query) {
            $query->active();
        }])
        ->orderBy('properties_count', 'desc')
        ->get();

        $totalLocations = Location::count();
        $activeLocations = Location::where('is_active', true)->count();
        $totalProperties = Property::active()->count();

        return response()->json([
            'summary' => [
                'total_locations' => $totalLocations,
                'active_locations' => $activeLocations,
                'total_properties' => $totalProperties,
                'avg_properties_per_location' => $totalLocations > 0 ? round($totalProperties / $totalLocations, 2) : 0,
            ],
            'locations' => $locations
        ]);
    }

    /**
     * Toggle location status (admin only).
     */
    public function toggleStatus(Request $request, string $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $location = Location::findOrFail($id);
        $location->is_active = !$location->is_active;
        $location->save();

        return response()->json([
            'message' => 'Location status updated',
            'is_active' => $location->is_active
        ]);
    }
}
