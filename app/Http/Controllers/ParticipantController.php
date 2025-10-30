<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    /**
     * Inviter un utilisateur à un événement privé via email
     */
    public function invite(Request $request, $eventId)
    {
        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email'
        ]);

        $event = Event::findOrFail($eventId);

        if ($event->created_by !== Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas inviter des participants.'], 403);
        }

        $results = [];
        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $results[$email] = 'Utilisateur introuvable';
                continue;
            }

            $existing = Participant::where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                $results[$email] = 'Déjà invité';
                continue;
            }

            $participant = Participant::create([
                'event_id' => $eventId,
                'user_id' => $user->id,
                'status' => 'invited',
            ]);

            // Dispatch event (which triggers listener for DB + email notification)
            event(new \App\Events\InvitationCreatedEvent($user, $event));

            $results[$email] = 'Invitation envoyée';
        }

        return response()->json($results);
    }

    /**
     * Répondre à une invitation (accept/refuse)
     */
    public function respondToInvitation(Request $request, $eventId)
    {
        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $participant = Participant::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Vous n\'êtes pas invité à cet événement.'], 403);
        }

        $participant->update(['status' => $request->status]);

        return response()->json([
            'message' => "Invitation {$request->status} avec succès.",
            'participant' => $participant,
        ]);
    }

    /**
     * Lister les participants d’un événement
     */
    public function index($eventId)
    {
        $event = Event::findOrFail($eventId);

        // Vérification pour événements privés
        if ($event->confidentiality === 'private' && $event->created_by !== Auth::id()) {
            $isInvited = Participant::where('event_id', $eventId)
                ->where('user_id', Auth::id())
                ->whereIn('status', ['invited', 'accepted'])
                ->exists();

            if (!$isInvited) {
                return response()->json(['message' => 'Accès refusé à cet événement privé.'], 403);
            }
        }

        $participants = Participant::with('user:id,name,email')
            ->where('event_id', $eventId)
            ->get();

        return response()->json($participants);
    }
}
