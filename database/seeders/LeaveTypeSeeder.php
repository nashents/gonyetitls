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
        $leaveTypes = [
        [
            'name' => 'Annual',
            'code' => 'AL',
            'entitlement' => 30,
            'is_paid' => true,
            'is_accruable' => true,
            'monthly_accrual_rate' => 2.5,
            'carry_forward_allowed' => true,
            'max_carry_forward_days' => 30,
            'active' => true,
            'description' => 'Employee annual vacation leave.',
        ],
        [
            'name' => 'Sick',
            'code' => 'SL',
            'entitlement' => 90,
            'is_paid' => true,
            'requires_medical_report' => true,
            'active' => true,
            'description' => 'Medical illness leave.',
        ],
        [
            'name' => 'Casual',
            'code' => 'CL',
            'entitlement' => 12,
            'is_paid' => true,
            'active' => true,
            'description' => 'Short personal leave.',
        ],
        [
            'name' => 'Maternity',
            'code' => 'ML',
            'entitlement' => 98,
            'is_paid' => true,
            'requires_attachment' => true,
            'active' => true,
            'description' => 'Maternity leave entitlement.',
        ],
        [
            'name' => 'Paternity',
            'code' => 'PL',
            'entitlement' => 5,
            'is_paid' => true,
            'active' => true,
            'description' => 'Paternity leave entitlement.',
        ],
        [
            'name' => 'Compassionate',
            'code' => 'COL',
            'entitlement' => 5,
            'is_paid' => true,
            'active' => true,
            'description' => 'Bereavement or funeral leave.',
        ],
        [
            'name' => 'Study',
            'code' => 'STL',
            'entitlement' => 10,
            'is_paid' => true,
            'active' => true,
            'description' => 'Approved examinations and studies.',
        ],
        [
            'name' => 'Special',
            'code' => 'SPL',
            'entitlement' => 30,
            'is_paid' => true,
            'active' => true,
            'description' => 'Special approved leave.',
        ],
        [
            'name' => 'Unpaid',
            'code' => 'UPL',
            'entitlement' => 365,
            'is_paid' => false,
            'active' => true,
            'description' => 'Leave without pay.',
        ],
        [
            'name' => 'Time Off In Lieu',
            'code' => 'TOIL',
            'entitlement' => 0,
            'is_paid' => true,
            'is_accruable' => true,
            'monthly_accrual_rate' => 0,
            'active' => false,
            'description' => 'Compensatory leave earned from overtime.',
        ],
        [
            'name' => 'Marriage',
            'code' => 'MAR',
            'entitlement' => 5,
            'is_paid' => true,
            'active' => false,
            'description' => 'Leave granted when an employee gets married.',
        ],
        [
            'name' => 'Adoption',
            'code' => 'ADP',
            'entitlement' => 30,
            'is_paid' => true,
            'active' => false,
            'description' => 'Leave granted for child adoption.',
        ],
        [
            'name' => 'Family Responsibility',
            'code' => 'FRL',
            'entitlement' => 5,
            'is_paid' => true,
            'active' => false,
            'description' => 'Leave for urgent family responsibilities.',
        ],
        [
            'name' => 'Sabbatical',
            'code' => 'SAB',
            'entitlement' => 90,
            'is_paid' => true,
            'active' => false,
            'description' => 'Extended approved leave from work.',
        ],
        [
            'name' => 'Jury Duty',
            'code' => 'JUR',
            'entitlement' => 30,
            'is_paid' => true,
            'active' => false,
            'description' => 'Leave for court or civic duty.',
        ],
        [
            'name' => 'Training',
            'code' => 'TRN',
            'entitlement' => 15,
            'is_paid' => true,
            'active' => false,
            'description' => 'Leave for approved training.',
        ],
        [
            'name' => 'Suspension',
            'code' => 'SUS',
            'entitlement' => 365,
            'is_paid' => false,
            'active' => false,
            'description' => 'Administrative leave due to suspension.',
        ],
        [
            'name' => 'Public Holiday Adjustment',
            'code' => 'PHA',
            'entitlement' => 10,
            'is_paid' => true,
            'active' => false,
            'description' => 'Adjustment leave for public holiday work.',
        ],
    ];

    foreach ($leaveTypes as $leaveType) {
        LeaveType::withTrashed()->updateOrCreate(
            ['name' => $leaveType['name']],
            $leaveType + ['is_locked' => true]
        );
    }
    }
}
