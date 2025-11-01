<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\DateOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get the date option and its event
        $dateOption = DateOption::findOrFail($validated['date_option_id']);
        $event = $dateOption->event;

        // ✅ Check if user already voted for this date
        $existingVote = Vote::where('user_id', Auth::id())
            ->where('date_option_id', $dateOption->id)
            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'You have already voted for this date.'], 400);
        }

        // ✅ Assign points
        $points = match ($validated['vote']) {
            'yes' => 2,
            'maybe' => 1,
            'no' => 0,
        };

        // ✅ Create the vote
        $vote = Vote::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'date_option_id' => $dateOption->id,
            'vote' => $validated['vote'],
            'points' => $points,
        ]);

        // ✅ Calculate the best date directly here
        $bestOption = $event->dateOptions()
            ->withSum('votes as points_sum', 'points') // sum points for each date option
            ->get()
            ->sortByDesc('points_sum')
            ->first();

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
