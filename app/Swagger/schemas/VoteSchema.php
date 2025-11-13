<?php


namespace App\Swagger\Schemas;


/**
 * @OA\Schema(
 *     schema="Vote",
 *     type="object",
 *     title="Vote",
 *     description="Represents a vote cast by a user for a date option in an event",
 *     @OA\Property(property="id", type="integer", example=1, description="Vote ID"),
 *     @OA\Property(property="user_id", type="integer", example=11, description="ID of the user who voted"),
 *     @OA\Property(property="event_id", type="integer", example=6, description="ID of the event associated with the vote"),
 *     @OA\Property(property="date_option_id", type="integer", example=17, description="ID of the date option voted for"),
 *     @OA\Property(property="vote", type="string", enum={"yes","maybe","no"}, example="yes", description="Vote choice"),
 *     @OA\Property(property="points", type="integer", example=2, description="Numeric points associated with the vote"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-03T15:50:50.000000Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-03T15:50:50.000000Z", description="Last update timestamp")
 * )
 */

class VoteSchema {}
