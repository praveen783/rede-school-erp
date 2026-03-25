<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;

class SectionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string',
        ]);

        $section = Section::create($data);

        return response()->json($section, 201);
    }
    public function index(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        return response()->json(
        Section::where('class_id', $request->class_id)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

}
