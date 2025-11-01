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


    /**
     * @OA\Get(
     *     path="/api/groups",
     *     summary="Liste tous les groupes",
     *     tags={"Groupes"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des groupes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Group")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $groups = Group::with('users', 'creator')->get();
        return response()->json($groups);
    }

    /**
     * Créer un nouveau groupe
     */

    /**
     * @OA\Post(
     *     path="/api/groups",
     *     summary="Créer un nouveau groupe",
     *     tags={"Groupes"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Groupe Dev"),
     *             @OA\Property(property="description", type="string", example="Description du groupe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Groupe créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/api/groups/{id}",
     *     summary="Afficher un groupe spécifique",
     *     tags={"Groupes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du groupe",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du groupe",
     *         @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Groupe non trouvé"
     *     )
     * )
     */
    public function show(Group $group)
    {
        $group->load('users', 'creator', 'events');
        return response()->json($group);
    }

    /**
     * Inviter des utilisateurs par email (ajout automatique au groupe)
     */

    /**
     * @OA\Post(
     *     path="/api/groups/{id}/invite",
     *     summary="Inviter des utilisateurs par email (ajout automatique au groupe)",
     *     tags={"Groupes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du groupe",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emails"},
     *             @OA\Property(
     *                 property="emails",
     *                 type="array",
     *                 @OA\Items(type="string", format="email", example="user@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateurs ajoutés et invitations envoyées",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateurs ajoutés et invitations envoyées !")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
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

    /**
     * @OA\Delete(
     *     path="/api/groups/{id}",
     *     summary="Supprimer un groupe",
     *     tags={"Groupes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du groupe",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Groupe supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Groupe supprimé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group); // Optionnel : vérifie que l'utilisateur peut supprimer
        $group->delete();
        return response()->json(['message' => 'Groupe supprimé']);
    }
}
