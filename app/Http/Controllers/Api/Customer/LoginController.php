<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle customer login request and return token if credentials are valid.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->guard('api_customer')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api_customer')->user(),
            'token'   => $token,
        ], 200);
    }

    /**
     * Get authenticated user data.
     */
    public function getUser()
    {
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api_customer')->user(),
        ], 200);
    }

    /**
     * Refresh JWT token and return a new one.
     */
    public function refreshToken(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Token not provided'], 401);
            }

            $refreshToken = JWTAuth::refresh($token);
            $user = JWTAuth::setToken($refreshToken)->toUser();

            $request->headers->set('Authorization', 'Bearer ' . $refreshToken);

            return response()->json([
                'success' => true,
                'user'    => $user,
                'token'   => $refreshToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout the user by invalidating the token.
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
