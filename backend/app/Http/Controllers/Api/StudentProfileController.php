<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    /**
     * Upload / update student profile photo (Student self-upload)
     */
    public function uploadPhoto(Request $request)
    {
        $user = $request->user();

        // Ensure logged-in user is linked to a student
        if (!$user->student || !$user->student->is_active) {
            abort(403, 'Student profile not found or inactive.');
        }

        $student = $user->student;

        // Validate image
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Delete old photo if exists (safe cleanup)
        if ($student->photo_path) {
            $oldPath = str_replace('storage/', '', $student->photo_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Generate deterministic filename
        $filename = 'student_' . $student->id . '.' . $request->file('photo')->extension();

        // Store photo
        $path = $request->file('photo')->storeAs(
            'students/photos',
            $filename,
            'public'
        );

        // Save DomPDF-safe relative public path
        $student->update([
            'photo_path' => 'storage/' . $path,
        ]);

        return response()->json([
            'message'    => 'Profile photo uploaded successfully.',
            'photo_path' => $student->photo_path,
        ]);
    }
}
