<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     title="Event",
 *     description="Schema representing an event",
 *     required={"id","title","location","category","privacy","created_by"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Team Meeting"),
 *     @OA\Property(property="description", type="string", example="Discuss project progress"),
 *     @OA\Property(property="location", type="string", example="Casablanca"),
 *     @OA\Property(property="category", type="string", example="Meeting"),
 *     @OA\Property(property="privacy", type="string", enum={"public","private"}, example="private"),
 *     @OA\Property(property="created_by", type="integer", example=3),
 *     @OA\Property(property="group_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="best_date_id", type="integer", nullable=true, example=10),
 *
 *     @OA\Property(
 *         property="creator",
 *         ref="#/components/schemas/User",
 *         description="The user who created the event"
 *     ),
 *
 *     @OA\Property(
 *         property="group",
 *         ref="#/components/schemas/Group",
 *         description="The group associated with the event"
 *     ),
 *
 *     @OA\Property(
 *         property="participants",
 *         type="array",
 *         description="List of participants for this event",
 *         @OA\Items(ref="#/components/schemas/Participant")
 *     ),
 *
 *     @OA\Property(
 *         property="dateOptions",
 *         type="array",
 *         description="List of proposed date options for this event",
 *         @OA\Items(ref="#/components/schemas/DateOption")
 *     ),
 *
 *     @OA\Property(
 *         property="bestDate",
 *         ref="#/components/schemas/DateOption",
 *         description="The selected best date for the event"
 *     ),
 *
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-03T15:50:50Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-03T15:50:50Z")
 * )
 */
class EventSchema {}
