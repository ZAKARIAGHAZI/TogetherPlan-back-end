<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="TogetherPlan API",
 *     version="1.0.0",
 *     description="API documentation for the TogetherPlan project. This API allows users to manage events, participants, voting on dates, groups, and notifications.",
 *     @OA\Contact(
 *         email="support@togetherplan.com",
 *         name="TogetherPlan Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api.togetherplan.com",
 *     description="Production server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Use Laravel Sanctum API token for authentication"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="Endpoints related to user management"
 * )
 *
 * @OA\Tag(
 *     name="Events",
 *     description="Endpoints for creating, updating, listing, and deleting events"
 * )
 *
 * @OA\Tag(
 *     name="Participants",
 *     description="Endpoints for managing participants of events, invitations, and responses"
 * )
 *
 * @OA\Tag(
 *     name="Votes",
 *     description="Endpoints for submitting votes on date options and retrieving voting information"
 * )
 *
 * @OA\Tag(
 *     name="Groups",
 *     description="Endpoints for managing groups, inviting users, and group membership"
 * )
 *
 * @OA\Tag(
 *     name="Notifications",
 *     description="Endpoints to view and mark notifications as read"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
