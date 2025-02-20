<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('designations')->insert([
            [
                'designation' => 'Stockman I',
                'description' => 'Responsible for managing and organizing stock inventory in a warehouse or storage facility.',
            ],
            [
                'designation' => 'LMO II',
                'description' => 'Logistics Management Officer II, oversees and manages logistical operations and supply chain activities.',
            ],
            [
                'designation' => 'Administrative Officer',
                'description' => 'Handles administrative tasks and ensures smooth office operations.',
            ],
            [
                'designation' => 'Technical Support Specialist',
                'description' => 'Provides IT support and troubleshooting for software and hardware issues.',
            ],
        ]);
    }
}
