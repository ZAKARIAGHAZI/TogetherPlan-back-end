<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="Participant",
 *     type="object",
 *     title="Participant",
 *     description="Schema representing a participant in an event",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID du participant",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="event_id",
 *         type="integer",
 *         description="ID de l'événement auquel le participant est associé",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID de l'utilisateur participant",
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Statut de l'invitation",
 *         enum={"invited", "accepted", "declined"},
 *         example="invited"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de création du participant",
 *         example="2025-11-03T16:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de dernière mise à jour du participant",
 *         example="2025-11-03T16:05:00Z"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="Informations sur l'utilisateur",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="event",
 *         type="object",
 *         description="Informations sur l'événement",
 *         ref="#/components/schemas/Event"
 *     )
 * )
 */


class ParticipantSchema {}
