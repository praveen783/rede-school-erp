<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolLogoController extends Controller
{
    public function upload(Request $request, School $school)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Optional: delete old logo
        if ($school->logo_path && Storage::disk('public')->exists(
            str_replace('storage/', '', $school->logo_path)
        )) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $school->logo_path)
            );
        }

        $path = $request->file('logo')
            ->store('schools/logos', 'public');

        $school->update([
            'logo_path' => 'storage/' . $path,
        ]);

        return response()->json([
            'message' => 'School logo uploaded successfully',
            'logo_path' => $school->logo_path,
        ]);
    }
}
