<?php

namespace App\Http\Controllers;

/**
*   @OA\Info(
*     version="1.0.0",
*     title="Laravel API Documentation",
*     description="API Documentation for e-commerce",
*     @OA\Contact(
*         email="phantanduy14@example.com"
*     )
*   )
*   @OA\SecurityScheme(
*     securityScheme="bearerAuth",
*     in="header",
*     name="bearerAuth",
*     type="http",
*     scheme="bearer",
*     bearerFormat="JWT",
*   ),
*
*   @OA\Parameter(
*     parameter="getById",
*     in="path",
*     name="id",
*     description="Default 1",
*     @OA\Schema(
*         type="integer",
*         default=1,
*     )
*   ),

*   // for query
*   @OA\Parameter(
*     parameter="page",
*     in="query",
*     name="page",
*     @OA\Schema(
*         type="integer",
*         default=1,
*     )
*   ),
*   @OA\Parameter(
*     parameter="limit",
*     in="query",
*     name="limit",
*     @OA\Schema(
*         type="integer",
*         default=5,
*     )
*   ),
*   @OA\Parameter(
*     parameter="search",
*     in="query",
*     name="search",
*     @OA\Schema(
*         type="string",
*         default="",
*     )
*   ),
*/

abstract class Controller
{
    //
}
