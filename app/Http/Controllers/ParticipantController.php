<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


/**
 * @OA\Tag(
 *     name="Participants",
 *     description="API Endpoints for managing event participants"
 * )
 */

class ParticipantController extends Controller
{
    /**
     * Inviter un utilisateur à un événement privé via email
     *
     * @OA\Post(
     *     path="/events/{eventId}/invite",
     *     summary="Inviter des utilisateurs à un événement privé",
     *     tags={"Participants"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID de l'événement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 @OA\Items(type="string", format="email"),
     *                 description="Liste des emails à inviter"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invitation envoyée ou erreurs par email",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"user1@example.com":"Invitation envoyée", "user2@example.com":"Utilisateur introuvable"}
     *         )
     *     ),
     *     @OA\Response(response=403, description="Vous ne pouvez pas inviter des participants")
     * )
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
     *
     * @OA\Post(
     *     path="/events/{eventId}/respond",
     *     summary="Répondre à une invitation à un événement",
     *     tags={"Participants"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID de l'événement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"accepted","declined"},
     *                 description="Statut de la réponse"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse envoyée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invitation accepted avec succès."),
     *             @OA\Property(property="participant", ref="#/components/schemas/Participant")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Vous n'êtes pas invité à cet événement")
     * )
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
     *
     * @OA\Get(
     *     path="/events/{eventId}/participants",
     *     summary="Lister les participants d’un événement",
     *     tags={"Participants"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         description="ID de l'événement",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des participants",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Participant")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Accès refusé à cet événement privé")
     * )
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
