<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Enquiry;

class AgentController extends Controller
{
    /**
     * Get agent dashboard data
     */
    public function dashboard(Request $request)
    {
        $agent = $request->user();
        
        $stats = [
            'total_properties' => $agent->properties()->count(),
            'active_properties' => $agent->properties()->where('status', 'active')->count(),
            'total_enquiries' => $agent->enquiries()->count(),
            'pending_enquiries' => $agent->enquiries()->where('status', 'pending')->count(),
            'recent_properties' => $agent->properties()
                ->with(['location', 'images'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_enquiries' => $agent->enquiries()
                ->with(['property', 'user'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'agent' => $agent->load('role'),
            'stats' => $stats
        ]);
    }

    /**
     * Get agent profile
     */
    public function profile(Request $request)
    {
        $agent = $request->user();
        
        return response()->json([
            'agent' => $agent->load('role'),
            'properties_count' => $agent->properties()->count(),
            'enquiries_count' => $agent->enquiries()->count(),
        ]);
    }

    /**
     * Update agent profile
     */
    public function updateProfile(Request $request)
    {
        $agent = $request->user();

        $validator = validator($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio' => 'nullable|string|max:1000',
            'company' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('avatars', 'public');
            $updateData['avatar'] = $avatarPath;
        }

        $agent->update($updateData);

        return response()->json([
            'agent' => $agent->fresh()->load('role'),
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Get all agents (admin only)
     */
    public function index(Request $request)
    {
        $agents = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })
        ->with(['properties' => function ($query) {
            $query->select('id', 'agent_id');
        }])
        ->paginate($request->get('per_page', 15));

        return response()->json($agents);
    }

    /**
     * Get specific agent details
     */
    public function show($id)
    {
        $agent = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })
        ->with(['properties' => function ($query) {
            $query->where('status', 'active')
                  ->with(['location', 'primaryImage'])
                  ->latest();
        }])
        ->findOrFail($id);

        return response()->json($agent);
    }

    /**
     * Toggle agent status (admin only)
     */
    public function toggleStatus($id)
    {
        $agent = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })->findOrFail($id);

        $agent->update(['is_active' => !$agent->is_active]);

        return response()->json([
            'message' => "Agent status updated to " . ($agent->is_active ? 'active' : 'inactive'),
            'agent' => $agent
        ]);
    }

    /**
     * Delete agent (admin only)
     */
    public function destroy($id)
    {
        $agent = User::whereHas('role', function ($query) {
            $query->where('name', 'agent');
        })->findOrFail($id);

        // Soft delete
        $agent->delete();

        return response()->json([
            'message' => 'Agent deleted successfully'
        ]);
    }
}
