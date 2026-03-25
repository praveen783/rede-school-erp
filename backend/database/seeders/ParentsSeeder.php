<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\ParentProfile;

class ParentsSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::all();

        foreach ($schools as $school) {

            // Create 20 parents per school
            for ($i = 1; $i <= 20; $i++) {

                $phone = '9' . str_pad($school->id . $i, 9, '0', STR_PAD_LEFT);

                ParentProfile::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'phone'     => $phone, // unique per school
                    ],
                    [
                        'name'       => "Test Parent {$i}",
                        'email'      => "parent{$i}@school{$school->id}.com",
                        'occupation' => 'Engineer',
                        'is_active'  => 1,
                    ]
                );
            }
        }
    }
}
