<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolsSeeder extends Seeder
{
    public function run(): void
    {
        School::updateOrCreate(
            ['id' => 1],
            [
                'name'      => 'Rede Public School',
                'code'      => 'RPS001',
                'is_active' => 1,
            ]
        );
    }
}
