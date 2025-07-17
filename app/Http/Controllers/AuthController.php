<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Mostafa Hassan"),
     *             @OA\Property(property="email", type="string", example="mostafa@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered")
     * )
     */
   use Illuminate\Support\Facades\Hash;

public function register(Request $request)
{
    $validated = $request->validate([
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    $validated['password'] = Hash::make($validated['password']); // تشفير الباسورد

    $user = User::create($validated);

    $token = auth()->login($user);

    return response()->json([
        'message' => 'User registered successfully',
        'token' => $token,
    ]);
}


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login user and get token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="mostafa@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Successfully logged out")
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     tags={"Auth"},
     *     summary="Refresh JWT token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Token refreshed")
     * )
     */
    public function refresh()
    {
        return response()->json([
            'token' => auth()->refresh()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/revoke",
     *     tags={"Auth"},
     *     summary="Invalidate current token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Token revoked")
     * )
     */
    public function revokeToken()
    {
        try {
            auth()->invalidate(true);
            return response()->json(['message' => 'Token revoked']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not revoke token'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/verify-email",
     *     tags={"Auth"},
     *     summary="Verify user email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "code"},
     *             @OA\Property(property="email", type="string", example="mostafa@example.com"),
     *             @OA\Property(property="code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email verified")
     * )
     */
    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || $user->email_verification_code !== $data['code']) {
            return response()->json(['message' => 'Invalid code'], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->save();

        return response()->json(['message' => 'Email verified']);
    }

    /**
     * @OA\Post(
     *     path="/api/resend-verification",
     *     tags={"Auth"},
     *     summary="Resend email verification code",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", example="mostafa@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Code sent")
     * )
     */
    public function resendEmailCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->email_verification_code = rand(100000, 999999);
        $user->save();

        Mail::raw("Your new verification code is: {$user->email_verification_code}", function ($m) use ($user) {
            $m->to($user->email)->subject('Resend Verification Code');
        });

        return response()->json(['message' => 'Verification code sent']);
    }
/**
 * Refresh JWT token
 *
 * @OA\Get(
 *     path="/api/refreshToken",
 *     tags={"Auth"},
 *     summary="Refresh and get a new JWT token",
 *     security={{ "bearerAuth": {} }},
 *     @OA\Response(
 *         response=200,
 *         description="New token returned",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="new.jwt.token.here")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
public function refreshToken()
{
    try {
        $newToken = auth()->refresh();
        return response()->json(['token' => $newToken], 200);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Invalid token'], 401);
    }
}

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     tags={"Auth"},
     *     summary="Reset password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "new_password"},
     *             @OA\Property(property="email", type="string", example="mostafa@example.com"),
     *             @OA\Property(property="new_password", type="string", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password updated")
     * )
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:6'
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
