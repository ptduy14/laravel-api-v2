<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Requests\CreateUserRequest;
use DB;

class AuthController extends Controller
{   

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register a new user",  
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","address","gender","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="gender", type="boolean", example=true),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account registered successfully"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="string", example="Validation Error"),
     *             @OA\Property(property="message", type="string", example="Validation errors occurred"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(CreateUserRequest $request) {
        $request->validated();
    
        try {
            DB::beginTransaction();
    
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'gender' => $request->input('gender'),
                'password' => bcrypt($request->input('password')),
            ]);
    
            $user->assignRole('user');

            $token = $user->createToken('access_token')->plainTextToken;
    
            DB::commit();
    
            return response()->json([
                'message' => 'Account registered successfully',
                'access_token' => $token,
                'data' => new UserResource($user)
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Account registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(FormLoginRequest $request) {
        $request->validated();
    
        $credentials = request(['email', 'password']);

        
        if (! $token = auth()->guard('jwt')->attempt($credentials)) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ]);
        }

    }
    
}
