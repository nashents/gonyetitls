<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// ✅ Adjust these model namespaces to match your app
use App\Models\ModuleGroup;
use App\Models\Module;
use App\Models\SubModule;

class MenuRegistrySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * VISIBILITY JSON (simple + practical)
             * Your app should evaluate these keys when building the sidebar:
             * - any_roles: ["Admin","SuperAdmin","Management"]
             * - any_departments: ["HR","Finance","Transport","HSEQ","Security","Workshop","Stores"]
             * - any_ranks: ["HOD","Directors"]
             * - flags: ["is_business_admin","not_driver","has_vehicle_assignment","has_hseq_department","is_department_head_hr","is_department_head_finance","is_department_head_transport","is_department_head_workshop","is_department_head_stores"]
             * - route_params supports placeholders like "{employee_id}", "{company_id}", "{user_id}", "{hseq_department_id}"
             */

            $groups = [

                // =========================================================
                // MAIN CATEGORY
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Main Category',
                        'slug'       => 'main-category',
                        'icon'       => null,
                        'sort_order' => 10,
                        'is_active'  => true,
                        'visibility' => $this->vis([]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Dashboard',
                                'slug'       => 'dashboard',
                                'icon'       => 'fas fa-tachometer-alt',
                                'route_name' => 'dashboard.index',
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Companies',
                                'slug'       => 'companies',
                                'icon'       => 'fas fa-building',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles' => ['Admin', 'SuperAdmin'],
                                    'flags'     => ['is_business_admin'], // matches $is_admin in blade
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                [
                                    'name'       => 'Manage Companies',
                                    'slug'       => 'manage-companies',
                                    'icon'       => 'fas fa-list',
                                    'route_name' => 'companies.index',
                                    'url'        => null,
                                    'sort_order' => 10,
                                    'is_active'  => true,
                                    'badge_key'  => null,
                                    'visibility' => $this->vis([
                                        'any_roles' => ['Admin', 'SuperAdmin'],
                                        'flags'     => ['is_business_admin'],
                                    ]),
                                    'route_params' => null,
                                ],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Reminders',
                                'slug'       => 'reminders',
                                'icon'       => 'fas fa-bell',
                                'route_name' => 'reminders.index',
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],

                // =========================================================
                // HUMAN RESOURCE
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Human Resource',
                        'slug'       => 'human-resource',
                        'icon'       => null,
                        'sort_order' => 20,
                        'is_active'  => true,
                        'visibility' => $this->vis([
                            'any_departments' => ['HR'],
                            'any_roles'       => ['SuperAdmin'],
                        ]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Master',
                                'slug'       => 'hr-master',
                                'icon'       => 'fas fa-cog',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles'       => ['SuperAdmin'],
                                    'any_departments' => ['HR'],
                                    // blade: (($isAdmin && $inHR) || $isSuperAdmin)
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Allowances','slug'=>'allowances','icon'=>'fas fa-list','route_name'=>'allowances.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Branches','slug'=>'branches','icon'=>'fas fa-list','route_name'=>'branches.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Departments','slug'=>'departments','icon'=>'fas fa-list','route_name'=>'departments.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['flags'=>['is_business_admin']]),'route_params'=>null],
                                ['name'=>'Deductions','slug'=>'deductions','icon'=>'fas fa-list','route_name'=>'deductions.index','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Earnings','slug'=>'earnings','icon'=>'fas fa-list','route_name'=>'earnings.index','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Grades','slug'=>'grades','icon'=>'fas fa-list','route_name'=>'grades.index','url'=>null,'sort_order' => 60,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Job Titles','slug'=>'job-titles','icon'=>'fas fa-list','route_name'=>'job_titles.index','url'=>null,'sort_order'=>70,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Qualifications','slug'=>'qualifications','icon'=>'fas fa-list','route_name'=>'qualifications.index','url'=>null,'sort_order'=>80,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                                ['name'=>'Leave Types','slug'=>'leave-types','icon'=>'fas fa-list','route_name'=>'leave_types.index','url'=>null,'sort_order'=>90,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin'],'any_departments'=>['HR']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Employees',
                                'slug'       => 'employees',
                                'icon'       => 'fas fa-users',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_departments' => ['HR'],
                                    'any_roles'       => ['SuperAdmin'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Employee','slug'=>'create-employee','icon'=>'fas fa-plus','route_name'=>'employees.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Employees','slug'=>'manage-employees','icon'=>'fas fa-list','route_name'=>'employees.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Leave Days','slug'=>'manage-leave-days','icon'=>'fas fa-list','route_name'=>'employees.leaves.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Archived Employees','slug'=>'archived-employees','icon'=>'fas fa-archive','route_name'=>'employees.archived','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Deleted Employees','slug'=>'deleted-employees','icon'=>'fas fa-trash','route_name'=>'employees.deleted','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Head of Departments',
                                'slug'       => 'head-of-departments',
                                'icon'       => 'fas fa-user-plus',
                                'route_name' => 'department_heads.index',
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_departments' => ['HR'],
                                    'any_roles'       => ['SuperAdmin'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Drivers',
                                'slug'       => 'drivers',
                                'icon'       => 'fas fa-users',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_departments' => ['Transport', 'HR'],
                                    'any_roles'       => ['SuperAdmin'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Driver','slug'=>'create-driver','icon'=>'fas fa-plus','route_name'=>'drivers.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Transport','HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Drivers','slug'=>'manage-drivers','icon'=>'fas fa-list','route_name'=>'drivers.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Transport','HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Archived Drivers','slug'=>'archived-drivers','icon'=>'fas fa-archive','route_name'=>'drivers.archived','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Transport','HR'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Leave Management',
                                'slug'       => 'leave-management',
                                'icon'       => 'fas fa-calendar',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Apply for leave','slug'=>'apply-for-leave','icon'=>'fas fa-plus','route_name'=>'leaves.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([]),'route_params'=>null],
                                ['name'=>'My Team','slug'=>'leave-my-team','icon'=>'fas fa-users','route_name'=>'leaves.myteam','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([]),'route_params'=>null],

                                // “manage” set (restricted)
                                ['name'=>'Manage Applications','slug'=>'manage-leave-applications','icon'=>'fas fa-list','route_name'=>'leaves.manage','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'], // hrdepartment_head
                                    'any_roles_2'     => ['Admin','Management'],   // your evaluator can treat this as “OR”
                                ]),'route_params'=>null],

                                ['name'=>'Pending Applications','slug'=>'pending-leaves','icon'=>'fas fa-clock','route_name'=>'leaves.pending','url'=>null, 'sort_order' => 40,'is_active'=>true,'badge_key'=>'leaves_pending_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                    'any_roles_2'     => ['Admin','Management'],
                                ]),'route_params'=>null],

                                ['name'=>'Approved Applications','slug'=>'approved-leaves','icon'=>'fas fa-check','route_name'=>'leaves.approved','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'leaves_approved_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                    'any_roles_2'     => ['Admin','Management'],
                                ]),'route_params'=>null],

                                ['name'=>'Rejected Applications','slug'=>'rejected-leaves','icon'=>'fas fa-ban','route_name'=>'leaves.rejected','url'=>null,'sort_order'=>60,'is_active'=>true,'badge_key'=>'leaves_rejected_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                    'any_roles_2'     => ['Admin','Management'],
                                ]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Inbox',
                                'slug'       => 'inbox',
                                'icon'       => 'fas fa-envelope',
                                'route_name' => 'emails.index',
                                'url'        => null,
                                'sort_order' => 60,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],

                // =========================================================
                // SALARIES & PAYROLL
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Salaries & Payroll',
                        'slug'       => 'salaries-payroll',
                        'icon'       => null,
                        'sort_order' => 30,
                        'is_active'  => true,
                        'visibility' => $this->vis([]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Master',
                                'slug'       => 'payroll-master',
                                'icon'       => 'fas fa-cog',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin'],
                                    'any_departments' => ['HR'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Allowances','slug'=>'allowances','icon'=>'fas fa-list','route_name'=>'allowances.index','url'=>null, 'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Deductions','slug'=>'deductions','icon'=>'fas fa-list','route_name'=>'deductions.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Earnings','slug'=>'earnings','icon'=>'fas fa-list','route_name'=>'earnings.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Loan Type','slug'=>'loan-types','icon'=>'fas fa-list','route_name'=>'loan_types.index','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Tax Table','slug'=>'tax-brackets','icon'=>'fas fa-list','route_name'=>'tax_brackets.index','url'=>null, 'sort_order' => 50,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['flags'=>['is_business_admin'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'My Payslip',
                                'slug'       => 'my-payslip',
                                'icon'       => 'fas fa-file',
                                'route_name' => 'payslips.index',
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Loans',
                                'slug'       => 'loans',
                                'icon'       => 'fas fa-credit-card',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'My Applications','slug'=>'my-loan-applications','icon'=>'fas fa-arrow-right','route_name'=>'loans.myloans','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([]),'route_params'=>null],

                                ['name'=>'Manage Loans','slug'=>'manage-loans','icon'=>'fas fa-list','route_name'=>'loans.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Management'],
                                    'any_departments' => ['HR','Finance'],
                                    'flags'           => ['is_department_head_finance'], // fndepartment_head
                                ]),'route_params'=>null],

                                ['name'=>'Pending Loans','slug'=>'pending-loans','icon'=>'fas fa-clock','route_name'=>'loans.pending','url'=>null, 'sort_order' => 30,'is_active'=>true,'badge_key'=>'loans_pending_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Management'],
                                    'any_departments' => ['HR','Finance'],
                                    'flags'           => ['is_department_head_finance'],
                                ]),'route_params'=>null],

                                ['name'=>'Approved Loans','slug'=>'approved-loans','icon'=>'fas fa-check','route_name'=>'loans.approved','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'loans_approved_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Management'],
                                    'any_departments' => ['HR','Finance'],
                                    'flags'           => ['is_department_head_finance'],
                                ]),'route_params'=>null],

                                ['name'=>'Rejected Loans','slug'=>'rejected-loans','icon'=>'fas fa-ban','route_name'=>'loans.rejected','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'loans_rejected_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Management'],
                                    'any_departments' => ['HR','Finance'],
                                    'flags'           => ['is_department_head_finance'],
                                ]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Salaries',
                                'slug'       => 'salaries',
                                'icon'       => 'fas fa-donate',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin'],
                                    'any_departments' => ['HR'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Salary','slug'=>'create-salary','icon'=>'fas fa-plus','route_name'=>'salaries.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Salaries','slug'=>'manage-salaries','icon'=>'fas fa-list','route_name'=>'salaries.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Payroll',
                                'slug'       => 'payroll',
                                'icon'       => 'fas fa-file',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin'],
                                    'any_departments' => ['HR'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Payrolls','slug'=>'manage-payrolls','icon'=>'fas fa-list','route_name'=>'payrolls.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HR'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending Payrolls','slug'=>'pending-payrolls','icon'=>'fas fa-clock','route_name'=>'payrolls.pending','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>'payrolls_pending_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin','Management'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                ]),'route_params'=>null],
                                ['name'=>'Approved Payrolls','slug'=>'approved-payrolls','icon'=>'fas fa-check','route_name'=>'payrolls.approved','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'payrolls_approved_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin','Management'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                ]),'route_params'=>null],
                                ['name'=>'Rejected Payrolls','slug'=>'rejected-payrolls','icon'=>'fas fa-ban','route_name'=>'payrolls.rejected','url'=>null, 'sort_order' => 40,'is_active'=>true,'badge_key'=>'payrolls_rejected_count','visibility'=>$this->vis([
                                    'any_roles'       => ['SuperAdmin','Admin','Management'],
                                    'any_departments' => ['HR'],
                                    'flags'           => ['is_department_head_hr'],
                                ]),'route_params'=>null],
                            ],
                        ],
                    ],
                ],

                // =========================================================
                // SALES & PAYMENTS (FINANCE)
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Sales & Payments',
                        'slug'       => 'sales-payments',
                        'icon'       => null,
                        'sort_order' => 40,
                        'is_active'  => true,
                        'visibility' => $this->vis([
                            'any_departments' => ['Finance'],
                            'any_roles'       => ['SuperAdmin'],
                        ]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Master',
                                'slug'       => 'finance-master',
                                'icon'       => 'fas fa-cog',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_roles' => ['SuperAdmin'],
                                    // blade tries to restrict to Admin in Finance AND HR
                                    'any_departments' => ['Finance','HR'],
                                    'require_all_departments' => true,
                                    'any_roles_2' => ['Admin'],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Currencies','slug'=>'currencies','icon'=>'fas fa-money-bill-alt','route_name'=>'currencies.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin'],'any_departments'=>['Finance','HR'],'require_all_departments'=>true]),'route_params'=>null],
                                ['name'=>'Payment Methods','slug'=>'payment-methods','icon'=>'fas fa-list','route_name'=>'payment_methods.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin'],'any_departments'=>['Finance','HR'],'require_all_departments'=>true]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Quotations',
                                'slug'       => 'quotations',
                                'icon'       => 'fas fa-file-invoice',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Quotation','slug'=>'create-quotation','icon'=>'fas fa-plus','route_name'=>'quotations.create','url'=>null, 'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Quotations','slug'=>'manage-quotations','icon'=>'fas fa-list','route_name'=>'quotations.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Invoices',
                                'slug'       => 'invoices',
                                'icon'       => 'fas fa-file-invoice-dollar',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Invoice','slug'=>'create-invoice','icon'=>'fas fa-plus','route_name'=>'invoices.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Invoices','slug'=>'manage-invoices','icon'=>'fas fa-list','route_name'=>'invoices.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],

                                ['name'=>'Pending Invoices','slug'=>'pending-invoices','icon'=>'fas fa-clock','route_name'=>'invoices.pending','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'invoices_pending_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Approved Invoices','slug'=>'approved-invoices','icon'=>'fas fa-check','route_name'=>'invoices.approved','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'invoices_approved_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Rejected Invoices','slug'=>'rejected-invoices','icon'=>'fas fa-ban','route_name'=>'invoices.rejected','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'invoices_rejected_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Deleted Invoices','slug'=>'deleted-invoices','icon'=>'fas fa-trash','route_name'=>'invoices.deleted','url'=>null,'sort_order'=>60,'is_active'=>true,'badge_key'=>'invoices_deleted_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Customer Statements',
                                'slug'       => 'customer-statements',
                                'icon'       => 'fas fa-file-invoice-dollar',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Statements','slug'=>'manage-customer-statements','icon'=>'fas fa-list','route_name'=>'customer_statements.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Credit Notes',
                                'slug'       => 'credit-notes',
                                'icon'       => 'fas fa-file-invoice-dollar',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create','slug'=>'create-credit-note','icon'=>'fas fa-plus','route_name'=>'credit_notes.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage C Notes','slug'=>'manage-credit-notes','icon'=>'fas fa-list','route_name'=>'credit_notes.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending C Notes','slug'=>'pending-credit-notes','icon'=>'fas fa-clock','route_name'=>'credit_notes.pending','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'credit_notes_pending_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Approved C Notes','slug'=>'approved-credit-notes','icon'=>'fas fa-check','route_name'=>'credit_notes.approved','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'credit_notes_approved_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Rejected C Notes','slug'=>'rejected-credit-notes','icon'=>'fas fa-ban','route_name'=>'credit_notes.rejected','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'credit_notes_rejected_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                // blade uses credit_notes.rejected also for “deleted” (looks like a bug). Keep it aligned:
                                ['name'=>'Deleted C Notes','slug'=>'deleted-credit-notes','icon'=>'fas fa-trash','route_name'=>'credit_notes.rejected','url'=>null,'sort_order'=>60,'is_active'=>true,'badge_key'=>'credit_notes_deleted_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Payments',
                                'slug'       => 'payments',
                                'icon'       => 'fas fa-credit-card',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 60,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Payments','slug'=>'manage-payments','icon'=>'fas fa-list','route_name'=>'payments.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Receipts','slug'=>'manage-receipts','icon'=>'fas fa-list','route_name'=>'receipts.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Products & Services',
                                'slug'       => 'products-services-invoices',
                                'icon'       => 'fas fa-boxes',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 70,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                [
                                    'name'=>'Manage P & S',
                                    'slug'=>'manage-products-services-invoices',
                                    'icon'=>'fas fa-list',
                                    'route_name'=>'product_services.all',
                                    'url'=>null,
                                    'sort_order' => 10,
                                    'is_active'=>true,
                                    'badge_key'=>null,
                                    'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                    'route_params'=>['category' => 'invoices'],
                                ],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Customers',
                                'slug'       => 'customers',
                                'icon'       => 'fas fa-user-friends',
                                'route_name' => 'customers.index',
                                'url'        => null,
                                'sort_order' => 80,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Accounts Receivable',
                                'slug'       => 'accounts-receivable',
                                'icon'       => 'fas fa-list',
                                'route_name' => 'accounts.receivable',
                                'url'        => null,
                                'sort_order' => 90,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],

                // =========================================================
                // PURCHASES
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Purchases',
                        'slug'       => 'purchases',
                        'icon'       => null,
                        'sort_order' => 50,
                        'is_active'  => true,
                        'visibility' => $this->vis([]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Bills',
                                'slug'       => 'bills',
                                'icon'       => 'fas fa-th-list',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Bill','slug'=>'create-bill','icon'=>'fas fa-plus','route_name'=>'bills.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Bills','slug'=>'manage-bills','icon'=>'fas fa-list','route_name'=>'bills.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending Bills','slug'=>'pending-bills','icon'=>'fas fa-clock','route_name'=>'bills.pending','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'bills_pending_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Approved Bills','slug'=>'approved-bills','icon'=>'fas fa-check','route_name'=>'bills.approved','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'bills_approved_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Rejected Bills','slug'=>'rejected-bills','icon'=>'fas fa-ban','route_name'=>'bills.rejected','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'bills_rejected_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Vendor Statements',
                                'slug'       => 'vendor-statements',
                                'icon'       => 'fas fa-file-invoice-dollar',
                                'route_name' => null,
                                'url'        => null,
                               'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Statements','slug'=>'manage-vendor-statements','icon'=>'fas fa-list','route_name'=>'vendor_statements.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Products & Services',
                                'slug'       => 'products-services-bills',
                                'icon'       => 'fas fa-boxes',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                [
                                    'name'=>'Manage P & S',
                                    'slug'=>'manage-products-services-bills',
                                    'icon'=>'fas fa-list',
                                    'route_name'=>'product_services.all',
                                    'url'=>null,
                                    'sort_order' => 10,
                                    'is_active'=>true,
                                    'badge_key'=>null,
                                    'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                    'route_params'=>['category' => 'bills'],
                                ],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Vendors',
                                'slug'       => 'vendors',
                                'icon'       => 'fas fa-user-friends',
                                'route_name' => 'vendors.index',
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Accounts Payable',
                                'slug'       => 'accounts-payable',
                                'icon'       => 'fas fa-list',
                                'route_name' => 'accounts.payable',
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Requisitions',
                                'slug'       => 'requisitions',
                                'icon'       => 'fas fa-hand-holding-usd',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 60,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Requisitions','slug'=>'manage-requisitions','icon'=>'fas fa-list','route_name'=>'requisitions.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis([]),'route_params'=>null],
                                ['name'=>'Pending Requisitions','slug'=>'pending-requisitions','icon'=>'fas fa-clock','route_name'=>'requisitions.pending','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>'requisitions_pending_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Approved Requisitions','slug'=>'approved-requisitions','icon'=>'fas fa-check','route_name'=>'requisitions.approved','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'requisitions_approved_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Rejected Requisitions','slug'=>'rejected-requisitions','icon'=>'fas fa-ban','route_name'=>'requisitions.rejected','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'requisitions_rejected_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                            ],
                        ],
                    ],
                ],

                // =========================================================
                // ACCOUNTING
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Accounting',
                        'slug'       => 'accounting',
                        'icon'       => null,
                        'sort_order' => 60,
                        'is_active'  => true,
                        'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Transactions',
                                'slug'       => 'transactions',
                                'icon'       => 'fas fa-money-check',
                                'route_name' => 'transactions.index',
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Charts of Accounts',
                                'slug'       => 'charts-of-accounts',
                                'icon'       => 'fas fa-balance-scale',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Accounts','slug'=>'manage-accounts','icon'=>'fas fa-list','route_name'=>'accounts.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Sales Taxes','slug'=>'manage-sales-taxes','icon'=>'fas fa-list','route_name'=>'accounts.tax','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['flags'=>['is_business_admin'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Bank Accounts',
                                'slug'       => 'bank-accounts',
                                'icon'       => 'fas fa-bank',
                                'route_name' => 'bank_accounts.index',
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Currency Exchange Rates',
                                'slug'       => 'exchange-rates',
                                'icon'       => 'fas fa-exchange',
                                'route_name' => 'exchange_rates.index',
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],

                // =========================================================
                // ASSET MANAGEMENT
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Asset Management',
                        'slug'       => 'asset-management',
                        'icon'       => null,
                        'sort_order' => 70,
                        'is_active'  => true,
                        'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Master',
                                'slug'       => 'asset-master',
                                'icon'       => 'fas fa-cog',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Categories','slug'=>'asset-categories','icon'=>'fas fa-list','route_name'=>'categories.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Attributes','slug'=>'asset-attributes','icon'=>'fas fa-list','route_name'=>'attributes.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Brands','slug'=>'asset-brands','icon'=>'fas fa-list','route_name'=>'brands.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Products',
                                'slug'       => 'asset-products',
                                'icon'       => 'fas fa-boxes',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Product','slug'=>'create-asset-product','icon'=>'fas fa-plus','route_name'=>'products.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Products','slug'=>'manage-asset-products','icon'=>'fas fa-list','route_name'=>'products.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Purchase Orders',
                                'slug'       => 'asset-purchase-orders',
                                'icon'       => 'fas fa-hand-holding-usd',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Orders','slug'=>'manage-asset-orders','icon'=>'fas fa-list','route_name'=>'purchases.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending Orders','slug'=>'pending-asset-orders','icon'=>'fas fa-clock','route_name'=>'purchases.pending','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>'asset_purchases_pending_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Approved Orders','slug'=>'approved-asset-orders','icon'=>'fas fa-check','route_name'=>'purchases.approved','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'asset_purchases_approved_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Rejected Orders','slug'=>'rejected-asset-orders','icon'=>'fas fa-ban','route_name'=>'purchases.rejected','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'asset_purchases_rejected_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                                ['name'=>'Deleted Orders','slug'=>'deleted-asset-orders','icon'=>'fas fa-trash','route_name'=>'purchases.deleted','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>'asset_purchases_deleted_count','visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin','Management'],'flags'=>['is_department_head_finance']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'GRV (Assets)',
                                'slug'       => 'grv-assets',
                                'icon'       => 'fas fa-th-list',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Assets GRVs','slug'=>'manage-assets-grv','icon'=>'fas fa-list','route_name'=>'goods_receiveds.assets','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Assets',
                                'slug'       => 'assets',
                                'icon'       => 'fas fa-th-list',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Asset','slug'=>'create-asset','icon'=>'fas fa-plus','route_name'=>'assets.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Assets','slug'=>'manage-assets','icon'=>'fas fa-list','route_name'=>'assets.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Dispatches (Assets)',
                                'slug'       => 'asset-dispatches',
                                'icon'       => 'fas fa-list',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 60,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Dispatches','slug'=>'manage-asset-dispatches','icon'=>'fas fa-list','route_name'=>'asset_dispatches.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Finance'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending Dispatches','slug'=>'pending-asset-dispatches','icon'=>'fas fa-clock','route_name'=>'asset_dispatches.pending','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>'asset_dispatches_pending_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management']]),'route_params'=>null],
                                ['name'=>'Approved Dispatches','slug'=>'approved-asset-dispatches','icon'=>'fas fa-check','route_name'=>'asset_dispatches.approved','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'asset_dispatches_approved_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management']]),'route_params'=>null],
                                ['name'=>'Rejected Dispatches','slug'=>'rejected-asset-dispatches','icon'=>'fas fa-ban','route_name'=>'asset_dispatches.rejected','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'asset_dispatches_rejected_count','visibility'=>$this->vis(['any_roles'=>['SuperAdmin','Admin','Management']]),'route_params'=>null],
                            ],
                        ],
                    ],
                ],

                // =========================================================
                // SHEQ
                // =========================================================
                [
                    'group' => [
                        'name'       => 'SHEQ',
                        'slug'       => 'sheq',
                        'icon'       => null,
                        'sort_order' => 80,
                        'is_active'  => true,
                        'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Master',
                                'slug'       => 'sheq-master',
                                'icon'       => 'fas fa-cog',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin','Admin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Cause Categories','slug'=>'loss-categories','icon'=>'fas fa-list','route_name'=>'loss_categories.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Cause Groups','slug'=>'loss-groups','icon'=>'fas fa-list','route_name'=>'loss_groups.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Loss Causes','slug'=>'losses','icon'=>'fas fa-list','route_name'=>'losses.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['Admin','SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Incidents',
                                'slug'       => 'incidents',
                                'icon'       => 'fas fa-exclamation-triangle',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Incidents','slug'=>'create-incidents','icon'=>'fas fa-plus','route_name'=>'incidents.create','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Manage Incidents','slug'=>'manage-incidents','icon'=>'fas fa-list','route_name'=>'incidents.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Age Pyramid',
                                'slug'       => 'age-pyramid',
                                'icon'       => 'fas fa-hourglass',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Customers','slug'=>'age-customers','icon'=>'fas fa-list','route_name'=>'customers.age','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Drivers','slug'=>'age-drivers','icon'=>'fas fa-list','route_name'=>'drivers.age','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Employees','slug'=>'age-employees','icon'=>'fas fa-list','route_name'=>'employees.age','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Horses','slug'=>'age-horses','icon'=>'fas fa-list','route_name'=>'horses.age','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Trailers','slug'=>'age-trailers','icon'=>'fas fa-list','route_name'=>'trailers.age','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Vehicles','slug'=>'age-vehicles','icon'=>'fas fa-list','route_name'=>'vehicles.age','url'=>null,'sort_order'=>60,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Vendors','slug'=>'age-vendors','icon'=>'fas fa-list','route_name'=>'vendors.age','url'=>null,'sort_order'=>70,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Compliance',
                                'slug'       => 'compliance',
                                'icon'       => 'fas fa-check',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 40,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Driver - Route Compliance','slug'=>'driver-route-compliance','icon'=>'fas fa-list','route_name'=>'compliances.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Training Workshops',
                                'slug'       => 'training-workshops',
                                'icon'       => 'fas fa-school',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 50,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'What to train?','slug'=>'training-items','icon'=>'fas fa-list','route_name'=>'training_items.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Who to train?','slug'=>'training-departments','icon'=>'fas fa-list','route_name'=>'training_departments.index','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Who needs training?','slug'=>'training-requirements','icon'=>'fas fa-list','route_name'=>'training_requirements.index','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Training Plan','slug'=>'training-plans','icon'=>'fas fa-list','route_name'=>'training_plans.index','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Training Program','slug'=>'trainings','icon'=>'fas fa-list','route_name'=>'trainings.index','url'=>null,'sort_order' => 50,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['HSEQ'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Documents',
                                'slug'       => 'hseq-documents',
                                'icon'       => 'fas fa-file',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 60,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([
                                    'any_departments'=>['HSEQ'],
                                    'any_roles'=>['SuperAdmin'],
                                    'flags'=>['has_hseq_department'], // $hseq_department exists
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                [
                                    'name'=>'Manage Documents',
                                    'slug'=>'manage-hseq-documents',
                                    'icon'=>'fas fa-list',
                                    'route_name'=>'documents.all',
                                    'url'=>null,
                                    'sort_order' => 10,
                                    'is_active'=>true,
                                    'badge_key'=>null,
                                    'visibility'=>$this->vis([
                                        'any_departments'=>['HSEQ'],
                                        'any_roles'=>['SuperAdmin'],
                                        'flags'=>['has_hseq_department'],
                                    ]),
                                    'route_params'=>['id' => '{hseq_department_id}', 'category' => 'department'],
                                ],
                            ],
                        ],
                    ],
                ],

                // =========================================================
                // GENERAL ACCESS (SECURITY)
                // =========================================================
                [
                    'group' => [
                        'name'       => 'General Access',
                        'slug'       => 'general-access',
                        'icon'       => null,
                        'sort_order' => 90,
                        'is_active'  => true,
                        'visibility' => $this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Gatepass',
                                'slug'       => 'gatepass-security',
                                'icon'       => 'fas fa-door-open',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Gatepasses','slug'=>'manage-gatepasses-security','icon'=>'fas fa-list','route_name'=>'gate_passes.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                                ['name'=>'Pending Gatepasses','slug'=>'pending-gatepasses-security','icon'=>'fas fa-clock','route_name'=>'gate_passes.pending','url'=>null,'sort_order' => 20,'is_active'=>true,'badge_key'=>'gate_passes_pending_count','visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>['department'=>'security']],
                                ['name'=>'Approved Gatepasses','slug'=>'approved-gatepasses-security','icon'=>'fas fa-check','route_name'=>'gate_passes.approved','url'=>null,'sort_order' => 30,'is_active'=>true,'badge_key'=>'gate_passes_approved_count','visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>['department'=>'security']],
                                ['name'=>'Rejected Gatepasses','slug'=>'rejected-gatepasses-security','icon'=>'fas fa-ban','route_name'=>'gate_passes.rejected','url'=>null,'sort_order' => 40,'is_active'=>true,'badge_key'=>'gate_passes_rejected_count','visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>['department'=>'security']],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Groups',
                                'slug'       => 'groups',
                                'icon'       => 'fas fa-users',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Groups','slug'=>'manage-groups','icon'=>'fas fa-list','route_name'=>'groups.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                        [
                            'module' => [
                                'name'       => 'Visitors',
                                'slug'       => 'visitors',
                                'icon'       => 'fas fa-user-friends',
                                'route_name' => null,
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Visitors','slug'=>'manage-visitors','icon'=>'fas fa-list','route_name'=>'visitors.index','url'=>null,'sort_order' => 10,'is_active'=>true,'badge_key'=>null,'visibility'=>$this->vis(['any_departments'=>['Security'],'any_roles'=>['SuperAdmin']]),'route_params'=>null],
                            ],
                        ],
                    ],
                ],

                
                // =========================================================
                // Fleet Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Fleet Management',
                        'slug' => 'fleet-management',
                        'icon' => 'fas fa-truck',
                        'sort_order' => 100,
                        'url' => null,
                        'is_active' => true,
                        'visibility' => $this->vis([
                            'departments' => ['Transport & Logistcs', 'Workshop'],
                            'or' => ['is_super_admin' => true],
                            'not_driver' => true,
                        ]),
                    ],
                    'modules' => [

                        // Master (flattened)
                        [
                            'module' => [
                                'name' => 'Master',
                                'slug' => 'fleet-master',
                                'icon' => 'fas fa-cog',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'is_active' => true,
                                'sort_order' => 10,
                                'badge_key' => null,
                                'visibility' => $this->vis([
                                    'or' => ['is_admin' => true, 'is_super_admin' => true],
                                ]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Fleet Clusters','slug'=>'clusters','icon'=>'fas fa-list','route_name'=>'clusters.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Horse Groups','slug'=>'horse-groups','icon'=>'fas fa-list','route_name'=>'horse_groups.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Horse Makes','slug'=>'horse-makes','icon'=>'fas fa-list','route_name'=>'horse_makes.index','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Horse Types','slug'=>'horse-types','icon'=>'fas fa-list','route_name'=>'horse_types.index','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Trailer Groups','slug'=>'trailer-groups','icon'=>'fas fa-list','route_name'=>'trailer_groups.index','route_params'=>null,'url'=>null,'sort_order' => 50,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Trailer Types','slug'=>'trailer-types','icon'=>'fas fa-list','route_name'=>'trailer_types.index','route_params'=>null,'url'=>null,'sort_order' => 60,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Vehicle Groups','slug'=>'vehicle-groups','icon'=>'fas fa-list','route_name'=>'vehicle_groups.index','route_params'=>null,'url'=>null,'sort_order' => 70,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Vehicle Makes','slug'=>'vehicle-makes','icon'=>'fas fa-list','route_name'=>'vehicle_makes.index','route_params'=>null,'url'=>null,'sort_order' => 80,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Vehicle Types','slug'=>'vehicle-types','icon'=>'fas fa-list','route_name'=>'vehicle_types.index','route_params'=>null,'url'=>null,'sort_order' => 90,'badge_key'=>null,'visibility'=>$this->vis([])],

                                // Flattened "Fleet Inspections" under Master
                                ['name'=>'Fleet Inspections - Checklists','slug'=>'checklist-categories','icon'=>'fas fa-list','route_name'=>'checklist_categories.index','route_params'=>null,'url'=>null,'sort_order' => 100,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Fleet Inspections - Checklist Item Groups','slug'=>'checklist-sub-categories','icon'=>'fas fa-list','route_name'=>'checklist_sub_categories.index','route_params'=>null,'url'=>null,'sort_order' => 110,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Fleet Inspections - Checklist Items','slug'=>'checklist-items','icon'=>'fas fa-list','route_name'=>'checklist_items.index','route_params'=>null,'url'=>null,'sort_order' => 120,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Horses',
                                'slug' => 'horses',
                                'icon' => 'fas fa-truck',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Horse','slug'=>'horses-create','icon'=>'fas fa-plus','route_name'=>'horses.create','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Horses','slug'=>'horses-index','icon'=>'fas fa-list','route_name'=>'horses.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Archived Horses','slug'=>'horses-archived','icon'=>'fas fa-archive','route_name'=>'horses.archived','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Trailers',
                                'slug' => 'trailers',
                                'icon' => 'fas fa-trailer',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Trailers','slug'=>'trailers-index','icon'=>'fas fa-list','route_name'=>'trailers.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Trailer Links','slug'=>'trailer-links','icon'=>'fas fa-list','route_name'=>'trailer_links.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Archived Trailers','slug'=>'trailers-archived','icon'=>'fas fa-archive','route_name'=>'trailers.archived','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Vehicles',
                                'slug' => 'vehicles',
                                'icon' => 'fas fa-car',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Vehicle','slug'=>'vehicles-create','icon'=>'fas fa-plus','route_name'=>'vehicles.create','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Vehicles','slug'=>'vehicles-index','icon'=>'fas fa-list','route_name'=>'vehicles.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Archived Vehicles','slug'=>'vehicles-archived','icon'=>'fas fa-archive','route_name'=>'vehicles.archived','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Assignments',
                                'slug' => 'assignments',
                                'icon' => 'fas fa-user-plus',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Driver - Horse','slug'=>'assignments-driver-horse','icon'=>'fas fa-plus','route_name'=>'assignments.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Horse - Trailer','slug'=>'assignments-horse-trailer','icon'=>'fas fa-plus','route_name'=>'trailer_assignments.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Employee - Vehicle','slug'=>'assignments-employee-vehicle','icon'=>'fas fa-plus','route_name'=>'vehicle_assignments.index','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Fleet Inspections',
                                'slug' => 'fleet-inspections',
                                'icon' => 'fas fa-search',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 60,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Inspections','slug'=>'checklists-index','icon'=>'fas fa-tasks','route_name'=>'checklists.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],
                    ],
                ],


                // =========================================================
                // Fuel Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Fuel Management',
                        'slug' => 'fuel-management',
                        'icon' => 'fas fa-gas-pump',
                        'is_active' => true,
                        'url' => null,
                        'sort_order' => 110,
                        'visibility' => $this->vis([
                            'departments' => ['Transport & Logistcs'],
                            'or' => ['is_super_admin' => true],
                        ]),
                    ],
                    'modules' => [

                        [
                            'module' => [
                                'name' => 'Fueling Stations',
                                'slug' => 'fueling-stations',
                                'icon' => 'fas fa-oil-can',
                                'route_name' => null,
                                'sort_order' => 10,
                                'is_active' => true,
                                'route_params' => null,
                                'url' => null,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Stations','slug'=>'containers-index','icon'=>'fas fa-list','route_name'=>'containers.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Fuel Transfers','slug'=>'transfers-fuel','icon'=>'fas fa-list','route_name'=>'transfers.fuel','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Fuel Stations TopUps',
                                'slug' => 'fuel-topups',
                                'icon' => 'fas fa-fill-drip',
                                'route_name' => null,
                                'route_params' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'url' => null,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Fuel Top Ups','slug'=>'topups-index','icon'=>'fas fa-list','route_name'=>'top_ups.index','route_params'=>null,'url'=>null, 'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Top Ups','slug'=>'topups-pending','icon'=>'fas fa-clock','route_name'=>'top_ups.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>'top_ups_pending','visibility'=>$this->vis(['or'=>['is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Top Ups','slug'=>'topups-approved','icon'=>'fas fa-check','route_name'=>'top_ups.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'top_ups_approved','visibility'=>$this->vis(['or'=>['is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Top Ups','slug'=>'topups-rejected','icon'=>'fas fa-ban','route_name'=>'top_ups.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'top_ups_rejected','visibility'=>$this->vis(['or'=>['is_admin'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Fuel Orders',
                                'slug' => 'fuel-orders',
                                'icon' => 'fas fa-receipt',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Fuel Orders','slug'=>'fuels-index','icon'=>'fas fa-list','route_name'=>'fuels.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Fuel Orders','slug'=>'fuels-pending','icon'=>'fas fa-clock','route_name'=>'fuels.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>'fuels_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Fuel Orders','slug'=>'fuels-approved','icon'=>'fas fa-check','route_name'=>'fuels.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'fuels_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Fuel Orders','slug'=>'fuels-rejected','icon'=>'fas fa-ban','route_name'=>'fuels.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'fuels_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Deleted Fuel Orders','slug'=>'fuels-deleted','icon'=>'fas fa-trash','route_name'=>'fuels.deleted','route_params'=>null,'url'=>null, 'sort_order' => 50, 'is_active' => true, 'badge_key'=>'fuels_deleted', 'visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Fuel Allocations',
                                'slug' => 'fuel-allocations',
                                'icon' => 'fas fa-random',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                [
                                    'name' => 'My Allocation',
                                    'slug' => 'allocations-myallocations',
                                    'icon' => 'fas fa-arrow-right',
                                    'route_name' => 'allocations.myallocations',
                                    'route_params' => ['id' => '{employee_id}'], // ✅ required {id}
                                    'url' => null,
                                    'sort_order' => 10,
                                    'is_active' => true,
                                    'badge_key' => 'my_allocation_count',
                                    'visibility' => $this->vis([]),
                                ],
                                [
                                    'name' => 'Manage Allocation',
                                    'slug' => 'allocations-index',
                                    'icon' => 'fas fa-list',
                                    'route_name' => 'allocations.index',
                                    'route_params' => null,
                                    'url' => null,
                                    'sort_order' => 20,
                                    'is_active' => true,
                                    'badge_key' => null,
                                    'visibility' => $this->vis(['or'=>['is_admin'=>true,'is_super_admin'=>true]]),
                                ],
                            ],
                        ],

                        [
                            'module' => [
                                'name' => 'Fuel Requisitions',
                                'slug' => 'fuel-requisitions',
                                'icon' => 'fas fa-clipboard-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                [
                                    'name' => 'My Requests',
                                    'slug' => 'fuel-requests-myrequests',
                                    'icon' => 'fas fa-arrow-right',
                                    'route_name' => 'fuel_requests.myrequests',
                                    'route_params' => ['id' => '{employee_id}'],
                                    'url' => null,
                                    'sort_order' => 10,
                                    'is_active' => true,
                                    'badge_key' => null,
                                    'visibility' => $this->vis([]),
                                ],
                                ['name'=>'Pending Requests','slug'=>'fuel-requests-pending','icon'=>'fas fa-clock','route_name'=>'fuel_requests.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>'fuel_requests_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Requests','slug'=>'fuel-requests-approved','icon'=>'fas fa-check','route_name'=>'fuel_requests.approved','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true,'badge_key'=>'fuel_requests_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Requests','slug'=>'fuel-requests-rejected','icon'=>'fas fa-ban','route_name'=>'fuel_requests.rejected','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true,'badge_key'=>'fuel_requests_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                    ],
                ],

                                // =========================================================
                // Trip Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Trip Management',
                        'slug' => 'trip-management',
                        'icon' => 'fas fa-road',
                        'sort_order' => 120,
                        'url' => null,
                        'is_active' => true,
                        'visibility' => $this->vis([
                            'or' => ['in_finance' => true, 'in_transport' => true, 'is_super_admin' => true],
                        ]),
                    ],
                    'modules' => [

                        // Master
                        [
                            'module' => [
                                'name' => 'Master',
                                'slug' => 'trip-master',
                                'icon' => 'fas fa-cog',
                                'route_name' => null,
                                'url' => null,
                                'sort_order' => 10,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([
                                    'or' => ['is_super_admin' => true, 'in_transport_admin' => true],
                                ]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [
                                ['name'=>'Agents','slug'=>'agents-index','icon'=>'fas fa-list','route_name'=>'agents.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Borders','slug'=>'borders-index','icon'=>'fas fa-bars','route_name'=>'borders.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Brokers','slug'=>'brokers-index','icon'=>'fas fa-list','route_name'=>'brokers.index','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Cargos','slug'=>'cargos-index','icon'=>'fas fa-truck-loading','route_name'=>'cargos.index','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Clearing Agents','slug'=>'clearing-agents-index','icon'=>'fas fa-building','route_name'=>'clearing_agents.index','route_params'=>null,'url'=>null,'sort_order' => 50,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Countries','slug'=>'countries-index','icon'=>'fas fa-globe-africa','route_name'=>'countries.index','route_params'=>null,'url'=>null,'sort_order' => 60,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Consignees','slug'=>'consignees-index','icon'=>'fas fa-users','route_name'=>'consignees.index','route_params'=>null,'url'=>null,'sort_order' => 70,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Corridors','slug'=>'corridors-index','icon'=>'fas fa-road','route_name'=>'corridors.index','route_params'=>null,'url'=>null,'sort_order' => 80,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Deductions','slug'=>'deductions-index','icon'=>'fas fa-list','route_name'=>'deductions.index','route_params'=>null,'url'=>null,'sort_order' => 90,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Destinations','slug'=>'destinations-index','icon'=>'fas fa-map-pin','route_name'=>'destinations.index','route_params'=>null,'url'=>null,'sort_order' => 100,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Expenses','slug'=>'expenses-index','icon'=>'fas fa-list','route_name'=>'expenses.index','route_params'=>null,'url'=>null,'sort_order' => 110,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Loading Points','slug'=>'loading-points-index','icon'=>'fas fa-map-marker','route_name'=>'loading_points.index','route_params'=>null,'url'=>null,'sort_order' => 120,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Offloading Points','slug'=>'offloading-points-index','icon'=>'fas fa-map-marker','route_name'=>'offloading_points.index','route_params'=>null,'url'=>null,'sort_order' => 130,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Provinces','slug'=>'provinces-index','icon'=>'fas fa-globe-africa','route_name'=>'provinces.index','route_params'=>null,'url'=>null,'sort_order' => 140,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Rehandling Jobs','slug'=>'works-index','icon'=>'fas fa-list','route_name'=>'works.index','route_params'=>null,'url'=>null,'sort_order' => 150,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Road Routes','slug'=>'routes-index','icon'=>'fas fa-road','route_name'=>'routes.index','route_params'=>null,'url'=>null,'sort_order' => 160,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],

                                // Super Admin only
                                ['name'=>'Teams','slug'=>'teams-index','icon'=>'fas fa-users','route_name'=>'teams.index','route_params'=>null,'url'=>null,'sort_order' => 170,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis(['or'=>['is_super_admin'=>true]])],

                                // Finance or Super Admin
                                ['name'=>'Trip Rates','slug'=>'rates-index','icon'=>'fas fa-list','route_name'=>'rates.index','route_params'=>null,'url'=>null,'sort_order' => 180,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis(['or'=>['in_finance'=>true,'is_super_admin'=>true]])],

                                ['name'=>'Trip Types','slug'=>'trip-types-index','icon'=>'fas fa-road','route_name'=>'trip_types.index','route_params'=>null,'url'=>null, 'sort_order' => 190,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Truck Stops','slug'=>'truck-stops-index','icon'=>'fas fa-stop','route_name'=>'truck_stops.index','route_params'=>null,'url'=>null,'sort_order' => 200,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Worksites','slug'=>'locations-index','icon'=>'fas fa-map-marker','route_name'=>'locations.index','route_params'=>null,'url'=>null,'sort_order' => 210,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Log Book (no children)
                        [
                            'module' => [
                                'name' => 'Log Book',
                                'slug' => 'log-book',
                                'icon' => 'fas fa-book',
                                'route_name' => 'logs.index',
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([
                                    'or' => ['is_super_admin' => true, 'has_vehicle_assignment' => true],
                                ]),
                            ],
                            'sub_modules' => [],
                        ],

                        // Rental
                        [
                            'module' => [
                                'name' => 'Car Rental',
                                'slug' => 'rental',
                                'icon' => 'fas fa-car',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active'  => false,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Rentals','slug'=>'rentals-index','icon'=>'fas fa-list','route_name'=>'rentals.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active'  => false,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Rentals','slug'=>'rentals-pending','icon'=>'fas fa-clock','route_name'=>'rentals.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active'  => false,'badge_key'=>'rentals_pending','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Rentals','slug'=>'rentals-approved','icon'=>'fas fa-check','route_name'=>'rentals.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active'  => false,'badge_key'=>'rentals_approved','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Rentals','slug'=>'rentals-rejected','icon'=>'fas fa-ban','route_name'=>'rentals.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active'  => false,'badge_key'=>'rentals_rejected','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                            ],
                        ],
                        // Transporters
                        [
                            'module' => [
                                'name' => 'Transporters',
                                'slug' => 'transporters',
                                'icon' => 'fas fa-truck',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Transporters','slug'=>'transporters-index','icon'=>'fas fa-list','route_name'=>'transporters.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active'  => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Transporters','slug'=>'transporters-pending','icon'=>'fas fa-clock','route_name'=>'transporters.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active'  => true,'badge_key'=>'transporters_pending','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Transporters','slug'=>'transporters-approved','icon'=>'fas fa-check','route_name'=>'transporters.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active'  => true,'badge_key'=>'transporters_approved','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Transporters','slug'=>'transporters-rejected','icon'=>'fas fa-ban','route_name'=>'transporters.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active'  => true,'badge_key'=>'transporters_rejected','visibility'=>$this->vis(['or'=>['in_transport_admin'=>true,'in_transport_management'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        // Shifts
                        [
                            'module' => [
                                'name' => 'Shifts',
                                'slug' => 'shifts',
                                'icon' => 'fas fa-clock',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Shifts','slug'=>'shifts-index','icon'=>'fas fa-list','route_name'=>'shifts.index','route_params'=>null,'url'=>null, 'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Shifts Reports','slug'=>'shifts-reports','icon'=>'fas fa-line-chart','route_name'=>'shifts.reports','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Trips
                        [
                            'module' => [
                                'name' => 'Trips',
                                'slug' => 'trips',
                                'icon' => 'fas fa-road',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 60,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Trip','slug'=>'trips-create','icon'=>'fas fa-plus','route_name'=>'trips.create','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Trips','slug'=>'trips-index','icon'=>'fas fa-list','route_name'=>'trips.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Trips','slug'=>'trips-pending','icon'=>'fas fa-clock','route_name'=>'trips.pending','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'trips_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Trips','slug'=>'trips-approved','icon'=>'fas fa-check','route_name'=>'trips.approved','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>'trips_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Trips','slug'=>'trips-rejected','icon'=>'fas fa-ban','route_name'=>'trips.rejected','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>'trips_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Deleted Trips','slug'=>'trips-deleted','icon'=>'fas fa-trash','route_name'=>'trips.deleted','route_params'=>null,'url'=>null,'sort_order' => 60, 'is_active' => true, 'badge_key'=>'trips_deleted','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Tracking Groups','slug'=>'trip-groups-index','icon'=>'fas fa-list','route_name'=>'trip_groups.index','route_params'=>null,'url'=>null,'sort_order' => 70, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Gatepass (Logistics)
                        [
                            'module' => [
                                'name' => 'Gatepass',
                                'slug' => 'gatepass-logistics',
                                'icon' => 'fas fa-door-open',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 70,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Pending Gatepasses','slug'=>'gatepass-logistics-pending','icon'=>'fas fa-clock','route_name'=>'gate_passes.pending','route_params'=>['department'=>'logistics'],'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>'logistics_gate_passes_pending','visibility'=>$this->vis([])],
                                ['name'=>'Approved Gatepasses','slug'=>'gatepass-logistics-approved','icon'=>'fas fa-check','route_name'=>'gate_passes.approved','route_params'=>['department'=>'logistics'],'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'logistics_gate_passes_approved','visibility'=>$this->vis([])],
                                ['name'=>'Rejected Gatepasses','slug'=>'gatepass-logistics-rejected','icon'=>'fas fa-ban','route_name'=>'gate_passes.rejected','route_params'=>['department'=>'logistics'],'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'logistics_gate_passes_rejected','visibility'=>$this->vis([])],
                            ],
                        ],

                        // Recoveries
                        [
                            'module' => [
                                'name' => 'Recoveries',
                                'slug' => 'recoveries',
                                'icon' => 'fas fa-hand-holding-usd',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 80,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['not_driver' => true]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Recovery','slug'=>'recoveries-create','icon'=>'fas fa-plus','route_name'=>'recoveries.create','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Recoveries','slug'=>'recoveries-index','icon'=>'fas fa-list','route_name'=>'recoveries.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Recoveries','slug'=>'recoveries-pending','icon'=>'fas fa-clock','route_name'=>'recoveries.pending','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'recoveries_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Recoveries','slug'=>'recoveries-approved','icon'=>'fas fa-check','route_name'=>'recoveries.approved','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>'recoveries_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Recoveries','slug'=>'recoveries-rejected','icon'=>'fas fa-ban','route_name'=>'recoveries.rejected','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>'recoveries_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_super_admin'=>true]])],
                            ],
                        ],
                    ],
                ],


                // =========================================================
                // Workshop Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Workshop Management',
                        'slug' => 'workshop-management',
                        'icon' => 'fas fa-tools',
                        'sort_order' => 130,
                        'is_active' => true,
                        'url' => null,
                        'visibility' => $this->vis([
                            'or' => ['in_finance' => true, 'in_workshop' => true, 'in_stores' => true, 'is_super_admin' => true],
                        ]),
                    ],
                    'modules' => [

                        // Master
                        [
                            'module' => [
                                'name' => 'Master',
                                'slug' => 'workshop-master',
                                'icon' => 'fas fa-cog',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 10,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([
                                    'or' => ['ws_department_head' => true, 'in_workshop_admin' => true, 'is_super_admin' => true],
                                ]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Job Types','slug'=>'service-types-index','icon'=>'fas fa-list','route_name'=>'service_types.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Inspection Item Groups','slug'=>'inspection-groups-index','icon'=>'fas fa-list','route_name'=>'inspection_groups.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Inspection Items','slug'=>'inspection-types-index','icon'=>'fas fa-list','route_name'=>'inspection_types.index','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Workshop Stations','slug'=>'stations-index','icon'=>'fas fa-list','route_name'=>'stations.index','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Bookings
                        [
                            'module' => [
                                'name' => 'Bookings',
                                'slug' => 'bookings',
                                'icon' => 'fas fa-tasks',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Booking','slug'=>'bookings-create','icon'=>'fas fa-plus','route_name'=>'bookings.create','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Bookings','slug'=>'bookings-index','icon'=>'fas fa-list','route_name'=>'bookings.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Bookings','slug'=>'bookings-pending','icon'=>'fas fa-clock','route_name'=>'bookings.pending','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'bookings_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'in_workshop_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Bookings','slug'=>'bookings-approved','icon'=>'fas fa-check','route_name'=>'bookings.approved','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>'bookings_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'in_workshop_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Bookings','slug'=>'bookings-rejected','icon'=>'fas fa-ban','route_name'=>'bookings.rejected','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>'bookings_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'in_workshop_admin'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        // Tickets
                        [
                            'module' => [
                                'name' => 'Tickets',
                                'slug' => 'tickets',
                                'icon' => 'fas fa-file-invoice',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Tickets','slug'=>'tickets-index','icon'=>'fas fa-tasks','route_name'=>'tickets.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis(['or'=>['st_department_head'=>true,'ws_department_head'=>true,'in_workshop_admin'=>true,'in_stores_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'My Tickets','slug'=>'tickets-cards','icon'=>'fas fa-tasks','route_name'=>'tickets.cards','route_params'=>['id'=>'{employee_id}'],'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'job_cards_count','visibility'=>$this->vis(['departments'=>['Workshop']])],
                            ],
                        ],

                        // Ticket Inspections
                        [
                            'module' => [
                                'name' => 'Ticket Inspections',
                                'slug' => 'ticket-inspections',
                                'icon' => 'fas fa-search',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Inspections','slug'=>'inspections-index','icon'=>'fas fa-tasks','route_name'=>'inspections.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis(['or'=>['st_department_head'=>true,'ws_department_head'=>true,'in_workshop_admin'=>true,'in_stores_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'My Inspections','slug'=>'inspections-my','icon'=>'fas fa-tasks','route_name'=>'inspections.my-inspections','route_params'=>['id'=>'{employee_id}'],'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'inspections_count','visibility'=>$this->vis(['departments'=>['Workshop']])],
                            ],
                        ],

                        // Gatepass (Workshop)
                        [
                            'module' => [
                                'name' => 'Gatepass',
                                'slug' => 'gatepass-workshop',
                                'icon' => 'fas fa-door-open',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['or'=>['in_workshop_admin'=>true,'is_super_admin'=>true]]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Pending Gatepasses','slug'=>'gatepass-workshop-pending','icon'=>'fas fa-clock','route_name'=>'gate_passes.pending','route_params'=>['department'=>'workshop'],'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>'workshop_gate_passes_pending','visibility'=>$this->vis([])],
                                ['name'=>'Approved Gatepasses','slug'=>'gatepass-workshop-approved','icon'=>'fas fa-check','route_name'=>'gate_passes.approved','route_params'=>['department'=>'workshop'],'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'workshop_gate_passes_approved','visibility'=>$this->vis([])],
                                ['name'=>'Rejected Gatepasses','slug'=>'gatepass-workshop-rejected','icon'=>'fas fa-ban','route_name'=>'gate_passes.rejected','route_params'=>['department'=>'workshop'],'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'workshop_gate_passes_rejected','visibility'=>$this->vis([])],
                            ],
                        ],
                    ],
                ],


                // =========================================================
                // Stores & Inventory Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Stores & Inventory Management',
                        'slug' => 'stores-inventory-management',
                        'icon' => 'fas fa-warehouse',
                        'sort_order' => 140,
                        'url' => null,
                        'is_active' => true,
                        'visibility' => $this->vis([
                            'or' => ['in_stores' => true, 'is_super_admin' => true],
                        ]),
                    ],
                    'modules' => [

                        // Master
                        [
                            'module' => [
                                'name' => 'Master',
                                'slug' => 'stores-master',
                                'icon' => 'fas fa-cog',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 10,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis(['or'=>['st_department_head'=>true,'in_stores_admin'=>true,'is_super_admin'=>true]]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Attributes','slug'=>'attributes-index','icon'=>'fas fa-list','route_name'=>'attributes.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Bins','slug'=>'bins-index','icon'=>'fas fa-list','route_name'=>'bins.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Brands','slug'=>'brands-index','icon'=>'fas fa-list','route_name'=>'brands.index','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Categories','slug'=>'categories-index','icon'=>'fas fa-list','route_name'=>'categories.index','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Racks','slug'=>'racks-index','icon'=>'fas fa-list','route_name'=>'racks.index','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Stores','slug'=>'stores-index','icon'=>'fas fa-list','route_name'=>'stores.index','route_params'=>null,'url'=>null,'sort_order' => 60, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Inventory Transfers
                        [
                            'module' => [
                                'name' => 'Inventory Transfers',
                                'slug' => 'inventory-transfers',
                                'icon' => 'fas fa-exchange',
                                'route_name' => null,
                                'route_params' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'url' => null,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Transfers','slug'=>'inventory-transfers-index','icon'=>'fas fa-list','route_name'=>'inventory_transfers.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Transfers','slug'=>'inventory-transfers-pending','icon'=>'fas fa-clock','route_name'=>'inventory_transfers.pending','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'inventory_transfers_pending','visibility'=>$this->vis([])],
                                ['name'=>'Approved Transfers','slug'=>'inventory-transfers-approved','icon'=>'fas fa-check','route_name'=>'inventory_transfers.approved','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'inventory_transfers_approved','visibility'=>$this->vis([])],
                                ['name'=>'Rejected Transfers','slug'=>'inventory-transfers-rejected','icon'=>'fas fa-ban','route_name'=>'inventory_transfers.rejected','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>'inventory_transfers_rejected','visibility'=>$this->vis([])],
                            ],
                        ],

                        // Products
                        [
                            'module' => [
                                'name' => 'Products',
                                'slug' => 'inventory-products',
                                'icon' => 'fas fa-boxes',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Product','slug'=>'inventory-products-create','icon'=>'fas fa-plus','route_name'=>'inventory_products.create','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Products','slug'=>'inventory-products-index','icon'=>'fas fa-list','route_name'=>'inventory_products.index','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Purchase Orders
                        [
                            'module' => [
                                'name' => 'Purchase Orders',
                                'slug' => 'inventory-purchase-orders',
                                'icon' => 'fas fa-hand-holding-usd',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Orders','slug'=>'inventory-purchases-index','icon'=>'fas fa-list','route_name'=>'inventory_purchases.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Orders','slug'=>'inventory-purchases-pending','icon'=>'fas fa-clock','route_name'=>'inventory_purchases.pending','route_params'=>null,'url'=>null,'sort_order' => 20, 'is_active' => true, 'badge_key'=>'inventory_purchases_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Orders','slug'=>'inventory-purchases-approved','icon'=>'fas fa-check','route_name'=>'inventory_purchases.approved','route_params'=>null,'url'=>null,'sort_order' => 30, 'is_active' => true, 'badge_key'=>'inventory_purchases_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Orders','slug'=>'inventory-purchases-rejected','icon'=>'fas fa-ban','route_name'=>'inventory_purchases.rejected','route_params'=>null,'url'=>null,'sort_order' => 40, 'is_active' => true, 'badge_key'=>'inventory_purchases_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Deleted Orders','slug'=>'inventory-purchases-deleted','icon'=>'fas fa-trash','route_name'=>'inventory_purchases.deleted','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>'inventory_purchases_deleted','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        // GRV (Inventory)
                        [
                            'module' => [
                                'name' => 'GRV (Inventory)',
                                'slug' => 'inventory-grv',
                                'icon' => 'fas fa-th-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Inventory GRVs','slug'=>'goods-receiveds-index','icon'=>'fas fa-list','route_name'=>'goods_receiveds.index','route_params'=>null,'url'=>null,'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Inventory
                        [
                            'module' => [
                                'name' => 'Inventory',
                                'slug' => 'inventories',
                                'icon' => 'fas fa-th-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 60,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Inventory','slug'=>'inventories-create','icon'=>'fas fa-plus','route_name'=>'inventories.create','route_params'=>null,'url'=>null, 'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Inventory','slug'=>'inventories-index','icon'=>'fas fa-list','route_name'=>'inventories.index','route_params'=>null,'url'=>null, 'sort_order' => 20, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Disposed Items','slug'=>'disposes-index','icon'=>'fas fa-list','route_name'=>'disposes.index','route_params'=>null,'url'=>null, 'sort_order' => 30, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Dispatches (Inventory)
                        [
                            'module' => [
                                'name' => 'Dispatches (Inventory)',
                                'slug' => 'inventory-dispatches',
                                'icon' => 'fas fa-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 70,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Dispatches','slug'=>'inventory-dispatches-index','icon'=>'fas fa-list','route_name'=>'inventory_dispatches.index','route_params'=>null,'url'=>null, 'sort_order' => 10, 'is_active' => true, 'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Dispatches','slug'=>'inventory-dispatches-pending','icon'=>'fas fa-clock','route_name'=>'inventory_dispatches.pending','route_params'=>null,'url'=>null, 'sort_order' => 20, 'is_active' => true, 'badge_key'=>'inventory_dispatches_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Dispatches','slug'=>'inventory-dispatches-approved','icon'=>'fas fa-check','route_name'=>'inventory_dispatches.approved','route_params'=>null,'url'=>null, 'sort_order' => 30, 'is_active' => true, 'badge_key'=>'inventory_dispatches_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Dispatches','slug'=>'inventory-dispatches-rejected','icon'=>'fas fa-ban','route_name'=>'inventory_dispatches.rejected','route_params'=>null,'url'=>null, 'sort_order' => 40, 'is_active' => true, 'badge_key'=>'inventory_dispatches_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                            ],
                        ],
                    ],
                ],


                // =========================================================
                // Tyre Management
                // =========================================================
                [
                    'group' => [
                        'name' => 'Tyre Management',
                        'slug' => 'tyre-management',
                        'icon' => 'fas fa-circle',
                        'url' => null,
                        'sort_order' => 150,
                        'is_active' => true,
                        'visibility' => $this->vis([
                            'or' => ['in_stores' => true, 'is_super_admin' => true],
                        ]),
                    ],
                    'modules' => [

                        // Tyre Transfers
                        [
                            'module' => [
                                'name' => 'Tyre Transfers',
                                'slug' => 'tyre-transfers',
                                'icon' => 'fas fa-exchange',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 10,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Transfers','slug'=>'tyre-transfers-index','icon'=>'fas fa-list','route_name'=>'tyre_transfers.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Transfers','slug'=>'tyre-transfers-pending','icon'=>'fas fa-clock','route_name'=>'tyre_transfers.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>'tyre_transfers_pending','visibility'=>$this->vis([])],
                                ['name'=>'Approved Transfers','slug'=>'tyre-transfers-approved','icon'=>'fas fa-check','route_name'=>'tyre_transfers.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'tyre_transfers_approved','visibility'=>$this->vis([])],
                                ['name'=>'Rejected Transfers','slug'=>'tyre-transfers-rejected','icon'=>'fas fa-ban','route_name'=>'tyre_transfers.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'tyre_transfers_rejected','visibility'=>$this->vis([])],
                            ],
                        ],

                        // Products
                        [
                            'module' => [
                                'name' => 'Products',
                                'slug' => 'tyre-products',
                                'icon' => 'fas fa-boxes',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 20,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Product','slug'=>'tyre-products-create','icon'=>'fas fa-plus','route_name'=>'tyre_products.create','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Products','slug'=>'tyre-products-index','icon'=>'fas fa-list','route_name'=>'tyre_products.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Purchase Orders
                        [
                            'module' => [
                                'name' => 'Purchase Orders',
                                'slug' => 'tyre-purchase-orders',
                                'icon' => 'fas fa-hand-holding-usd',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 30,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Orders','slug'=>'tyre-purchases-index','icon'=>'fas fa-list','route_name'=>'tyre_purchases.index','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Orders','slug'=>'tyre-purchases-pending','icon'=>'fas fa-clock','route_name'=>'tyre_purchases.pending','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>'tyre_purchases_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Orders','slug'=>'tyre-purchases-approved','icon'=>'fas fa-check','route_name'=>'tyre_purchases.approved','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'tyre_purchases_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Orders','slug'=>'tyre-purchases-rejected','icon'=>'fas fa-ban','route_name'=>'tyre_purchases.rejected','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'tyre_purchases_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Deleted Orders','slug'=>'tyre-purchases-deleted','icon'=>'fas fa-trash','route_name'=>'tyre_purchases.deleted','route_params'=>null,'url'=>null,'sort_order' => 50, 'is_active' => true, 'badge_key'=>'tyre_purchases_deleted', 'visibility'=>$this->vis(['or'=>['is_management'=>true, 'ws_department_head' => true, 'st_department_head' => true, 'is_super_admin' => true]])],
                            ],
                        ],

                        // GRV (Tyres)
                        [
                            'module' => [
                                'name' => 'GRV (Tyres)',
                                'slug' => 'tyres-grv',
                                'icon' => 'fas fa-th-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 40,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Tyre GRVs','slug'=>'goods-receiveds-tyres','icon'=>'fas fa-list','route_name'=>'goods_receiveds.tyres','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Tyres
                        [
                            'module' => [
                                'name' => 'Tyres',
                                'slug' => 'tyres',
                                'icon' => 'fas fa-th-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 50,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Tyre','slug'=>'tyres-create','icon'=>'fas fa-plus','route_name'=>'tyres.create','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Tyres','slug'=>'tyres-index','icon'=>'fas fa-list','route_name'=>'tyres.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Tyre Assignments','slug'=>'tyre-assignments-index','icon'=>'fas fa-list','route_name'=>'tyre_assignments.index','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Disposed Items','slug'=>'disposes-index-tyres','icon'=>'fas fa-list','route_name'=>'disposes.index','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                            ],
                        ],

                        // Retreads
                        [
                            'module' => [
                                'name' => 'Retreads',
                                'slug' => 'retreads',
                                'icon' => 'fas fa-th-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 60,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Create Retread','slug'=>'retreads-create','icon'=>'fas fa-plus','route_name'=>'retreads.create','route_params'=>null,'url'=>null,'sort_order' => 10,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Manage Retread','slug'=>'retreads-index','icon'=>'fas fa-list','route_name'=>'retreads.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Retreads','slug'=>'retreads-pending','icon'=>'fas fa-clock','route_name'=>'retreads.pending','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'retreads_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Retreads','slug'=>'retreads-approved','icon'=>'fas fa-check','route_name'=>'retreads.approved','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'retreads_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Retreads','slug'=>'retreads-rejected','icon'=>'fas fa-ban','route_name'=>'retreads.rejected','route_params'=>null,'url'=>null,'sort_order' => 50,'is_active' => true,'badge_key'=>'retreads_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'ws_department_head'=>true,'st_department_head'=>true,'is_super_admin'=>true]])],
                            ],
                        ],

                        // Dispatches (Tyres)
                        [
                            'module' => [
                                'name' => 'Dispatches (Tyres)',
                                'slug' => 'tyre-dispatches',
                                'icon' => 'fas fa-list',
                                'route_name' => null,
                                'route_params' => null,
                                'url' => null,
                                'sort_order' => 70,
                                'is_active' => true,
                                'badge_key' => null,
                                'visibility' => $this->vis([]),
                            ],
                            'sub_modules' => [
                                ['name'=>'Manage Dispatches','slug'=>'tyre-dispatches-index','icon'=>'fas fa-list','route_name'=>'tyre_dispatches.index','route_params'=>null,'url'=>null,'sort_order' => 20,'is_active' => true,'badge_key'=>null,'visibility'=>$this->vis([])],
                                ['name'=>'Pending Dispatches','slug'=>'tyre-dispatches-pending','icon'=>'fas fa-clock','route_name'=>'tyre_dispatches.pending','route_params'=>null,'url'=>null,'sort_order' => 30,'is_active' => true,'badge_key'=>'tyre_dispatches_pending','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Approved Dispatches','slug'=>'tyre-dispatches-approved','icon'=>'fas fa-check','route_name'=>'tyre_dispatches.approved','route_params'=>null,'url'=>null,'sort_order' => 40,'is_active' => true,'badge_key'=>'tyre_dispatches_approved','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                                ['name'=>'Rejected Dispatches','slug'=>'tyre-dispatches-rejected','icon'=>'fas fa-ban','route_name'=>'tyre_dispatches.rejected','route_params'=>null,'url'=>null,'sort_order' => 50,'is_active' => true,'badge_key'=>'tyre_dispatches_rejected','visibility'=>$this->vis(['or'=>['is_management'=>true,'is_admin'=>true,'is_super_admin'=>true]])],
                            ],
                        ],
                    ],
                ],

                // =========================================================
                // BUSINESS SETTINGS
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Business Settings',
                        'slug'       => 'business-settings',
                        'icon'       => null,
                        'sort_order' => 160,
                        'is_active'  => true,
                        'url'        => null,
                        'visibility' => $this->vis([
                            'any_roles' => ['SuperAdmin','Management'],
                            'any_ranks' => ['Directors'],
                        ]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'Company Profile',
                                'slug'       => 'company-profile',
                                'icon'       => 'fas fa-cog',
                                'route_name' => 'company-profile',
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_roles'=>['SuperAdmin','Management'],'any_ranks'=>['Directors']]),
                                'route_params' => ['company' => '{company_id}'],
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Create new business',
                                'slug'       => 'create-new-business',
                                'icon'       => 'fas fa-plus-circle',
                                'route_name' => 'companies.index',
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_roles'=>['SuperAdmin','Management'],'any_ranks'=>['Directors']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],

                // =========================================================
                // PROFILE SETTINGS
                // =========================================================
                [
                    'group' => [
                        'name'       => 'Profile Settings',
                        'slug'       => 'profile-settings',
                        'icon'       => null,
                        'sort_order' => 170,
                        'is_active'  => true,
                        'url'        => null,
                        'visibility' => $this->vis([]),
                    ],
                    'modules' => [
                        [
                            'module' => [
                                'name'       => 'My Profile',
                                'slug'       => 'my-profile',
                                'icon'       => 'fas fa-user',
                                'route_name' => 'profile',
                                'url'        => null,
                                'sort_order' => 10,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => ['user' => '{user_id}'],
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Audits',
                                'slug'       => 'audits',
                                'icon'       => 'fas fa-history',
                                'route_name' => 'audits.all',
                                'url'        => null,
                                'sort_order' => 20,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis(['any_roles'=>['SuperAdmin']]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                        [
                            'module' => [
                                'name'       => 'Logout',
                                'slug'       => 'logout',
                                'icon'       => 'fas fa-sign-out-alt',
                                'route_name' => 'logout',
                                'url'        => null,
                                'sort_order' => 30,
                                'is_active'  => true,
                                'badge_key'  => null,
                                'visibility' => $this->vis([]),
                                'route_params' => null,
                            ],
                            'sub_modules' => [],
                        ],
                    ],
                ],
            ];

            // ---------------------------------------------------------
    // Seed everything (index-driven sort_order, overridable)
    // ---------------------------------------------------------
    foreach ($groups as $gIndex => $g) {

        // support both shapes: $g['group'] can be array OR string
        $groupData = is_array($g['group'])
            ? $g['group']
            : ['name' => $g['group']];

        $group = $this->upsertGroup($groupData, $gIndex);

        foreach (($g['modules'] ?? []) as $mIndex => $m) {

            // support both shapes: $m['module'] can be array OR string
            $moduleData = isset($m['module'])
                ? (is_array($m['module']) ? $m['module'] : ['name' => $m['module']])
                : (is_array($m) ? $m : ['name' => $m]);

            $module = $this->upsertModule($group->id, $moduleData, $mIndex);

            foreach (($m['sub_modules'] ?? []) as $sIndex => $sm) {

                $subModuleData = is_array($sm) ? $sm : ['name' => $sm];

                $this->upsertSubModule($module->id, $subModuleData, $sIndex);
            }
        }
    }

       });
    }

    // =============================================================
    // Helpers
    // =============================================================

    private function vis(array $rules): array
    {
        return $rules;
    }

    /**
     * @param int $fallbackSort If sort_order not provided, use index.
     */
    private function upsertGroup(array $data, int $indexSort = 0): ModuleGroup
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        return ModuleGroup::updateOrCreate(
            ['slug' => $slug],
            [
                'name'       => $data['name'],
                'icon'       => $data['icon'] ?? null,
                'sort_order' => $indexSort, // ✅ index default
                'is_active'  => $data['is_active'] ?? true,
                'visibility' => $data['visibility'] ?? null,
            ]
        );
    }

    private function upsertModule(int $moduleGroupId, array $data, int $indexSort = 0): Module
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        return Module::updateOrCreate(
            ['module_group_id' => $moduleGroupId, 'slug' => $slug],
            [
                'name'         => $data['name'],
                'icon'         => $data['icon'] ?? null,
                'route_name'   => $data['route_name'] ?? null,
                'url'          => $data['url'] ?? null,
                'sort_order'   => $indexSort, // ✅ index default
                'is_active'    => $data['is_active'] ?? true,
                'badge_key'    => $data['badge_key'] ?? null,
                'visibility'   => $data['visibility'] ?? null,
                // If you have route_params column in modules table (json), keep it; otherwise remove this line
                'route_params' => $data['route_params'] ?? null,
            ]
        );
    }

    private function upsertSubModule(int $moduleId, array $data, int $indexSort = 0): SubModule
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        return SubModule::updateOrCreate(
            ['module_id' => $moduleId, 'slug' => $slug],
            [
                'name'         => $data['name'],
                'icon'         => $data['icon'] ?? null,
                'route_name'   => $data['route_name'] ?? null,
                'url'          => $data['url'] ?? null,
                'sort_order'   => $indexSort, // ✅ index default
                'is_active'    => $data['is_active'] ?? true,
                'badge_key'    => $data['badge_key'] ?? null,
                'visibility'   => $data['visibility'] ?? null,
                // If you have route_params column in sub_modules table (json), keep it; otherwise remove this line
                'route_params' => $data['route_params'] ?? null,
            ]
        );
    }
}