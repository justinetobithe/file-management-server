<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'BM/ABM Office',
            'Accounting Section',
            'Finance Section',
            'Provident Fund Section',
            'Marketing Section',
            'Healthcare Services',
            'Human Resource Section',
            'Bingo Section',
            'General Services Section',
            'Information Technology Section',
            'Logistics Management Section',
            'Communication Section',
            'Treasury Division',
            'Internal Security Division',
            'Surveillance Division',
            'Gaming Division',
            'Slot Machine Division',
            'Internal Audit Section',
            'Customer Relations Section',
            'Procurement Section',
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate([
                'name' => $department
            ]);
        }
    }
}
