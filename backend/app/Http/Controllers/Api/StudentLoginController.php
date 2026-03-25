<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class StudentLoginController extends Controller
{
    
    public function create(Student $student)
    {
        $admin = auth()->user();

        // 🔒 School isolation
        if ($student->school_id !== $admin->school_id) {
            abort(403, 'Unauthorized');
        }

        $tempPassword = Str::random(8);
        $systemEmail  = strtolower($student->admission_no) . '@school.local';

        $user = DB::transaction(function () use ($student, $systemEmail, $tempPassword) {

            // 1️⃣ Try resolving existing user
            $user = null;

            if ($student->user_id) {
                $user = User::find($student->user_id);
            }

            if (!$user) {
                $user = User::where('email', $systemEmail)->first();
            }

            // 2️⃣ If user exists → reset password
            if ($user) {
                $user->update([
                    'password'  => Hash::make($tempPassword),
                    'is_active' => true,
                ]);
            } 
            // 3️⃣ If not → create new user
            else {
                $user = User::create([
                    'name'      => $student->name,
                    'email'     => $systemEmail,
                    'password'  => Hash::make($tempPassword),
                    'role'      => 'student',
                    'school_id' => $student->school_id,
                    'is_active' => true,
                ]);
            }

            // 4️⃣ Ensure student is linked
            if (!$student->user_id) {
                $student->update([
                    'user_id' => $user->id
                ]);
            }

            return $user;
        });

        return response()->json([
            'message'      => 'Student login credentials generated successfully',
            'username'     => $student->admission_no,
            'system_email' => $systemEmail,
            'password'     => $tempPassword
        ]);
    }
}
