<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\GroupInvitation;

class GroupController extends Controller
{
    /**
     * Afficher tous les groupes
     */
    public function index()
    {
        $groups = Group::with('users', 'creator')->get();
        return response()->json($groups);
    }

    /**
     * Créer un nouveau groupe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = Group::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Ajouter automatiquement le créateur comme membre
        $group->users()->attach(auth()->id());

        return response()->json($group, 201);
    }

    /**
     * Afficher un groupe spécifique
     */
    public function show(Group $group)
    {
        $group->load('users', 'creator', 'events');
        return response()->json($group);
    }

    /**
     * Inviter des utilisateurs par email (ajout automatique au groupe)
     */
    public function invite(Request $request, Group $group)
    {
        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'email|exists:users,email',
        ]);

        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();

            // Ajout automatique si l'utilisateur n'est pas déjà membre
            if (!$group->users()->where('user_id', $user->id)->exists()) {
                $group->users()->attach($user->id);
            }

            // Envoi d'email de notification (optionnel)
            Mail::to($user->email)->send(new GroupInvitation($group, auth()->user()));
        }

        return response()->json(['message' => 'Utilisateurs ajoutés et invitations envoyées !']);
    }

    /**
     * Supprimer un groupe
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group); // Optionnel : vérifie que l'utilisateur peut supprimer
        $group->delete();
        return response()->json(['message' => 'Groupe supprimé']);
    }
}
