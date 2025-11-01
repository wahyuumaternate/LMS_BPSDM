<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="LMS BPSDM MALUT API Documentation",
 *     version="1.0.0",
 *     description="API documentation for LMS BPSDM MALUT module",
 *     @OA\Contact(
 *         email="retmu@example.com",
 *         name="Wahyu Umaternate",
 *         url="https://wahyuumaternate.my.id/"
 *     ),
 *     
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Enter your bearer token in the format: your-token-here (without 'Bearer' prefix)"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
