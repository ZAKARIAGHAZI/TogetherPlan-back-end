<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for managing user notifications"
 * )
 */
class NotificationController extends Controller
{
    /**
     * List unread notifications
     *
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Get unread notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of unread notifications",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="uuid-notification-id"),
     *                 @OA\Property(property="type", type="string", example="App\\Notifications\\NewEventNotification"),
     *                 @OA\Property(property="data", type="object", example={"message":"You have a new event invitation"}),
     *                 @OA\Property(property="read_at", type="string", nullable=true, format="date-time", example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-10T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->unreadNotifications;
        return response()->json($notifications);
    }

    /**
     * Mark a notification as read
     *
     * @OA\Patch(
     *     path="/api/notifications/{id}/read",
     *     summary="Mark a notification as read",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="string", example="uuid-notification-id")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification marquée comme lue.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found or already read",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Notification introuvable ou déjà lue.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->unreadNotifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification introuvable ou déjà lue.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }
}
