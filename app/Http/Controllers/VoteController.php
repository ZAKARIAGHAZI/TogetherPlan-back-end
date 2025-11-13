<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\DateOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Store a new vote and update the best date
 *
 * @OA\Post(
 *     path="/api/votes",
 *     summary="Submit a vote for a date option",
 *     description="Allows an authenticated user to vote 'yes', 'maybe', or 'no' on a date option. Recalculates the best date for the event.",
 *     operationId="storeVote",
 *     tags={"Votes"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"date_option_id","vote"},
 *             @OA\Property(property="date_option_id", type="integer", example=5, description="ID of the date option to vote on"),
 *             @OA\Property(property="vote", type="string", enum={"yes","maybe","no"}, example="yes", description="Type of vote")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Vote submitted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Vote submitted successfully!"),
 *             @OA\Property(property="vote", ref="#/components/schemas/Vote"),
 *             @OA\Property(property="best_date_id", type="integer", example=16, description="ID of the current best date for the event")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="User has already voted for this date option",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="You have already voted for this date.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="The date option id field is required."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
class VoteController extends Controller
{
    /**
     * Store a new vote and update the best date
     */
    public function store(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'date_option_id' => 'required|exists:date_options,id',
            'vote' => 'required|in:yes,maybe,no',
        ]);

        // ✅ Retrieve the date option with its event
        $dateOption = DateOption::with('event')->findOrFail($validated['date_option_id']);
        $event = $dateOption->event;

        // ✅ Ensure the user hasn’t already voted for this *date option*
        $existingVote = Vote::where('user_id', Auth::id())
            ->where('date_option_id', $dateOption->id)
            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'You have already voted for this date.'], 400);
        }

        // ✅ Assign numeric points for vote type
        $points = match ($validated['vote']) {
            'yes' => 2,
            'maybe' => 1,
            'no' => 0,
        };

        // ✅ Create the new vote
        $vote = Vote::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'date_option_id' => $dateOption->id,
            'vote' => $validated['vote'],
            'points' => $points,
        ]);

        // ✅ Recalculate the best date for this event
        $bestOption = $event->dateOptions()
            ->withSum('votes', 'points')
            ->get()
            ->sortByDesc('votes_sum_points')
            ->first();

        Log::info('Best option:', ['bestOption' => $bestOption]);
        // ✅ Update event’s best_date_id if a top option exists
        if ($bestOption) {
            $event->update(['best_date_id' => $bestOption->id]);
            $event->refresh();
        }

        return response()->json([
            'message' => 'Vote submitted successfully!',
            'vote' => $vote,
            'best_date_id' => $event->best_date_id,
        ]);
    }
}
