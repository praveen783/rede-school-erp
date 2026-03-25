<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function uploadPhoto(Request $request)
    {
        $user = $request->user();

        // Ensure student profile exists
        if (!$user->student || !$user->student->is_active) {
            abort(403, 'Student not eligible');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $student = $user->student;

        // Delete old photo if exists
        if ($student->photo_path) {
            $oldPath = str_replace('storage/', '', $student->photo_path);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('photo')
            ->store('students/photos', 'public');

        $student->update([
            'photo_path' => 'storage/' . $path,
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_path' => $student->photo_path,
        ]);
    }
}
