<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $leave_types = ['Sick', 'Annual', 'Casual', 'Maternity'];
        foreach($leave_types as $leave_type){
            LeaveType::create([
                'name' => $leave_type
            ]);
        }
    }
}
