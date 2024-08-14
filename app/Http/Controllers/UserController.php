<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\HTTPException;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use DB;

class UserController extends Controller
{   
    /**
        * @OA\Get(
        *     path="/api/users",
        *     summary="Get users",
        *     tags={"Users"},
        *     security={{"bearerAuth":{}}},
        *     @OA\Response(
        *         response=200,
        *         description="Get users successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string"),
        *             @OA\Property(property="data", type="object")
        *         )
        *     ),
        *     @OA\Response(
        *         response=401,
        *         description="Unauthorized"
        *     )
        * )
    */
    public function getAllUsers(Request $request) {

        $queryParams = handleQueryParameter($request);

        $users = User::when($queryParams['search'], function($query, $search) {
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate($queryParams['limit']);

        if ($users->isEmpty()) {
            throw HTTPException::NOT_FOUND();
        }

        return response()->json([
            'message' => 'Get users successfully',
            'data' => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total_items' => $users->total(),
                'per_page' => $users->perPage(),
            ]
        ], 200);
    }

    /**
        * @OA\Get(
        *     path="/api/users/{id}",
        *     summary="Get users",
        *     tags={"Users"},
        *     security={{"bearerAuth":{}}},
        *     @OA\Parameter(
        *         ref="#/components/parameters/getById" 
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Get users successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string"),
        *             @OA\Property(property="data", type="object")
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Resource not found",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Resource not found")
        *         )
        *     )
        * )
    */
    public function getUser($id) {
        $user = User::find($id);

        if (!$user) {
            throw HTTPException::NOT_FOUND('User not found');
        }

        return response()->json([
            'message' => 'Get user successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "address", "gender", "role", "verify", "password"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="johndoe@gmail.com"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 example="1234567890"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 example="123 Main St, Springfield"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 example="user"
     *             ),
     *             @OA\Property(
     *                 property="verify",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", type="object", additionalProperties=true)
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
     public function createUser(CreateUserRequest $request) {
        $request->validated();

        try {
            DB::beginTransaction();
    
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'verify' => $request->input('verify'),
                'gender' => $request->input('gender'),
                'password' => bcrypt($request->input('password')),
            ]);
    
            $user->assignRole($request->input('role'));
    
            DB::commit();
    
            return response()->json([
                'message' => 'Created user successfully',
                'data' => new UserResource($user)
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Create user failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update user information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address", "gender", "role", "verify"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Jane Doe"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 example="1234567890"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 example="456 Elm St, Springfield"
     *             ),
     *             @OA\Property(
     *                 property="gender",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 example="user"
     *             ),
     *             @OA\Property(
     *                 property="verify",
     *                 type="boolean",
     *                 example=false
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", type="object", additionalProperties=true)
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function updateUser(UpdateUserRequest $request, $id) {
        $user = User::find($id);

        if (!$user) {
            throw HTTPException::NOT_FOUND();
        }

        $request->validated();

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'gender' => $request->input('gender'),
                'role' => $request->input('role'),
                'verify' => $request->input('verify'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Updated user successfully',
                'data' => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Create user failed',
                'error' => $e->getMessage(),
            ], 500);
        }

        $user->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'gender' => $request->input('gender'),
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         ref="#/components/parameters/getById" 
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Constraint violation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User has related orders or cart and cannot be deleted."
     *             )
     *         )
     *     )
     * )
     */
    public function deleteUser($id) { // will refactor to soft delete later
        $user = User::find($id);
        if (!$user) {
            throw HTTPException::NOT_FOUND('User not found');
        }
    
        // Kiểm tra xem người dùng có đơn hàng không
        if ($user->orders()->exists()) {
            throw HTTPException::CONSTRAINT_VIOLATION('User has related orders and cannot be deleted.');
        }
    
        // Kiểm tra xem người dùng có giỏ hàng không
        if ($user->cart()->exists()) {
            $user->cart()->delete();
        }
    
        // Xóa người dùng
        $user->delete();
    
        return response()->json([
            'status' => 200,
            'message' => 'User deleted successfully',
        ], 200);
    }
}   
