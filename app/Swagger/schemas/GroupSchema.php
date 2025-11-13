<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="Group",
 *     type="object",
 *     title="Group",
 *     required={"id", "name", "created_by"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Groupe Dev"),
 *     @OA\Property(property="description", type="string", example="Description du groupe"),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="users", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="events", type="array", @OA\Items(type="object"))
 * )
 */

class GroupSchema {}
