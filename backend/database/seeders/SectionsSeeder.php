<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Section;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all classes
        $classes = SchoolClass::all();

        foreach ($classes as $class) {

            // Define sections per class
            $sections = [
                'A',
                'B',
            ];

            foreach ($sections as $sectionName) {
                Section::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'name'     => $sectionName,
                    ],
                    [
                        // no extra fields required
                    ]
                );
            }
        }
    }
}
