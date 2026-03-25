<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherLoginController extends Controller
{
    /**
     * Create or reset teacher login credentials
     */
    public function create(Teacher $teacher)
    {
        // Ensure teacher belongs to admin's school
        $admin = auth()->user();

        if ($teacher->school_id !== $admin->school_id) {
            abort(403, 'Unauthorized');
        }

        // Generate temporary password
        $tempPassword = Str::random(8);

        // Case 1: User already exists → reset password
        if ($teacher->user_id) {

            $user = User::findOrFail($teacher->user_id);

            $user->update([
                'password'  => Hash::make($tempPassword),
                'is_active' => true,
            ]);

        } 
        // Case 2: Create new user
        else {

            if (! $teacher->email) {
                return response()->json([
                    'message' => 'Teacher email is required to create login'
                ], 422);
            }

            $user = User::create([
                'name'      => $teacher->name,
                'email'     => $teacher->email,
                'password'  => Hash::make($tempPassword),
                'role'      => 'teacher',
                'school_id' => $teacher->school_id,
                'is_active' => true,
            ]);

            $teacher->update([
                'user_id' => $user->id
            ]);
        }

        // ⚠️ DEV ONLY: return password (remove in production)
        return response()->json([
            'message' => 'Teacher login credentials generated successfully',
            'email'   => $user->email,
            'password'=> $tempPassword
        ]);
    }
}
