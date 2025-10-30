<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * List events: all public or private events if the user is invited
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Filter: show public events or private events if user is invited
        $query->where(function ($q) {
            $q->where('privacy', 'public')
                ->orWhereHas('participants', function ($q2) {
                    $q2->where('user_id', Auth::id())
                        ->where('status', 'invited');
                });
        });

        // Optional filters
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $events = $query->with('group')->latest()->get();

        return response()->json($events);
    }

    /**
     * Show event details
     */
    public function show(Event $event)
    {
        // Private event: check if user is invited or creator
        if ($event->privacy === 'private') {
            $isInvited = $event->participants()
                ->where('user_id', Auth::id())
                ->where('status', 'invited')
                ->exists();

            if (!$isInvited && $event->created_by !== Auth::id()) {
                return response()->json(['message' => 'You are not authorized to view this private event'], 403);
            }
        }

        return response()->json($event->load('dateOptions', 'participants', 'group'));
    }

    /**
     * Store a new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'privacy' => 'required|in:public,private',
            'date_options' => 'array',
            'invitees' => 'array', // array of user IDs for private event
            'group_id' => 'nullable|exists:groups,id', // optional group
        ]);

        // Create event
        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'category' => $validated['category'],
            'privacy' => $validated['privacy'],
            'created_by' => Auth::id(),
            'group_id' => $validated['group_id'] ?? null,
        ]);

        // Add date options if provided
        if (!empty($validated['date_options'])) {
            foreach ($validated['date_options'] as $date) {
                $event->dateOptions()->create([
                    'proposed_date' => $date['proposed_date'],
                    'proposed_time' => $date['proposed_time'] ?? null,
                ]);
            }
        }

        // Invite participants if event is private
        if ($event->privacy === 'private' && !empty($validated['invitees'])) {
            foreach ($validated['invitees'] as $userId) {
                $event->participants()->create([
                    'user_id' => $userId,
                    'status' => 'invited',
                ]);
                // Optional: send email notification here
            }
        }

        // Add group members automatically if a group is linked
        if (!empty($validated['group_id'])) {
            $group = Group::find($validated['group_id']);
            foreach ($group->users as $user) {
                if ($user->id !== Auth::id()) {
                    $event->participants()->create([
                        'user_id' => $user->id,
                        'status' => 'invited',
                    ]);
                    // Optional: send email notification here
                }
            }
        }

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event->load('dateOptions', 'participants', 'group'),
        ], 201);
    }

    /**
     * Update an event
     */
    public function update(Request $request, Event $event)
    {
        // Only creator can update
        if ($event->created_by !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to update this event'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'privacy' => 'sometimes|required|in:public,private',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event->load('group'),
        ]);
    }

    /**
     * Delete an event
     */
    public function destroy(Event $event)
    {
        // Only creator can delete
        if ($event->created_by !== Auth::id()) {
            return response()->json(['message' => 'You are not authorized to delete this event'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
