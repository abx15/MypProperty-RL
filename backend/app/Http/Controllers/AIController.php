<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\AIRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    /**
     * Generate AI price suggestion for a property
     */
    public function priceSuggestion(Request $request)
    {
        $validator = validator($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'property_type' => 'required|in:sale,rent',
            'category' => 'required|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'amenities' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        // Log the AI request
        $aiRequest = AIRequest::create([
            'user_id' => $user->id,
            'type' => 'price_suggestion',
            'input_data' => $request->all(),
            'status' => 'processing',
        ]);

        try {
            // Simulate AI processing (replace with actual AI service)
            $suggestedPrice = $this->calculatePriceSuggestion($request->all());
            
            $aiRequest->update([
                'status' => 'completed',
                'response_data' => ['suggested_price' => $suggestedPrice],
            ]);

            return response()->json([
                'suggested_price' => $suggestedPrice,
                'confidence' => $this->calculateConfidence($request->all()),
                'factors' => $this->getPriceFactors($request->all()),
                'request_id' => $aiRequest->id,
            ]);

        } catch (\Exception $e) {
            $aiRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to generate price suggestion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate AI property description
     */
    public function generateDescription(Request $request)
    {
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'property_type' => 'required|in:sale,rent',
            'category' => 'required|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqft' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'amenities' => 'nullable|array',
            'location_name' => 'nullable|string',
            'key_features' => 'nullable|array',
            'tone' => 'nullable|in:professional,friendly,luxury,minimal',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        // Log the AI request
        $aiRequest = AIRequest::create([
            'user_id' => $user->id,
            'type' => 'description_generation',
            'input_data' => $request->all(),
            'status' => 'processing',
        ]);

        try {
            // Generate description (replace with actual AI service)
            $description = $this->generatePropertyDescription($request->all());
            $shortDescription = $this->generateShortDescription($request->all());
            
            $aiRequest->update([
                'status' => 'completed',
                'response_data' => [
                    'description' => $description,
                    'short_description' => $shortDescription,
                ],
            ]);

            return response()->json([
                'description' => $description,
                'short_description' => $shortDescription,
                'suggested_title' => $this->generateSuggestedTitle($request->all()),
                'key_highlights' => $this->extractKeyHighlights($request->all()),
                'request_id' => $aiRequest->id,
            ]);

        } catch (\Exception $e) {
            $aiRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to generate description',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate market insights for admin
     */
    public function marketInsights(Request $request)
    {
        $validator = validator($request->all(), [
            'location_id' => 'nullable|exists:locations,id',
            'property_type' => 'nullable|in:sale,rent',
            'period' => 'nullable|in:7,30,90,365',
            'insight_type' => 'nullable|in:pricing,trends,demand,competition',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        // Log the AI request
        $aiRequest = AIRequest::create([
            'user_id' => $user->id,
            'type' => 'market_insights',
            'input_data' => $request->all(),
            'status' => 'processing',
        ]);

        try {
            $insights = $this->generateMarketInsights($request->all());
            
            $aiRequest->update([
                'status' => 'completed',
                'response_data' => $insights,
            ]);

            return response()->json($insights);

        } catch (\Exception $e) {
            $aiRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to generate market insights',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get AI requests history
     */
    public function requests(Request $request)
    {
        $requests = AIRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($requests);
    }

    /**
     * Get AI usage statistics
     */
    public function statistics(Request $request)
    {
        $period = $request->get('period', 30); // days
        
        $stats = [
            'total_requests' => AIRequest::where('created_at', '>=', now()->subDays($period))->count(),
            'successful_requests' => AIRequest::where('created_at', '>=', now()->subDays($period))
                ->where('status', 'completed')->count(),
            'failed_requests' => AIRequest::where('created_at', '>=', now()->subDays($period))
                ->where('status', 'failed')->count(),
            'requests_by_type' => AIRequest::selectRaw('type, count(*) as count')
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('type')
                ->get(),
            'daily_usage' => AIRequest::selectRaw('DATE(created_at) as date, count(*) as count')
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }

    // Helper methods (these would connect to actual AI services)
    private function calculatePriceSuggestion($data)
    {
        // Simulated price calculation - replace with actual AI service
        $basePrice = 250000;
        $pricePerSqft = 150;
        
        if (isset($data['area_sqft'])) {
            $basePrice = $data['area_sqft'] * $pricePerSqft;
        }
        
        if (isset($data['bedrooms'])) {
            $basePrice += $data['bedrooms'] * 15000;
        }
        
        if (isset($data['bathrooms'])) {
            $basePrice += $data['bathrooms'] * 10000;
        }
        
        // Adjust for property type
        if ($data['property_type'] === 'rent') {
            $basePrice = $basePrice * 0.005; // Convert to monthly rent
        }
        
        return round($basePrice, 2);
    }

    private function calculateConfidence($data)
    {
        $confidence = 0.7; // Base confidence
        
        if (isset($data['area_sqft']) && isset($data['bedrooms'])) {
            $confidence += 0.2;
        }
        
        if (isset($data['year_built'])) {
            $confidence += 0.1;
        }
        
        return min($confidence, 0.95);
    }

    private function getPriceFactors($data)
    {
        return [
            'location' => 'High demand area',
            'size' => $data['area_sqft'] ?? 'Unknown' . ' sqft',
            'bedrooms' => $data['bedrooms'] ?? 'Unknown',
            'property_type' => $data['property_type'],
        ];
    }

    private function generatePropertyDescription($data)
    {
        $tone = $data['tone'] ?? 'professional';
        $bedrooms = $data['bedrooms'] ?? 'spacious';
        $bathrooms = $data['bathrooms'] ?? 'modern';
        $area = $data['area_sqft'] ?? 'generous';
        
        $descriptions = [
            'professional' => "This exceptional {$data['category']} property offers {$bedrooms} bedrooms and {$bathrooms} bathrooms across {$area} square feet of carefully designed living space. Located in " . ($data['location_name'] ?? 'a prime location') . ", this {$data['property_type']} opportunity represents outstanding value in today's market.",
            'friendly' => "Welcome to your dream home! This lovely {$data['category']} features {$bedrooms} cozy bedrooms and {$bathrooms} beautiful bathrooms. With {$area} square feet of living space, there's plenty of room to make wonderful memories in this " . ($data['location_name'] ?? 'charming neighborhood') . ".",
            'luxury' => "Indulge in the epitome of sophistication with this magnificent {$data['category']} residence. Spanning an impressive {$area} square feet, this distinguished property boasts {$bedrooms} elegant bedrooms and {$bathrooms} exquisite bathrooms. Situated in the prestigious " . ($data['location_name'] ?? 'exclusive enclave') . ", this is a rare opportunity to acquire a truly exceptional home.",
            'minimal' => "{$data['category']} with {$bedrooms} bedrooms, {$bathrooms} bathrooms. {$area} sqft. {$data['property_type']}. " . ($data['location_name'] ?? 'Prime location') . "."
        ];
        
        return $descriptions[$tone] ?? $descriptions['professional'];
    }

    private function generateShortDescription($data)
    {
        $bedrooms = $data['bedrooms'] ?? 'multiple';
        $location = $data['location_name'] ?? 'great location';
        return "Beautiful {$data['category']} with {$bedrooms} bedrooms in {$location}.";
    }

    private function generateSuggestedTitle($data)
    {
        $location = $data['location_name'] ?? 'Prime Location';
        return "Stunning {$data['category']} in {$location}";
    }

    private function extractKeyHighlights($data)
    {
        $highlights = [];
        
        if (isset($data['bedrooms']) && $data['bedrooms'] > 0) {
            $highlights[] = "{$data['bedrooms']} Bedrooms";
        }
        
        if (isset($data['bathrooms']) && $data['bathrooms'] > 0) {
            $highlights[] = "{$data['bathrooms']} Bathrooms";
        }
        
        if (isset($data['area_sqft'])) {
            $highlights[] = "{$data['area_sqft']} sqft";
        }
        
        if (isset($data['amenities']) && is_array($data['amenities'])) {
            $highlights = array_merge($highlights, array_slice($data['amenities'], 0, 3));
        }
        
        return $highlights;
    }

    private function generateMarketInsights($data)
    {
        return [
            'market_trend' => 'upward',
            'average_price' => 350000,
            'price_change' => '+5.2%',
            'demand_level' => 'high',
            'inventory_level' => 'low',
            'days_on_market' => 45,
            'recommendations' => [
                'Current market conditions favor sellers',
                'Properties in this area are selling quickly',
                'Consider pricing competitively to attract multiple offers'
            ],
            'forecast' => [
                'next_quarter' => 'Continued growth expected',
                'price_prediction' => '+3-5%',
                'demand_outlook' => 'Strong'
            ]
        ];
    }
}
