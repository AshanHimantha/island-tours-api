<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Island Tours API Documentation",
 *     description="API documentation for Island Tours system",
 *     @OA\Contact(
 *         email="admin@islandtours.com",
 *         name="Island Tours Support"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Island Tours API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth",
 *     bearerFormat="JWT"
 * )
 */
class ApiDocController extends Controller
{
    // This is just a placeholder class for API documentation
    // No methods needed
}