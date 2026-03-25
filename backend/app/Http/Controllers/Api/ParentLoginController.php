<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentLoginController extends Controller
{
    /**
     * Create or reset parent login credentials (Admin only)
     */
   public function create(ParentProfile $parent)
    {
        $admin = auth()->user();

        // 🔒 School isolation
        if ($parent->school_id !== $admin->school_id) {
            abort(403, 'Unauthorized');
        }

        // 🔒 Ensure parent is active
        if (! $parent->is_active) {
            return response()->json([
                'message' => 'Parent is inactive'
            ], 422);
        }

        // 🔑 System login email (ERP-safe & unique)
        $loginEmail = "parent-{$parent->school_id}-{$parent->id}@school.local";

        // 🔑 Temp password
        $tempPassword = Str::random(8);

        /*
        |--------------------------------------------------------------------------
        | STEP 1: Resolve user safely
        |--------------------------------------------------------------------------
        */

        $user = null;

        // Case A: linked user
        if ($parent->user_id) {
            $user = User::find($parent->user_id);
        }

        // Case B: fallback by email
        if (! $user) {
            $user = User::where('email', $loginEmail)->first();
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Create or Reset
        |--------------------------------------------------------------------------
        */

        if ($user) {
            // ✅ RESET PASSWORD
            $user->update([
                'password'  => Hash::make($tempPassword),
                'is_active' => true,
            ]);

            // 🔗 Fix missing link
            if (! $parent->user_id) {
                $parent->update([
                    'user_id' => $user->id
                ]);
            }
        } else {
            // ✅ FIRST TIME CREATE
            $user = User::create([
                'name'      => $parent->name,
                'email'     => $loginEmail,
                'password'  => Hash::make($tempPassword),
                'role'      => 'parent',
                'school_id' => $parent->school_id,
                'is_active' => true,
            ]);

            $parent->update([
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'message'  => 'Parent login credentials generated successfully',
            'email'    => $loginEmail,
            'password' => $tempPassword
        ]);
    }

}
