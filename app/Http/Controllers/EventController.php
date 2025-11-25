<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * List events
     *
     * @OA\Get(
     *     path="/api/events",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     summary="Get list of events",
     *     description="List all public events or private events if the user is invited",
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Filter events by location",
     *         required=false,
     *         @OA\Schema(type="string", example="Casablanca")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter events by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Meeting")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of events",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Weekly Meeting"),
     *                 @OA\Property(property="description", type="string", example="Team weekly sync"),
     *                 @OA\Property(property="location", type="string", example="Casablanca"),
     *                 @OA\Property(property="category", type="string", example="Meeting"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="start_date", type="string", format="date-time", example="2025-11-13T10:00:00Z"),
     *                 @OA\Property(property="end_date", type="string", format="date-time", example="2025-11-13T12:00:00Z"),
     *                 @OA\Property(
     *                     property="creator",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Zakaria Ghazi"),
     *                     @OA\Property(property="email", type="string", example="user@example.com")
     *                 ),
     *                 @OA\Property(
     *                     property="group",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Groupe Dev")
     *                 )
     *             )
     *         )
     *     )
     * )
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

        $events = $query->with('creator', 'dateOptions','group')->latest()->get();

        return response()->json($events);
    }

    /**
     * Show event details
     *
     * @OA\Get(
     *     path="/api/events/{id}",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     summary="Get details of a single event",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event details",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to view this event")
     * )
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
        return response()->json($event->load('creator','dateOptions.votes', 'participants', 'group','bestDate'));
    }

    /**
     * Store a new event
     *
     * @OA\Post(
     *     path="/api/events",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     summary="Create a new event",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","location","category","privacy"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Team Meeting"),
     *             @OA\Property(property="description", type="string", example="Discuss project progress"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="Casablanca"),
     *             @OA\Property(property="category", type="string", maxLength=100, example="Meeting"),
     *             @OA\Property(property="privacy", type="string", enum={"public","private"}, example="private"),
     *             @OA\Property(
     *                 property="date_options",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="proposed_date", type="string", format="date", example="2025-11-15"),
     *                     @OA\Property(property="proposed_time", type="string", format="time", example="14:30:00")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="invitees",
     *                 type="array",
     *                 description="Array of user IDs to invite",
     *                 @OA\Items(type="integer", example=2)
     *             ),
     *             @OA\Property(property="group_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     )
     * )
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
            'event' => $event->load('creator','dateOptions', 'participants', 'group'),
        ], 201);
    }

    /**
     * Update an event
     *
     * @OA\Put(
     *     path="/api/events/{id}",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     summary="Update an existing event",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="location", type="string", maxLength=255),
     *             @OA\Property(property="category", type="string", maxLength=100),
     *             @OA\Property(property="privacy", type="string", enum={"public","private"}),
     *             @OA\Property(property="group_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to update this event")
     * )
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
     *
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     summary="Delete an event",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Event deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized to delete this event")
     * )
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
