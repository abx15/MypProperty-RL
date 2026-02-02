<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Enquiry;
use App\Models\Property;
use App\Models\AnalyticsLog;
use App\Models\Notification;

class EnquiryController extends Controller
{
    /**
     * Display a listing of enquiries based on user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            // Admin can see all enquiries
            $query = Enquiry::with(['user', 'property.agent', 'property.location'])
                ->orderBy('created_at', 'desc');
        } elseif ($user->isAgent()) {
            // Agent can see enquiries for their properties
            $query = Enquiry::with(['user', 'property.location'])
                ->whereHas('property', function ($q) use ($user) {
                    $q->where('agent_id', $user->id);
                })
                ->orderBy('created_at', 'desc');
        } else {
            // Regular user can see their own enquiries
            $query = Enquiry::with(['property.agent', 'property.location'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $enquiries = $query->paginate($perPage);

        return response()->json([
            'enquiries' => $enquiries
        ]);
    }

    /**
     * Store a newly created enquiry.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'message' => 'required|string|max:2000',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $property = Property::findOrFail($request->property_id);

        // Check if property is active
        if ($property->status !== 'active') {
            return response()->json([
                'message' => 'Cannot enquire about inactive property'
            ], 422);
        }

        // Check if user already sent an enquiry for this property
        $existingEnquiry = Enquiry::where('property_id', $request->property_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingEnquiry) {
            return response()->json([
                'message' => 'You have already sent an enquiry for this property',
                'enquiry' => $existingEnquiry
            ], 422);
        }

        try {
            $enquiry = Enquiry::create([
                'property_id' => $request->property_id,
                'user_id' => $request->user()->id,
                'message' => $request->message,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => 'new',
            ]);

            // Log analytics
            AnalyticsLog::create([
                'property_id' => $request->property_id,
                'user_id' => $request->user()->id,
                'action' => 'enquiry',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create notification for the agent
            Notification::create([
                'user_id' => $property->agent_id,
                'type' => 'enquiry',
                'title' => 'New Property Enquiry',
                'message' => "You have a new enquiry for {$property->title}",
                'data' => [
                    'property_id' => $property->id,
                    'enquiry_id' => $enquiry->id,
                    'user_name' => $request->user()->name,
                ],
            ]);

            // TODO: Trigger AI summary job here
            // GenerateAIDescriptionJob::dispatch($enquiry);

            return response()->json([
                'message' => 'Enquiry sent successfully',
                'enquiry' => $enquiry->load(['property', 'user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send enquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified enquiry.
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $enquiry = Enquiry::with(['user', 'property.agent', 'property.location'])
            ->findOrFail($id);

        // Check permissions
        if ($user->isAdmin()) {
            // Admin can see all enquiries
            return response()->json(['enquiry' => $enquiry]);
        } elseif ($user->isAgent()) {
            // Agent can see enquiries for their properties
            if ($enquiry->property->agent_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            // Regular user can see their own enquiries
            if ($enquiry->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json(['enquiry' => $enquiry]);
    }

    /**
     * Update the specified enquiry (agent response).
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:new,contacted,closed',
            'agent_response' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $enquiry = Enquiry::findOrFail($id);

        // Check permissions - only agent or admin can respond
        if (!$user->isAgent() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If agent, check if they own the property
        if ($user->isAgent() && $enquiry->property->agent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized - you can only respond to enquiries for your properties'], 403);
        }

        $updateData = [
            'agent_response' => $request->agent_response,
            'status' => $request->get('status', 'contacted'),
        ];

        $enquiry->update($updateData);

        // Create notification for the user
        Notification::create([
            'user_id' => $enquiry->user_id,
            'type' => 'enquiry_response',
            'title' => 'Response to Your Enquiry',
            'message' => "Agent has responded to your enquiry for {$enquiry->property->title}",
            'data' => [
                'property_id' => $enquiry->property_id,
                'enquiry_id' => $enquiry->id,
                'agent_name' => $enquiry->property->agent->name,
            ],
        ]);

        return response()->json([
            'message' => 'Enquiry updated successfully',
            'enquiry' => $enquiry->fresh()->load(['user', 'property.agent'])
        ]);
    }

    /**
     * Remove the specified enquiry (admin only).
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $enquiry = Enquiry::findOrFail($id);
        $enquiry->delete();

        return response()->json([
            'message' => 'Enquiry deleted successfully'
        ]);
    }

    /**
     * Get enquiry statistics (admin and agent).
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            // Admin gets platform-wide statistics
            $totalEnquiries = Enquiry::count();
            $newEnquiries = Enquiry::where('status', 'new')->count();
            $contactedEnquiries = Enquiry::where('status', 'contacted')->count();
            $closedEnquiries = Enquiry::where('status', 'closed')->count();

            // Recent trends
            $enquiriesThisMonth = Enquiry::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $enquiriesLastMonth = Enquiry::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();

        } elseif ($user->isAgent()) {
            // Agent gets statistics for their properties
            $totalEnquiries = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->count();

            $newEnquiries = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->where('status', 'new')->count();

            $contactedEnquiries = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->where('status', 'contacted')->count();

            $closedEnquiries = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->where('status', 'closed')->count();

            // Recent trends
            $enquiriesThisMonth = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $enquiriesLastMonth = Enquiry::whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();

        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'summary' => [
                'total_enquiries' => $totalEnquiries,
                'new_enquiries' => $newEnquiries,
                'contacted_enquiries' => $contactedEnquiries,
                'closed_enquiries' => $closedEnquiries,
                'enquiries_this_month' => $enquiriesThisMonth,
                'enquiries_last_month' => $enquiriesLastMonth,
                'monthly_growth' => $enquiriesLastMonth > 0 ? 
                    round((($enquiriesThisMonth - $enquiriesLastMonth) / $enquiriesLastMonth) * 100, 2) : 0,
            ],
            'status_distribution' => [
                'new' => $newEnquiries,
                'contacted' => $contactedEnquiries,
                'closed' => $closedEnquiries,
            ]
        ]);
    }

    /**
     * Get user dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_enquiries' => $user->enquiries()->count(),
            'pending_enquiries' => $user->enquiries()->where('status', 'new')->count(),
            'contacted_enquiries' => $user->enquiries()->where('status', 'contacted')->count(),
            'wishlist_count' => $user->wishlists()->count(),
            'recent_enquiries' => $user->enquiries()
                ->with(['property.agent', 'property.location', 'property.primaryImage'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'user' => $user->load('role'),
            'stats' => $stats
        ]);
    }

    /**
     * Get agent enquiries
     */
    public function agentEnquiries(Request $request)
    {
        $user = $request->user();
        
        $query = Enquiry::with(['user', 'property.location', 'property.primaryImage'])
            ->whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            })
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $perPage = $request->get('per_page', 15);
        $enquiries = $query->paginate($perPage);

        return response()->json($enquiries);
    }
}
