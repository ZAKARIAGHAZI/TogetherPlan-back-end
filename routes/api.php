<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Protected routes (authenticated users only)
Route::middleware('auth:sanctum')->group(function () {

    // List all events (public + private if invited)
    Route::get('/events', [EventController::class, 'index']);

    // Create new event
    Route::post('/events', [EventController::class, 'store']);

    // Show event details
    Route::get('/events/{event}', [EventController::class, 'show']);

    // Update event (only creator)
    Route::put('/events/{event}', [EventController::class, 'update']);

    // Delete event (only creator)
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
});


// Protected routes (authenticated users only)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('events/{event}')->group(function () {
        Route::post('/invite', [ParticipantController::class, 'invite']); // invitation par email
        Route::post('/respond', [ParticipantController::class, 'respondToInvitation']); // accepter/refuser
        Route::get('/participants', [ParticipantController::class, 'index']); // liste participants
    });
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // vote for the best date 
    Route::post('/votes', [VoteController::class, 'store']);
});


// Routes pour les groupes (authentification requise)
Route::middleware('auth:sanctum')->group(function () {
    // Liste tous les groupes
    Route::get('/groups', [GroupController::class, 'index']);
    // Crée un nouveau groupe
    Route::post('/groups', [GroupController::class, 'store']);
    // Affiche un groupe spécifique
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    // Inviter des utilisateurs à un groupe (ajout automatique)
    Route::post('/groups/{group}/invite', [GroupController::class, 'invite']);
    // Supprimer un groupe
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
});

// Routes pour la gestion des utilisateurs (Admin uniquement)
Route::middleware('auth:sanctum')->group(function () {
    // Liste tous les utilisateurs (Admin only)
    Route::get('/users', [UserController::class, 'index']);
    // Mettre à jour un utilisateur (User can update self OR Admin can update anyone)
    Route::put('/users/{id}', [UserController::class, 'update']);
    // Supprimer un utilisateur (User can delete self OR Admin can delete others)
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});


require __DIR__ . '/auth.php';
