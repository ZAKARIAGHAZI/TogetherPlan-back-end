<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="Schema representing a user",
 *     required={"id","name","email"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Zakaria Ghazi"),
 *     @OA\Property(property="email", type="string", format="email", example="zakariaghazi77@gmail.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-11-03T15:50:50Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-03T15:50:50Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-03T15:50:50Z"),
 *
 *     @OA\Property(
 *         property="groups",
 *         type="array",
 *         description="Groups the user belongs to",
 *         @OA\Items(ref="#/components/schemas/Group")
 *     ),
 *
 *     @OA\Property(
 *         property="participants",
 *         type="array",
 *         description="Participant records for this user",
 *         @OA\Items(ref="#/components/schemas/Participant")
 *     ),
 *
 *     @OA\Property(
 *         property="events_created",
 *         type="array",
 *         description="Events created by this user",
 *         @OA\Items(ref="#/components/schemas/Event")
 *     )
 * )
 */
class UserSchema {}
