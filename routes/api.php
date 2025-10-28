<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipantController;


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
});



require __DIR__ . '/auth.php';
