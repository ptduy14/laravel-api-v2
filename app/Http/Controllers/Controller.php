<?php

namespace App\Http\Controllers;

/**
*   @OA\Info(
*     version="1.0.0",
*     title="Laravel API Documentation",
*     description="API Documentation for your project",
*     @OA\Contact(
*         email="your-email@example.com"
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
*/

abstract class Controller
{
    //
}
