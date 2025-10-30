<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /notifications
    public function index(Request $request)
    {
        $notifications = $request->user()->unreadNotifications;
        return response()->json($notifications);
    }

    // PATCH /notifications/{id}/read
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
