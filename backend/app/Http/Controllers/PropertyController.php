<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\AnalyticsLog;
use App\Models\Wishlist;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties with filtering and pagination.
     */
    public function index(Request $request)
    {
        $query = Property::with(['agent', 'location', 'primaryImage'])
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by property type
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by location
        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Bedrooms filter
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        // Bathrooms filter
        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        // Area filter
        if ($request->has('min_area')) {
            $query->where('area_sqft', '>=', $request->min_area);
        }
        if ($request->has('max_area')) {
            $query->where('area_sqft', '<=', $request->max_area);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['price', 'created_at', 'views_count', 'area_sqft'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $properties = $query->paginate($perPage);

        // Add wishlist status if user is authenticated
        if ($request->user()) {
            $properties->getCollection()->transform(function ($property) use ($request) {
                $property->is_wishlisted = Wishlist::where('user_id', $request->user()->id)
                    ->where('property_id', $property->id)
                    ->exists();
                return $property;
            });
        }

        return response()->json([
            'properties' => $properties,
            'filters' => [
                'property_types' => ['sale', 'rent'],
                'categories' => ['house', 'apartment', 'commercial', 'land'],
                'price_range' => [
                    'min' => Property::min('price'),
                    'max' => Property::max('price'),
                ],
                'area_range' => [
                    'min' => Property::min('area_sqft'),
                    'max' => Property::max('area_sqft'),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
            'property_type' => 'required|in:sale,rent',
            'category' => 'required|in:house,apartment,commercial,land',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'required|integer|min:1',
            'year_built' => 'nullable|integer|min:1800|max:' . date('Y'),
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'required|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $property = Property::create([
                'agent_id' => $request->user()->id,
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . time(),
                'description' => $request->description,
                'price' => $request->price,
                'location_id' => $request->location_id,
                'property_type' => $request->property_type,
                'category' => $request->category,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'area_sqft' => $request->area_sqft,
                'year_built' => $request->year_built,
                'amenities' => $request->amenities,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]);

            // Handle image uploads
            if ($request->has('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('properties', 'public');
                    
                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_url' => $path,
                        'is_primary' => $index === 0, // First image is primary
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Property created successfully',
                'property' => $property->load(['agent', 'location', 'images'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Request $request, $slug)
    {
        $property = Property::with([
            'agent', 
            'location', 
            'images' => function ($query) {
                $query->orderBy('order');
            }
        ])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        // Increment view count and log analytics
        $property->incrementViews();
        
        AnalyticsLog::create([
            'property_id' => $property->id,
            'user_id' => $request->user()?->id,
            'action' => 'view',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Add wishlist status if user is authenticated
        if ($request->user()) {
            $property->is_wishlisted = Wishlist::where('user_id', $request->user()->id)
                ->where('property_id', $property->id)
                ->exists();
        }

        return response()->json([
            'property' => $property
        ]);
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Check if user owns this property or is admin
        if ($request->user()->id !== $property->agent_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'location_id' => 'sometimes|required|exists:locations,id',
            'property_type' => 'sometimes|required|in:sale,rent',
            'category' => 'sometimes|required|in:house,apartment,commercial,land',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'sometimes|required|integer|min:1',
            'year_built' => 'nullable|integer|min:1800|max:' . date('Y'),
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'sometimes|required|string|max:500',
            'status' => 'sometimes|required|in:active,pending,sold,rented',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = $request->only([
                'title', 'description', 'price', 'location_id', 'property_type',
                'category', 'bedrooms', 'bathrooms', 'area_sqft', 'year_built',
                'amenities', 'latitude', 'longitude', 'address', 'status'
            ]);

            if ($request->has('title')) {
                $updateData['slug'] = Str::slug($request->title) . '-' . time();
            }

            $property->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Property updated successfully',
                'property' => $property->fresh()->load(['agent', 'location', 'images'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Check if user owns this property or is admin
        if ($request->user()->id !== $property->agent_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Soft delete the property
            $property->delete();

            DB::commit();

            return response()->json([
                'message' => 'Property deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status (admin only).
     */
    public function toggleFeatured(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $property = Property::findOrFail($id);
        $property->is_featured = !$property->is_featured;
        $property->save();

        return response()->json([
            'message' => 'Property featured status updated',
            'is_featured' => $property->is_featured
        ]);
    }

    /**
     * Get properties for the authenticated agent.
     */
    public function agentProperties(Request $request)
    {
        $query = Property::with(['location', 'primaryImage'])
            ->where('agent_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property type
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json($properties);
    }

    /**
     * Upload images for a property.
     */
    public function uploadImages(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Check if user owns this property or is admin
        if ($request->user()->id !== $property->agent_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                
                $propertyImage = PropertyImage::create([
                    'property_id' => $property->id,
                    'image_url' => $path,
                    'is_primary' => false,
                    'order' => PropertyImage::where('property_id', $property->id)->max('order') + 1,
                ]);

                $uploadedImages[] = $propertyImage;
            }

            return response()->json([
                'message' => 'Images uploaded successfully',
                'images' => $uploadedImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a property image.
     */
    public function deleteImage(Request $request, $id, $image_id)
    {
        $property = Property::findOrFail($id);

        // Check if user owns this property or is admin
        if ($request->user()->id !== $property->agent_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $image = PropertyImage::where('property_id', $property->id)
            ->where('id', $image_id)
            ->firstOrFail();

        try {
            // Delete from storage
            $imagePath = storage_path('app/public/' . $image->image_url);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $image->delete();

            return response()->json([
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
