<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherEducation;
use App\Models\TeacherExperience;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    
    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'employee_code'    => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other',
            'date_of_joining'  => 'nullable|date',
            'date_of_birth'    => 'nullable|date',
            'address'          => 'nullable|string|max:1000',
            'qualification'    => 'nullable|string|max:500',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'primary_subject'  => 'nullable|string|max:255',
            'secondary_subject'=> 'nullable|string|max:255',
            'is_active'        => 'nullable|boolean',
            'subjects'         => 'required|array|min:1',
            'subjects.*'       => 'exists:subjects,id',
            // Education entries
            'educations'                       => 'nullable|array',
            'educations.*.degree'              => 'required_with:educations|string|max:255',
            'educations.*.institution'         => 'required_with:educations|string|max:255',
            'educations.*.field_of_study'      => 'nullable|string|max:255',
            'educations.*.board_or_university' => 'nullable|string|max:255',
            'educations.*.passing_year'        => 'nullable|integer|min:1950|max:2100',
            'educations.*.result'              => 'nullable|string|max:100',
            'educations.*.percentage'          => 'nullable|numeric|min:0|max:100',
            'educations.*.grade'               => 'nullable|string|max:10',
            // Experience entries
            'experiences'                      => 'nullable|array',
            'experiences.*.organization'       => 'required_with:experiences|string|max:255',
            'experiences.*.designation'        => 'required_with:experiences|string|max:255',
            'experiences.*.department'         => 'nullable|string|max:255',
            'experiences.*.from_date'          => 'nullable|date',
            'experiences.*.to_date'            => 'nullable|date',
            'experiences.*.is_current'         => 'nullable|boolean',
            'experiences.*.responsibilities'   => 'nullable|string|max:1000',
            'experiences.*.leaving_reason'     => 'nullable|string|max:255',
        ]);

        // Resolve school_id
        if ($user->school_id) {
            $data['school_id'] = $user->school_id;
        } else {
            $request->validate([
                'school_id' => 'required|exists:schools,id'
            ]);
            $data['school_id'] = $request->school_id;
        }

        // Unique employee code per school
        $exists = Teacher::where('school_id', $data['school_id'])
            ->where('employee_code', $data['employee_code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Employee code already exists for this school'
            ], 422);
        }

        // Create teacher
        $teacher = Teacher::create([
            'school_id'        => $data['school_id'],
            'employee_code'    => $data['employee_code'],
            'name'             => $data['name'],
            'email'            => $data['email'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'gender'           => $data['gender'] ?? null,
            'date_of_joining'  => $data['date_of_joining'] ?? null,
            'date_of_birth'    => $data['date_of_birth'] ?? null,
            'address'          => $data['address'] ?? null,
            'qualification'    => $data['qualification'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
            'primary_subject'  => $data['primary_subject'] ?? null,
            'secondary_subject'=> $data['secondary_subject'] ?? null,
            'is_active'        => $data['is_active'] ?? 1,
        ]);

        //  ATTACH SUBJECTS WITH school_id IN PIVOT
        $pivotData = [];
        foreach ($data['subjects'] as $subjectId) {
            $pivotData[$subjectId] = [
                'school_id' => $data['school_id']
            ];
        }

        $teacher->subjects()->attach($pivotData);

        // Save education entries
        if (!empty($data['educations'])) {
            foreach ($data['educations'] as $edu) {
                $teacher->educations()->create(array_merge($edu, [
                    'school_id' => $data['school_id'],
                ]));
            }
        }

        // Save experience entries
        if (!empty($data['experiences'])) {
            foreach ($data['experiences'] as $exp) {
                $exp['is_current'] = !empty($exp['is_current']) ? 1 : 0;
                if ($exp['is_current']) {
                    $exp['to_date'] = null;
                }
                $teacher->experiences()->create(array_merge($exp, [
                    'school_id' => $data['school_id'],
                ]));
            }
        }

        return response()->json(
            $teacher->load(['subjects:id,name', 'educations', 'experiences']),
            201
        );
    }



    /**
     * List teachers
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Teacher::with(['subjects:id,name'])
            ->where('school_id', $user->school_id);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return response()->json(
            $query->orderBy('name')->paginate(100)
        );
    }


    /**
     * View single teacher
     */
    public function show($id)
    {
        $user = auth()->user();

        $teacher = Teacher::with(['subjects:id,name', 'educations', 'experiences'])
            ->where('school_id', $user->school_id)
            ->findOrFail($id);

        return response()->json($teacher);
    }


    /**
     * Update teacher
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $teacher = Teacher::where('school_id', $user->school_id)
            ->findOrFail($id);

        $data = $request->validate([
            'employee_code'    => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other',
            'date_of_joining'  => 'nullable|date',
            'date_of_birth'    => 'nullable|date',
            'address'          => 'nullable|string|max:1000',
            'qualification'    => 'nullable|string|max:500',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'primary_subject'  => 'nullable|string|max:255',
            'secondary_subject'=> 'nullable|string|max:255',
            'is_active'        => 'required|boolean',
            'subjects'         => 'required|array|min:1',
            'subjects.*'       => 'exists:subjects,id',
            // Education entries
            'educations'                       => 'nullable|array',
            'educations.*.degree'              => 'required_with:educations|string|max:255',
            'educations.*.institution'         => 'required_with:educations|string|max:255',
            'educations.*.field_of_study'      => 'nullable|string|max:255',
            'educations.*.board_or_university' => 'nullable|string|max:255',
            'educations.*.passing_year'        => 'nullable|integer|min:1950|max:2100',
            'educations.*.result'              => 'nullable|string|max:100',
            'educations.*.percentage'          => 'nullable|numeric|min:0|max:100',
            'educations.*.grade'               => 'nullable|string|max:10',
            // Experience entries
            'experiences'                      => 'nullable|array',
            'experiences.*.organization'       => 'required_with:experiences|string|max:255',
            'experiences.*.designation'        => 'required_with:experiences|string|max:255',
            'experiences.*.department'         => 'nullable|string|max:255',
            'experiences.*.from_date'          => 'nullable|date',
            'experiences.*.to_date'            => 'nullable|date',
            'experiences.*.is_current'         => 'nullable|boolean',
            'experiences.*.responsibilities'   => 'nullable|string|max:1000',
            'experiences.*.leaving_reason'     => 'nullable|string|max:255',
        ]);

        // Prevent duplicate employee code
        $exists = Teacher::where('school_id', $user->school_id)
            ->where('employee_code', $data['employee_code'])
            ->where('id', '!=', $teacher->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Employee code already exists for this school'
            ], 422);
        }

        // Update teacher
        $teacher->update($data);

        // 🔑 FIX: sync subjects WITH school_id
        $syncData = [];
        foreach ($request->subjects as $subjectId) {
            $syncData[$subjectId] = [
                'school_id' => $teacher->school_id
            ];
        }

        $teacher->subjects()->sync($syncData);

        // Replace education entries (delete old, insert new)
        if (array_key_exists('educations', $data)) {
            $teacher->educations()->delete();
            foreach ($data['educations'] ?? [] as $edu) {
                $teacher->educations()->create(array_merge($edu, [
                    'school_id' => $teacher->school_id,
                ]));
            }
        }

        // Replace experience entries (delete old, insert new)
        if (array_key_exists('experiences', $data)) {
            $teacher->experiences()->delete();
            foreach ($data['experiences'] ?? [] as $exp) {
                $exp['is_current'] = !empty($exp['is_current']) ? 1 : 0;
                if ($exp['is_current']) {
                    $exp['to_date'] = null;
                }
                $teacher->experiences()->create(array_merge($exp, [
                    'school_id' => $teacher->school_id,
                ]));
            }
        }

        return response()->json(
            $teacher->load(['subjects:id,name', 'educations', 'experiences'])
        );
    }


    public function destroy($id)
    {
        $user = auth()->user();

        // Find teacher within same school
        $teacher = Teacher::where('id', $id)
            ->where('school_id', $user->school_id)
            ->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found'
            ], 404);
        }

        // Detach subjects (important)
        $teacher->subjects()->detach();

        // Soft delete teacher
        $teacher->delete();

        return response()->json([
            'message' => 'Teacher deleted successfully'
        ], 200);
    }

    /**
     * Activate / Deactivate teacher
     */
    public function toggleStatus($id)
    {
        $user = auth()->user();

        $teacher = Teacher::where('school_id', $user->school_id)
            ->findOrFail($id);

        $teacher->update([
            'is_active' => ! $teacher->is_active
        ]);

        return response()->json([
            'message' => $teacher->is_active
                ? 'Teacher activated successfully'
                : 'Teacher deactivated successfully'
        ]);
    }
}
