<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\HTTPException;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserActivationEmail;
use DB;

class AuthController extends Controller
{   
    /**
        * @OA\Get(
        *     path="/api/auth/me",
        *     summary="Get the authenticated user",
        *     tags={"Auth"},
        *     security={{"bearerAuth":{}}},
        *     @OA\Response(
        *         response=200,
        *         description="Successfully retrieved user information",
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
    public function getMe()
    {
        $user = Auth::user();

        // Trả về thông tin của người dùng dưới dạng JSON
        return response()->json([
            'message' => 'Get account successfully',
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logout")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    
    public function logout(Request $request) {
            $user = Auth::user();

            if ($user->verify) {
                throw HTTPException::FORBIDDEN('User is inactive');
            }
            
            // Xóa tất cả các token của người dùng hiện tại
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Successfully logout'
            ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Auth"},
     *     summary="Register",  
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
     *         description="Account registered successfully, check the email for active",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account registered successfully, check the email for active"),
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
    public function register(RegisterRequest $request) {
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
    
            DB::commit();

            $secretKey = env('SECRET_HASH_TOKEN_ACTIVITION');
            $string = $user->email." ".$secretKey;

            $tokenActivition = Crypt::encrypt($string);

            // The email sending is done using the to method on the Mail facade
            Mail::to($user->email)->send(new UserActivationEmail($user->name, $tokenActivition));

            return response()->json([
                'message' => 'Account registered successfully, check the email for active',
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Account registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Auth"},
     *     summary="Login",  
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account login Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account login Successfully"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request) {
        $request->validated();
    
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            throw HTTPException::UNAUTHORIZED();
        }

        $user = Auth::user();
        
        // Xóa các token cũ (tuỳ chọn, để người dùng không có nhiều token)
        $user->tokens()->delete();

        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'message' => 'Account login successfully',
            'access_token' => $token,
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/auth/update",
     *     summary="Update user information",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","phone","address","gender"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="gender", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Change password successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Change password successfully"),
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
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $request->validated();

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            // Cập nhật thông tin người dùng
            $user->update([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'gender' => $request->input('gender'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User information updated successfully',
                'data' => new UserResource($user)
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/auth/change-password",
     *     summary="Change password",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "new_password"},
     *             @OA\Property(property="password", type="password", example="aaaa"),
     *             @OA\Property(property="new_password", type="password", example="aaaaa"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User information updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Change password successfully"),
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request) {
        $request->validated();
        
        $user = Auth::user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 401);
        }

        try {
            DB::beginTransaction();
            
            // Update the user's password
            $user->update(['password' => Hash::make($request->input('new_password'))]);

            DB::commit();

            return response()->json([
                'message' => 'Change password Successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activeUser($token) {
        $email = Crypt::decrypt($token);
      
        $user = User::where('email', explode(" ",$email)[0])->first();

        if (!$user) {
            throw HTTPException::BAD_REQUEST('Invalid activition token');
        }

        if ($user->verify) {
            throw HTTPException::BAD_REQUEST('User already actived');            
        }

        $userUpdated = $user->update(['verify' => true]);

        return response()->json(['message' => 'User activated successfully.'], 200);
    }
}
