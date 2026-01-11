<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register user baru
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // create Sanctum token
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken('api-token')->plainTextToken;
        } else {
            $token = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Register berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => $token ? 'Bearer' : null,
        ], 201);
    }

    /**
     * Login & generate JWT token
     */
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! $token = Auth::guard('api')->attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'access_token' => $token,
        'token_type' => 'Bearer', // ← INI PENTING
        'expires_in' => auth('api')->factory()->getTTL() * 60,
    ]);
}
    /**
     * Logout (invalidate JWT)
     */
    public function logout(Request $request)
    {
        // If using Sanctum tokens, delete current access token
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken')) {
            $current = $user->currentAccessToken();
            if ($current) {
                $current->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);
        }

        // fallback for other guards
        try {
            Auth::guard('api')->logout();
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }
}