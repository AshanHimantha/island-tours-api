<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication and management"
 * )
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     * 
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new admin or staff user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin", "staff"}, example="staff")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="staff"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="User registered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'User registered successfully',
        ], 201);
    }

    /**
     * Login user and create session with HTTP cookie.
     * 
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login and create authentication cookie",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="staff")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    
        $user = $request->user();
        
        // Create a token string manually
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Determine proper cookie settings based on environment
        $isProduction = config('app.env') === 'production';
        $sameSite = $isProduction ? 'lax' : 'none';
        
        // Important: When SameSite=None, Secure MUST be true (even in development)
        $secure = $isProduction || $sameSite === 'none';
        
        // Return both token in response AND set cookie
        return response()->json([
            'user' => $user,
            'token' => $token // Include token in response for development fallback
        ])->cookie(
            'auth_token',         // Cookie name
            $token,               // Cookie value
            60,                   // Expiration (60 minutes = 1 hour)
            '/',                  // Path
            null,                 // Domain (null = current domain)
            $secure,              // Secure must be true when SameSite=None
            true,                 // HTTP only
            false,                // Raw
            $sameSite             // SameSite policy
        );
    }
    /**
     * Logout user (revoke token).
     * 
     * @OA\Get(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout and invalidate cookie",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Delete the current token from the database
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }
        
        return response()->json([
            'message' => 'Logged out successfully',
        ])->cookie(
            'auth_token',     // Cookie name
            '',               // Empty value
            -1,               // Expire immediately
            '/',              // Path
            null,             // Domain
            config('app.env') === 'production', // Secure
            true,             // HTTP only
            false,            // Raw
            'lax'             // SameSite policy - changed from 'strict' to 'lax'
        );
    }

    /**
     * Get authenticated user.
     * 
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="Get authenticated user details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="role", type="string", example="admin"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function user(Request $request)
    {
        // Return the authenticated user
        return response()->json($request->user());
    }
    
    /**
     * Verify if the user is authenticated.
     * 
     * @OA\Get(
     *     path="/api/verify",
     *     tags={"Authentication"},
     *     summary="Verify if user is authenticated",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User is authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="authenticated", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User is not authenticated"
     *     )
     * )
     */
    public function verify(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()
        ]);
    }
}
