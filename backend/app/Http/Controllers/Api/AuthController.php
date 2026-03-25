<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Fetch user (ignore soft-deleted users automatically)
        $user = User::where('email', $request->email)->first();

        // 3. Check user exists & password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 4. Check if account is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is inactive. Contact admin.'
            ], 403);
        }

        // 5. Update last login timestamp
        $user->update([
            'last_login_at' => now(),
        ]);

        // 6. Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 7. Return response
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'school_id' => $user->school_id,
            ],
        ]);
    }
    public function logout(Request $request)
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    public function me(Request $request)
    {
        $user = $request->user();

         // Load student only if role is student
        if ($user->role === 'student') {
            $user->load([
                'student',
                'student.class',
                'student.section'
            ]);
        }
        // Load school only if relation exists
        $user->load('school');

        return response()->json([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'is_active' => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'created_at'    => $user->created_at,
            'school' => $user->school,
            'student'  => $user->student,
        ]);
    }


}
