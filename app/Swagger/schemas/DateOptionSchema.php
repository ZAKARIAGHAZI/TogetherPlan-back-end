<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="DateOption",
 *     type="object",
 *     title="DateOption",
 *     description="Schema representing a proposed date option for an event",
 *     required={"event_id","proposed_date"},

 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="event_id", type="integer", example=5),
 *     @OA\Property(property="proposed_date", type="string", format="date", example="2025-11-15"),
 *     @OA\Property(property="proposed_time", type="string", format="time", nullable=true, example="14:30:00"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-03T16:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-03T16:05:00Z"),

 *     @OA\Property(
 *         property="event",
 *         ref="#/components/schemas/Event",
 *         description="The event this date option belongs to"
 *     ),

 *     @OA\Property(
 *         property="votes",
 *         type="array",
 *         description="Votes associated with this date option",
 *         @OA\Items(ref="#/components/schemas/Vote")
 *     )
 * )
 */
class DateOptionSchema {}
