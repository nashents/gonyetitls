<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ModuleGroup;
use App\Models\Module;
use App\Models\Submodule;

class MenuRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $menu = [

            // =========================
            // Main Category
            // =========================
            [
                'group' => 'Main Category',
                'modules' => [
                    [
                        'name' => 'Dashboard',
                        'icon' => 'fas fa-tachometer-alt',
                        'route_name' => 'dashboard.index',
                    ],
                    [
                        'name' => 'Companies',
                        'icon' => 'fas fa-building',
                        'submodules' => [
                            ['name' => 'Manage Companies', 'icon' => 'fas fa-list', 'route_name' => 'companies.index'],
                        ],
                    ],
                    [
                        'name' => 'Reminders',
                        'icon' => 'fas fa-bell',
                        'route_name' => 'reminders.index',
                    ],
                ],
            ],

            // =========================
            // Human Resource
            // =========================
            [
                'group' => 'Human Resource',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Allowances', 'icon' => 'fas fa-list', 'route_name' => 'allowances.index'],
                            ['name' => 'Branches', 'icon' => 'fas fa-list', 'route_name' => 'branches.index'],
                            ['name' => 'Departments', 'icon' => 'fas fa-list', 'route_name' => 'departments.index'],
                            ['name' => 'Deductions', 'icon' => 'fas fa-list', 'route_name' => 'deductions.index'],
                            ['name' => 'Earnings', 'icon' => 'fas fa-list', 'route_name' => 'earnings.index'],
                            ['name' => 'Grades', 'icon' => 'fas fa-list', 'route_name' => 'grades.index'],
                            ['name' => 'Job Titles', 'icon' => 'fas fa-list', 'route_name' => 'job_titles.index'],
                            ['name' => 'Qualifications', 'icon' => 'fas fa-list', 'route_name' => 'qualifications.index'],
                            ['name' => 'Leave Types', 'icon' => 'fas fa-list', 'route_name' => 'leave_types.index'],
                        ],
                    ],
                    [
                        'name' => 'Employees',
                        'icon' => 'fas fa-users',
                        'submodules' => [
                            ['name' => 'Create Employee', 'icon' => 'fas fa-plus', 'route_name' => 'employees.create'],
                            ['name' => 'Manage Employees', 'icon' => 'fas fa-list', 'route_name' => 'employees.index'],
                            ['name' => 'Manage Leave Days', 'icon' => 'fas fa-list', 'route_name' => 'employees.leaves.index'],
                            ['name' => 'Archived Employees', 'icon' => 'fas fa-archive', 'route_name' => 'employees.archived'],
                            ['name' => 'Deleted Employees', 'icon' => 'fas fa-trash', 'route_name' => 'employees.deleted'],
                        ],
                    ],
                    [
                        'name' => 'Head of Departments',
                        'icon' => 'fas fa-user-plus',
                        'route_name' => 'department_heads.index',
                    ],
                    [
                        'name' => 'Drivers',
                        'icon' => 'fas fa-users',
                        'submodules' => [
                            ['name' => 'Create Driver', 'icon' => 'fas fa-plus', 'route_name' => 'drivers.create'],
                            ['name' => 'Manage Drivers', 'icon' => 'fas fa-list', 'route_name' => 'drivers.index'],
                            ['name' => 'Archived Employees', 'icon' => 'fas fa-archive', 'route_name' => 'drivers.archived'],
                        ],
                    ],
                    [
                        'name' => 'Leave Management',
                        'icon' => 'fas fa-calendar',
                        'submodules' => [
                            ['name' => 'Apply for leave', 'icon' => 'fas fa-plus', 'route_name' => 'leaves.index'],
                            ['name' => 'My Team', 'icon' => 'fas fa-users', 'route_name' => 'leaves.myteam'],
                            ['name' => 'Manage Applications', 'icon' => 'fas fa-list', 'route_name' => 'leaves.manage'],
                            ['name' => 'Pending Applications', 'icon' => 'fas fa-clock', 'route_name' => 'leaves.pending', 'badge_key' => 'leavesPendingCount'],
                            ['name' => 'Approved Applications', 'icon' => 'fas fa-check', 'route_name' => 'leaves.approved', 'badge_key' => 'leavesApprovedCount'],
                            ['name' => 'Rejected Applications', 'icon' => 'fas fa-ban', 'route_name' => 'leaves.rejected', 'badge_key' => 'leavesRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Inbox',
                        'icon' => 'fas fa-envelope',
                        'route_name' => 'emails.index',
                    ],
                ],
            ],

            // =========================
            // Salaries & Payroll
            // =========================
            [
                'group' => 'Salaries & Payroll',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Allowances', 'icon' => 'fas fa-list', 'route_name' => 'allowances.index'],
                            ['name' => 'Deductions', 'icon' => 'fas fa-list', 'route_name' => 'deductions.index'],
                            ['name' => 'Earnings', 'icon' => 'fas fa-list', 'route_name' => 'earnings.index'],
                            ['name' => 'Loan Type', 'icon' => 'fas fa-list', 'route_name' => 'loan_types.index'],
                            ['name' => 'Tax Table', 'icon' => 'fas fa-list', 'route_name' => 'tax_brackets.index'],
                        ],
                    ],
                    [
                        'name' => 'My Payslip',
                        'icon' => 'fas fa-file',
                        'route_name' => 'payslips.index',
                    ],
                    [
                        'name' => 'Loans',
                        'icon' => 'fas fa-credit-card',
                        'submodules' => [
                            ['name' => 'My Applications', 'icon' => 'fas fa-arrow-right', 'route_name' => 'loans.myloans'],
                            ['name' => 'Manage Loans', 'icon' => 'fas fa-list', 'route_name' => 'loans.index'],
                            ['name' => 'Pending Loans', 'icon' => 'fas fa-clock', 'route_name' => 'loans.pending', 'badge_key' => 'loansPendingCount'],
                            ['name' => 'Approved Loans', 'icon' => 'fas fa-check', 'route_name' => 'loans.approved', 'badge_key' => 'loansApprovedCount'],
                            ['name' => 'Rejected Loans', 'icon' => 'fas fa-ban', 'route_name' => 'loans.rejected', 'badge_key' => 'loansRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Salaries',
                        'icon' => 'fas fa-donate',
                        'submodules' => [
                            ['name' => 'Create Salary', 'icon' => 'fas fa-plus', 'route_name' => 'salaries.create'],
                            ['name' => 'Manage Salaries', 'icon' => 'fas fa-list', 'route_name' => 'salaries.index'],
                        ],
                    ],
                    [
                        'name' => 'Payroll',
                        'icon' => 'fas fa-file',
                        'submodules' => [
                            ['name' => 'Manage Payrolls', 'icon' => 'fas fa-list', 'route_name' => 'payrolls.index'],
                            ['name' => 'Pending Payrolls', 'icon' => 'fas fa-clock', 'route_name' => 'payrolls.pending', 'badge_key' => 'payrollsPendingCount'],
                            ['name' => 'Approved Payrolls', 'icon' => 'fas fa-check', 'route_name' => 'payrolls.approved', 'badge_key' => 'payrollsApprovedCount'],
                            ['name' => 'Rejected Payrolls', 'icon' => 'fas fa-ban', 'route_name' => 'payrolls.rejected', 'badge_key' => 'payrollsRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Sales & Payments
            // =========================
            [
                'group' => 'Sales & Payments',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Currencies', 'icon' => 'fas fa-money-bill-alt', 'route_name' => 'currencies.index'],
                            ['name' => 'Payment Methods', 'icon' => 'fas fa-list', 'route_name' => 'payment_methods.index'],
                        ],
                    ],
                    [
                        'name' => 'Quotations',
                        'icon' => 'fas fa-file-invoice',
                        'submodules' => [
                            ['name' => 'Create Quotation', 'icon' => 'fas fa-plus', 'route_name' => 'quotations.create'],
                            ['name' => 'Manage Quotations', 'icon' => 'fas fa-list', 'route_name' => 'quotations.index'],
                        ],
                    ],
                    [
                        'name' => 'Invoices',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'submodules' => [
                            ['name' => 'Create Invoice', 'icon' => 'fas fa-plus', 'route_name' => 'invoices.create'],
                            ['name' => 'Manage Invoices', 'icon' => 'fas fa-list', 'route_name' => 'invoices.index'],
                            ['name' => 'Pending Invoices', 'icon' => 'fas fa-clock', 'route_name' => 'invoices.pending', 'badge_key' => 'invoicesPendingCount'],
                            ['name' => 'Approved Invoices', 'icon' => 'fas fa-check', 'route_name' => 'invoices.approved', 'badge_key' => 'invoicesApprovedCount'],
                            ['name' => 'Rejected Invoices', 'icon' => 'fas fa-ban', 'route_name' => 'invoices.rejected', 'badge_key' => 'invoicesRejectedCount'],
                            ['name' => 'Deleted Invoices', 'icon' => 'fas fa-trash', 'route_name' => 'invoices.deleted', 'badge_key' => 'invoicesDeletedCount'],
                        ],
                    ],
                    [
                        'name' => 'Customer Statements',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'submodules' => [
                            ['name' => 'Manage Statements', 'icon' => 'fas fa-list', 'route_name' => 'customer_statements.index'],
                        ],
                    ],
                    [
                        'name' => 'Credit Notes',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'submodules' => [
                            ['name' => 'Create', 'icon' => 'fas fa-plus', 'route_name' => 'credit_notes.create'],
                            ['name' => 'Manage C Notes', 'icon' => 'fas fa-list', 'route_name' => 'credit_notes.index'],
                            ['name' => 'Pending C Notes', 'icon' => 'fas fa-clock', 'route_name' => 'credit_notes.pending', 'badge_key' => 'credit_notesPendingCount'],
                            ['name' => 'Approved C Notes', 'icon' => 'fas fa-check', 'route_name' => 'credit_notes.approved', 'badge_key' => 'credit_notesApprovedCount'],
                            ['name' => 'Rejected C Notes', 'icon' => 'fas fa-ban', 'route_name' => 'credit_notes.rejected', 'badge_key' => 'credit_notesRejectedCount'],
                            // sidebar had route('credit_notes.rejected') for deleted (likely typo) — keep as-is from your snippet:
                            ['name' => 'Deleted C Notes', 'icon' => 'fas fa-trash', 'route_name' => 'credit_notes.rejected', 'badge_key' => 'credit_notesDeletedCount'],
                        ],
                    ],
                    [
                        'name' => 'Payments',
                        'icon' => 'fas fa-credit-card',
                        'submodules' => [
                            ['name' => 'Manage Payments', 'icon' => 'fas fa-list', 'route_name' => 'payments.index'],
                            ['name' => 'Manage Receipts', 'icon' => 'fas fa-list', 'route_name' => 'receipts.index'],
                        ],
                    ],
                    [
                        'name' => 'Products & Services',
                        'icon' => 'fas fa-boxes',
                        'submodules' => [
                            [
                                'name' => 'Manage P & S',
                                'icon' => 'fas fa-list',
                                'route_name' => 'product_services.all',
                                'route_params' => ['category' => 'invoices'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Customers',
                        'icon' => 'fas fa-user-friends',
                        'route_name' => 'customers.index',
                    ],
                    [
                        'name' => 'Accounts Receivable',
                        'icon' => 'fas fa-list',
                        'route_name' => 'accounts.receivable',
                    ],
                ],
            ],

            // =========================
            // Purchases
            // =========================
            [
                'group' => 'Purchases',
                'modules' => [
                    [
                        'name' => 'Bills',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Create Bill', 'icon' => 'fas fa-plus', 'route_name' => 'bills.create'],
                            ['name' => 'Manage Bills', 'icon' => 'fas fa-list', 'route_name' => 'bills.index'],
                            ['name' => 'Pending Bills', 'icon' => 'fas fa-clock', 'route_name' => 'bills.pending', 'badge_key' => 'billsPendingCount'],
                            ['name' => 'Approved Bills', 'icon' => 'fas fa-check', 'route_name' => 'bills.approved', 'badge_key' => 'billsApprovedCount'],
                            ['name' => 'Rejected Bills', 'icon' => 'fas fa-ban', 'route_name' => 'bills.rejected', 'badge_key' => 'billsRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Vendor Statements',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'submodules' => [
                            ['name' => 'Manage Statements', 'icon' => 'fas fa-list', 'route_name' => 'vendor_statements.index'],
                        ],
                    ],
                    [
                        'name' => 'Products & Services',
                        'icon' => 'fas fa-boxes',
                        'submodules' => [
                            [
                                'name' => 'Manage P & S',
                                'icon' => 'fas fa-list',
                                'route_name' => 'product_services.all',
                                'route_params' => ['category' => 'bills'],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Vendors',
                        'icon' => 'fas fa-user-friends',
                        'route_name' => 'vendors.index',
                    ],
                    [
                        'name' => 'Accounts Payable',
                        'icon' => 'fas fa-list',
                        'route_name' => 'accounts.payable',
                    ],
                    [
                        'name' => 'Requisitions',
                        'icon' => 'fas fa-hand-holding-usd',
                        'submodules' => [
                            ['name' => 'Manage Requisitions', 'icon' => 'fas fa-list', 'route_name' => 'requisitions.index'],
                            ['name' => 'Pending Requisitions', 'icon' => 'fas fa-clock', 'route_name' => 'requisitions.pending', 'badge_key' => 'requisitionsPendingCount'],
                            ['name' => 'Approved Requisitions', 'icon' => 'fas fa-check', 'route_name' => 'requisitions.approved', 'badge_key' => 'requisitionsApprovedCount'],
                            ['name' => 'Rejected Requisitions', 'icon' => 'fas fa-ban', 'route_name' => 'requisitions.rejected', 'badge_key' => 'requisitionsRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Accounting
            // =========================
            [
                'group' => 'Accounting',
                'modules' => [
                    [
                        'name' => 'Transactions',
                        'icon' => 'fas fa-money-check',
                        'route_name' => 'transactions.index',
                    ],
                    [
                        'name' => 'Charts of Accounts',
                        'icon' => 'fas fa-balance-scale',
                        'submodules' => [
                            ['name' => 'Manage Accounts', 'icon' => 'fas fa-list', 'route_name' => 'accounts.index'],
                            ['name' => 'Manage Sales Taxes', 'icon' => 'fas fa-list', 'route_name' => 'accounts.tax'],
                        ],
                    ],
                    [
                        'name' => 'Bank Accounts',
                        'icon' => 'fas fa-bank',
                        'route_name' => 'bank_accounts.index',
                    ],
                    [
                        'name' => 'Currency Exchange Rates',
                        'icon' => 'fas fa-exchange',
                        'route_name' => 'exchange_rates.index',
                    ],
                ],
            ],

            // =========================
            // Asset Management
            // =========================
            [
                'group' => 'Asset Management',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Manage Categories', 'icon' => 'fas fa-list', 'route_name' => 'categories.index'],
                            ['name' => 'Manage Attributes', 'icon' => 'fas fa-list', 'route_name' => 'attributes.index'],
                            ['name' => 'Manage Brands', 'icon' => 'fas fa-list', 'route_name' => 'brands.index'],
                        ],
                    ],
                    [
                        'name' => 'Products',
                        'icon' => 'fas fa-boxes',
                        'submodules' => [
                            ['name' => 'Create Product', 'icon' => 'fas fa-plus', 'route_name' => 'products.create'],
                            ['name' => 'Manage Products', 'icon' => 'fas fa-list', 'route_name' => 'products.index'],
                        ],
                    ],
                    [
                        'name' => 'Purchase Orders',
                        'icon' => 'fas fa-hand-holding-usd',
                        'submodules' => [
                            ['name' => 'Manage Orders', 'icon' => 'fas fa-list', 'route_name' => 'purchases.index'],
                            ['name' => 'Pending Orders', 'icon' => 'fas fa-clock', 'route_name' => 'purchases.pending', 'badge_key' => 'asset_purchasesPendingCount'],
                            ['name' => 'Approved Orders', 'icon' => 'fas fa-check', 'route_name' => 'purchases.approved', 'badge_key' => 'asset_purchasesApprovedCount'],
                            ['name' => 'Rejected Orders', 'icon' => 'fas fa-ban', 'route_name' => 'purchases.rejected', 'badge_key' => 'asset_purchasesRejectedCount'],
                            ['name' => 'Deleted Orders', 'icon' => 'fas fa-trash', 'route_name' => 'purchases.deleted', 'badge_key' => 'asset_purchasesDeletedCount'],
                        ],
                    ],
                    [
                        'name' => 'GRV (Assets)',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Manage Assets GRVs', 'icon' => 'fas fa-list', 'route_name' => 'goods_receiveds.assets'],
                        ],
                    ],
                    [
                        'name' => 'Assets',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Create Asset', 'icon' => 'fas fa-plus', 'route_name' => 'assets.create'],
                            ['name' => 'Manage Assets', 'icon' => 'fas fa-list', 'route_name' => 'assets.index'],
                        ],
                    ],
                    [
                        'name' => 'Dispatches (Assets)',
                        'icon' => 'fas fa-list',
                        'submodules' => [
                            ['name' => 'Manage Dispatches', 'icon' => 'fas fa-list', 'route_name' => 'asset_dispatches.index'],
                            ['name' => 'Pending Dispatches', 'icon' => 'fas fa-clock', 'route_name' => 'asset_dispatches.pending', 'badge_key' => 'asset_dispatchesPendingCount'],
                            ['name' => 'Approved Dispatches', 'icon' => 'fas fa-check', 'route_name' => 'asset_dispatches.approved', 'badge_key' => 'asset_dispatchesApprovedCount'],
                            ['name' => 'Rejected Dispatches', 'icon' => 'fas fa-ban', 'route_name' => 'asset_dispatches.rejected', 'badge_key' => 'asset_dispatchesRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // SHEQ
            // =========================
            [
                'group' => 'SHEQ',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Cause Categories', 'icon' => 'fas fa-list', 'route_name' => 'loss_categories.index'],
                            ['name' => 'Cause Groups', 'icon' => 'fas fa-list', 'route_name' => 'loss_groups.index'],
                            ['name' => 'Loss Causes', 'icon' => 'fas fa-list', 'route_name' => 'losses.index'],
                        ],
                    ],
                    [
                        'name' => 'Incidents',
                        'icon' => 'fas fa-exclamation-triangle',
                        'submodules' => [
                            ['name' => 'Create Incidents', 'icon' => 'fas fa-plus', 'route_name' => 'incidents.create'],
                            ['name' => 'Manage Incidents', 'icon' => 'fas fa-list', 'route_name' => 'incidents.index'],
                        ],
                    ],
                    [
                        'name' => 'Age Pyramid',
                        'icon' => 'fas fa-hourglass',
                        'submodules' => [
                            ['name' => 'Customers', 'icon' => 'fas fa-list', 'route_name' => 'customers.age'],
                            ['name' => 'Drivers', 'icon' => 'fas fa-list', 'route_name' => 'drivers.age'],
                            ['name' => 'Employees', 'icon' => 'fas fa-list', 'route_name' => 'employees.age'],
                            ['name' => 'Horses', 'icon' => 'fas fa-list', 'route_name' => 'horses.age'],
                            ['name' => 'Trailers', 'icon' => 'fas fa-list', 'route_name' => 'trailers.age'],
                            ['name' => 'Vehicles', 'icon' => 'fas fa-list', 'route_name' => 'vehicles.age'],
                            ['name' => 'Vendors', 'icon' => 'fas fa-list', 'route_name' => 'vendors.age'],
                        ],
                    ],
                    [
                        'name' => 'Compliance',
                        'icon' => 'fas fa-check',
                        'submodules' => [
                            ['name' => 'Driver - Route Compliance', 'icon' => 'fas fa-list', 'route_name' => 'compliances.index'],
                        ],
                    ],
                    [
                        'name' => 'Training Workshops',
                        'icon' => 'fas fa-school',
                        'submodules' => [
                            ['name' => 'What to train?', 'icon' => 'fas fa-list', 'route_name' => 'training_items.index'],
                            ['name' => 'Who to train?', 'icon' => 'fas fa-list', 'route_name' => 'training_departments.index'],
                            ['name' => 'Who needs training?', 'icon' => 'fas fa-list', 'route_name' => 'training_requirements.index'],
                            ['name' => 'Training Plan', 'icon' => 'fas fa-list', 'route_name' => 'training_plans.index'],
                            ['name' => 'Training Program', 'icon' => 'fas fa-list', 'route_name' => 'trainings.index'],
                        ],
                    ],
                    [
                        'name' => 'Documents',
                        'icon' => 'fas fa-file',
                        'submodules' => [
                            [
                                'name' => 'Manage Documents',
                                'icon' => 'fas fa-list',
                                'route_name' => 'documents.all',
                                // dynamic department id placeholder
                                'route_params' => ['id' => '{hseq_department_id}', 'category' => 'department'],
                            ],
                        ],
                    ],
                ],
            ],

            // =========================
            // General Access (Security)
            // =========================
            [
                'group' => 'General Access',
                'modules' => [
                    [
                        'name' => 'Gatepass',
                        'icon' => 'fas fa-door-open',
                        'submodules' => [
                            ['name' => 'Manage Gatepasses', 'icon' => 'fas fa-list', 'route_name' => 'gate_passes.index'],
                            [
                                'name' => 'Pending Gatepasses',
                                'icon' => 'fas fa-clock',
                                'route_name' => 'gate_passes.pending',
                                'route_params' => ['department' => 'security'],
                                'badge_key' => 'gate_passesPendingCount',
                            ],
                            [
                                'name' => 'Approved Gatepasses',
                                'icon' => 'fas fa-check',
                                'route_name' => 'gate_passes.approved',
                                'route_params' => ['department' => 'security'],
                                'badge_key' => 'gate_passesApprovedCount',
                            ],
                            [
                                'name' => 'Rejected Gatepasses',
                                'icon' => 'fas fa-ban',
                                'route_name' => 'gate_passes.rejected',
                                'route_params' => ['department' => 'security'],
                                'badge_key' => 'gate_passesRejectedCount',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Groups',
                        'icon' => 'fas fa-users',
                        'submodules' => [
                            ['name' => 'Manage Groups', 'icon' => 'fas fa-list', 'route_name' => 'groups.index'],
                        ],
                    ],
                    [
                        'name' => 'Visitors',
                        'icon' => 'fas fa-user-friends',
                        'submodules' => [
                            ['name' => 'Manage Visitors', 'icon' => 'fas fa-list', 'route_name' => 'visitors.index'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Fleet Management
            // =========================
            [
                'group' => 'Fleet Management',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Fleet Clusters', 'icon' => 'fas fa-list', 'route_name' => 'clusters.index'],
                            ['name' => 'Horse Groups', 'icon' => 'fas fa-list', 'route_name' => 'horse_groups.index'],
                            ['name' => 'Horse Makes', 'icon' => 'fas fa-list', 'route_name' => 'horse_makes.index'],
                            ['name' => 'Horse Types', 'icon' => 'fas fa-list', 'route_name' => 'horse_types.index'],
                            ['name' => 'Trailer Groups', 'icon' => 'fas fa-list', 'route_name' => 'trailer_groups.index'],
                            ['name' => 'Trailer Types', 'icon' => 'fas fa-list', 'route_name' => 'trailer_types.index'],
                            ['name' => 'Vehicle Groups', 'icon' => 'fas fa-list', 'route_name' => 'vehicle_groups.index'],
                            ['name' => 'Vehicle Makes', 'icon' => 'fas fa-list', 'route_name' => 'vehicle_makes.index'],
                            ['name' => 'Vehicle Types', 'icon' => 'fas fa-list', 'route_name' => 'vehicle_types.index'],
                            // Nested in your blade: Fleet Inspections master setup
                            ['name' => 'Checklists', 'icon' => 'fas fa-list', 'route_name' => 'checklist_categories.index'],
                            ['name' => 'Checklist Items Groups', 'icon' => 'fas fa-list', 'route_name' => 'checklist_sub_categories.index'],
                            ['name' => 'Checklist Items', 'icon' => 'fas fa-list', 'route_name' => 'checklist_items.index'],
                        ],
                    ],
                    [
                        'name' => 'Horses',
                        'icon' => 'fas fa-truck',
                        'submodules' => [
                            ['name' => 'Create Horse', 'icon' => 'fas fa-plus', 'route_name' => 'horses.create'],
                            ['name' => 'Manage Horses', 'icon' => 'fas fa-list', 'route_name' => 'horses.index'],
                            ['name' => 'Archived Horses', 'icon' => 'fas fa-archive', 'route_name' => 'horses.archived'],
                        ],
                    ],
                    [
                        'name' => 'Trailers',
                        'icon' => 'fas fa-trailer',
                        'submodules' => [
                            ['name' => 'Manage Trailers', 'icon' => 'fas fa-list', 'route_name' => 'trailers.index'],
                            ['name' => 'Trailer Links', 'icon' => 'fas fa-list', 'route_name' => 'trailer_links.index'],
                            ['name' => 'Archived Trailers', 'icon' => 'fas fa-archive', 'route_name' => 'trailers.archived'],
                        ],
                    ],
                    [
                        'name' => 'Vehicles',
                        'icon' => 'fas fa-car',
                        'submodules' => [
                            ['name' => 'Create Vehicle', 'icon' => 'fas fa-plus', 'route_name' => 'vehicles.create'],
                            ['name' => 'Manage Vehicles', 'icon' => 'fas fa-list', 'route_name' => 'vehicles.index'],
                            ['name' => 'Archived Vehicles', 'icon' => 'fas fa-archive', 'route_name' => 'vehicles.archived'],
                        ],
                    ],
                    [
                        'name' => 'Assignments',
                        'icon' => 'fas fa-user-plus',
                        'submodules' => [
                            ['name' => 'Driver - Horse', 'icon' => 'fas fa-plus', 'route_name' => 'assignments.index'],
                            ['name' => 'Horse - Trailer', 'icon' => 'fas fa-plus', 'route_name' => 'trailer_assignments.index'],
                            ['name' => 'Employee - Vehicle', 'icon' => 'fas fa-plus', 'route_name' => 'vehicle_assignments.index'],
                        ],
                    ],
                    [
                        'name' => 'Fleet Inspections',
                        'icon' => 'fas fa-search',
                        'submodules' => [
                            ['name' => 'Manage Inspections', 'icon' => 'fas fa-tasks', 'route_name' => 'checklists.index'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Fuel Management
            // =========================
            [
                'group' => 'Fuel Management',
                'modules' => [
                    [
                        'name' => 'Fueling Stations',
                        'icon' => null,
                        'submodules' => [
                            ['name' => 'Manage Stations', 'icon' => 'fas fa-list', 'route_name' => 'containers.index'],
                            ['name' => 'Fuel Transfers', 'icon' => 'fas fa-list', 'route_name' => 'transfers.fuel'],
                        ],
                    ],
                    [
                        'name' => 'Fuel Stations TopUps',
                        'icon' => null,
                        'submodules' => [
                            ['name' => 'Fuel Top Ups', 'icon' => 'fas fa-list', 'route_name' => 'top_ups.index'],
                            ['name' => 'Pending Top Ups', 'icon' => 'fas fa-clock', 'route_name' => 'top_ups.pending', 'badge_key' => 'top_upsPendingCount'],
                            ['name' => 'Approved Top Ups', 'icon' => 'fas fa-check', 'route_name' => 'top_ups.approved', 'badge_key' => 'top_upsApprovedCount'],
                            ['name' => 'Rejected Top Ups', 'icon' => 'fas fa-ban', 'route_name' => 'top_ups.rejected', 'badge_key' => 'top_upsRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Fuel Orders',
                        'icon' => null,
                        'submodules' => [
                            ['name' => 'Manage Fuel Orders', 'icon' => 'fas fa-list', 'route_name' => 'fuels.index'],
                            ['name' => 'Pending Fuel Orders', 'icon' => 'fas fa-clock', 'route_name' => 'fuels.pending', 'badge_key' => 'fuelsPendingCount'],
                            ['name' => 'Approved Fuel Orders', 'icon' => 'fas fa-check', 'route_name' => 'fuels.approved', 'badge_key' => 'fuelsApprovedCount'],
                            ['name' => 'Rejected Fuel Orders', 'icon' => 'fas fa-ban', 'route_name' => 'fuels.rejected', 'badge_key' => 'fuelsRejectedCount'],
                            ['name' => 'Deleted Fuel Orders', 'icon' => 'fas fa-trash', 'route_name' => 'fuels.deleted', 'badge_key' => 'fuelsDelectedCount'], // typo kept from your code
                        ],
                    ],
                    [
                        'name' => 'Fuel Allocations',
                        'icon' => null,
                        'submodules' => [
                            [
                                'name' => 'My Allocation',
                                'icon' => 'fas fa-arrow-right',
                                'route_name' => 'allocations.myallocations',
                                'route_params' => ['employee' => '{employee_id}'],
                                'badge_key' => 'myAllocationCount',
                            ],
                            ['name' => 'Manage Allocation', 'icon' => 'fas fa-list', 'route_name' => 'allocations.index'],
                        ],
                    ],
                    [
                        'name' => 'Fuel Requisitions',
                        'icon' => null,
                        'submodules' => [
                            [
                                'name' => 'My Requests',
                                'icon' => 'fas fa-arrow-right',
                                'route_name' => 'fuel_requests.myrequests',
                                'route_params' => ['employee' => '{employee_id}'],
                            ],
                            ['name' => 'Pending Requests', 'icon' => 'fas fa-clock', 'route_name' => 'fuel_requests.pending', 'badge_key' => 'fuelRequesitionPendingCount'],
                            ['name' => 'Approved Requests', 'icon' => 'fas fa-check', 'route_name' => 'fuel_requests.approved', 'badge_key' => 'fuelRequesitionApprovedCount'],
                            ['name' => 'Rejected Requests', 'icon' => 'fas fa-ban', 'route_name' => 'fuel_requests.rejected', 'badge_key' => 'fuelRequesitionRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Trip Management
            // =========================
            [
                'group' => 'Trip Management',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Agents', 'icon' => 'fas fa-list', 'route_name' => 'agents.index'],
                            ['name' => 'Borders', 'icon' => 'fas fa-bars', 'route_name' => 'borders.index'],
                            ['name' => 'Brokers', 'icon' => 'fas fa-list', 'route_name' => 'brokers.index'],
                            ['name' => 'Cargos', 'icon' => 'fas fa-truck-loading', 'route_name' => 'cargos.index'],
                            ['name' => 'Clearing Agents', 'icon' => 'fas fa-building', 'route_name' => 'clearing_agents.index'],
                            ['name' => 'Countries', 'icon' => 'fas fa-globe-africa', 'route_name' => 'countries.index'],
                            ['name' => 'Consignees', 'icon' => 'fas fa-users', 'route_name' => 'consignees.index'],
                            ['name' => 'Corridors', 'icon' => 'fas fa-road', 'route_name' => 'corridors.index'],
                            ['name' => 'Deductions', 'icon' => 'fas fa-list', 'route_name' => 'deductions.index'],
                            ['name' => 'Destinations', 'icon' => 'fas fa-map-pin', 'route_name' => 'destinations.index'],
                            ['name' => 'Expenses', 'icon' => 'fas fa-list', 'route_name' => 'expenses.index'],
                            ['name' => 'Loading Points', 'icon' => 'fas fa-map-marker', 'route_name' => 'loading_points.index'],
                            ['name' => 'Offloading Points', 'icon' => 'fas fa-map-marker', 'route_name' => 'offloading_points.index'],
                            ['name' => 'Provinces', 'icon' => 'fas fa-globe-africa', 'route_name' => 'provinces.index'],
                            ['name' => 'Rehandling Jobs', 'icon' => 'fas fa-list', 'route_name' => 'works.index'],
                            ['name' => 'Road Routes', 'icon' => 'fas fa-road', 'route_name' => 'routes.index'],
                            ['name' => 'Teams', 'icon' => 'fas fa-users', 'route_name' => 'teams.index'],
                            ['name' => 'Trip Rates', 'icon' => 'fas fa-list', 'route_name' => 'rates.index'],
                            ['name' => 'Trip Types', 'icon' => 'fas fa-road', 'route_name' => 'trip_types.index'],
                            ['name' => 'Truck Stops', 'icon' => 'fas fa-stop', 'route_name' => 'truck_stops.index'],
                            ['name' => 'Worksites', 'icon' => 'fas fa-map-marker', 'route_name' => 'locations.index'],
                        ],
                    ],
                    [
                        'name' => 'Log Book',
                        'icon' => 'fas fa-book',
                        'route_name' => 'logs.index',
                    ],
                    [
                        'name' => 'Transporters',
                        'icon' => 'fas fa-truck',
                        'submodules' => [
                            ['name' => 'Manage Transporters', 'icon' => 'fas fa-list', 'route_name' => 'transporters.index'],
                            ['name' => 'Pending Transporters', 'icon' => 'fas fa-clock', 'route_name' => 'transporters.pending', 'badge_key' => 'transportersPendingCount'],
                            ['name' => 'Approved Transporters', 'icon' => 'fas fa-check', 'route_name' => 'transporters.approved', 'badge_key' => 'transportersApprovedCount'],
                            ['name' => 'Rejected Transporters', 'icon' => 'fas fa-ban', 'route_name' => 'transporters.rejected', 'badge_key' => 'transportersRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Shifts',
                        'icon' => 'fas fa-clock',
                        'submodules' => [
                            ['name' => 'Manage Shifts', 'icon' => 'fas fa-list', 'route_name' => 'shifts.index'],
                            ['name' => 'Shifts Reports', 'icon' => 'fas fa-line-chart', 'route_name' => 'shifts.reports'],
                        ],
                    ],
                    [
                        'name' => 'Trips',
                        'icon' => 'fas fa-road',
                        'submodules' => [
                            ['name' => 'Create Trip', 'icon' => 'fas fa-plus', 'route_name' => 'trips.create'],
                            ['name' => 'Manage Trips', 'icon' => 'fas fa-list', 'route_name' => 'trips.index'],
                            ['name' => 'Pending Trips', 'icon' => 'fas fa-clock', 'route_name' => 'trips.pending', 'badge_key' => 'tripsPendingCount'],
                            ['name' => 'Approved Trips', 'icon' => 'fas fa-check', 'route_name' => 'trips.approved', 'badge_key' => 'tripsApprovedCount'],
                            ['name' => 'Rejected Trips', 'icon' => 'fas fa-ban', 'route_name' => 'trips.rejected', 'badge_key' => 'tripsRejectedCount'],
                            ['name' => 'Deleted Trips', 'icon' => 'fas fa-trash', 'route_name' => 'trips.deleted', 'badge_key' => 'tripsDelectedCount'],
                            ['name' => 'Tracking Groups', 'icon' => 'fas fa-list', 'route_name' => 'trip_groups.index'],
                        ],
                    ],
                    [
                        'name' => 'Gatepass',
                        'icon' => 'fas fa-door-open',
                        'submodules' => [
                            [
                                'name' => 'Pending Gatepasses',
                                'icon' => 'fas fa-clock',
                                'route_name' => 'gate_passes.pending',
                                'route_params' => ['department' => 'logistics'],
                                'badge_key' => 'logistics_gate_passesPendingCount',
                            ],
                            [
                                'name' => 'Approved Gatepasses',
                                'icon' => 'fas fa-check',
                                'route_name' => 'gate_passes.approved',
                                'route_params' => ['department' => 'logistics'],
                                'badge_key' => 'logistics_gate_passesApprovedCount',
                            ],
                            [
                                'name' => 'Rejected Gatepasses',
                                'icon' => 'fas fa-ban',
                                'route_name' => 'gate_passes.rejected',
                                'route_params' => ['department' => 'logistics'],
                                'badge_key' => 'logistics_gate_passesRejectedCount',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Recoveries',
                        'icon' => 'fas fa-hand-holding-usd',
                        'submodules' => [
                            ['name' => 'Create Recovery', 'icon' => 'fas fa-plus', 'route_name' => 'recoveries.create'],
                            ['name' => 'Manage Recoveries', 'icon' => 'fas fa-list', 'route_name' => 'recoveries.index'],
                            ['name' => 'Pending Recoveries', 'icon' => 'fas fa-clock', 'route_name' => 'recoveries.pending', 'badge_key' => 'recoveriesPendingCount'],
                            ['name' => 'Approved Recoveries', 'icon' => 'fas fa-check', 'route_name' => 'recoveries.approved', 'badge_key' => 'recoveriesApprovedCount'],
                            ['name' => 'Rejected Recoveries', 'icon' => 'fas fa-ban', 'route_name' => 'recoveries.rejected', 'badge_key' => 'recoveriesRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Workshop Management
            // =========================
            [
                'group' => 'Workshop Management',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Job Types', 'icon' => 'fas fa-list', 'route_name' => 'service_types.index'],
                            ['name' => 'Inspection Item Groups', 'icon' => 'fas fa-list', 'route_name' => 'inspection_groups.index'],
                            ['name' => 'Inspection Items', 'icon' => 'fas fa-list', 'route_name' => 'inspection_types.index'],
                            ['name' => 'Workshop Stations', 'icon' => 'fas fa-list', 'route_name' => 'stations.index'],
                        ],
                    ],
                    [
                        'name' => 'Bookings',
                        'icon' => 'fas fa-tasks',
                        'submodules' => [
                            ['name' => 'Create Booking', 'icon' => 'fas fa-plus', 'route_name' => 'bookings.create'],
                            ['name' => 'Manage Bookings', 'icon' => 'fas fa-list', 'route_name' => 'bookings.index'],
                            ['name' => 'Pending Bookings', 'icon' => 'fas fa-clock', 'route_name' => 'bookings.pending', 'badge_key' => 'bookingsPendingCount'],
                            ['name' => 'Approved Bookings', 'icon' => 'fas fa-check', 'route_name' => 'bookings.approved', 'badge_key' => 'bookingsApprovedCount'],
                            ['name' => 'Rejected Bookings', 'icon' => 'fas fa-ban', 'route_name' => 'bookings.rejected', 'badge_key' => 'bookingsRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Tickets',
                        'icon' => 'fas fa-file-invoice',
                        'submodules' => [
                            ['name' => 'Manage Tickets', 'icon' => 'fas fa-tasks', 'route_name' => 'tickets.index'],
                            [
                                'name' => 'My Tickets',
                                'icon' => 'fas fa-tasks',
                                'route_name' => 'tickets.cards',
                                'route_params' => ['employee' => '{employee_id}'],
                                'badge_key' => 'jobCardsCount',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Ticket Inspections',
                        'icon' => 'fas fa-search',
                        'submodules' => [
                            ['name' => 'Manage Inspections', 'icon' => 'fas fa-tasks', 'route_name' => 'inspections.index'],
                            [
                                'name' => 'My Inspections',
                                'icon' => 'fas fa-tasks',
                                'route_name' => 'inspections.my-inspections',
                                'route_params' => ['employee' => '{employee_id}'],
                                'badge_key' => 'inspectionsCount',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Gatepass',
                        'icon' => 'fas fa-door-open',
                        'submodules' => [
                            [
                                'name' => 'Pending Gatepasses',
                                'icon' => 'fas fa-clock',
                                'route_name' => 'gate_passes.pending',
                                'route_params' => ['department' => 'workshop'],
                                'badge_key' => 'workshop_gate_passesPendingCount',
                            ],
                            [
                                'name' => 'Approved Gatepasses',
                                'icon' => 'fas fa-check',
                                'route_name' => 'gate_passes.approved',
                                'route_params' => ['department' => 'workshop'],
                                'badge_key' => 'workshop_gate_passesApprovedCount',
                            ],
                            [
                                'name' => 'Rejected Gatepasses',
                                'icon' => 'fas fa-ban',
                                'route_name' => 'gate_passes.rejected',
                                'route_params' => ['department' => 'workshop'],
                                'badge_key' => 'workshop_gate_passesRejectedCount',
                            ],
                        ],
                    ],
                ],
            ],

            // =========================
            // Stores & Inventory Management
            // =========================
            [
                'group' => 'Stores & Inventory Management',
                'modules' => [
                    [
                        'name' => 'Master',
                        'icon' => 'fas fa-cog',
                        'submodules' => [
                            ['name' => 'Attributes', 'icon' => 'fas fa-list', 'route_name' => 'attributes.index'],
                            ['name' => 'Bins', 'icon' => 'fas fa-list', 'route_name' => 'bins.index'],
                            ['name' => 'Brands', 'icon' => 'fas fa-list', 'route_name' => 'brands.index'],
                            ['name' => 'Categories', 'icon' => 'fas fa-list', 'route_name' => 'categories.index'],
                            ['name' => 'Racks', 'icon' => 'fas fa-list', 'route_name' => 'racks.index'],
                            ['name' => 'Stores', 'icon' => 'fas fa-list', 'route_name' => 'stores.index'],
                        ],
                    ],
                    [
                        'name' => 'Inventory Transfers',
                        'icon' => 'fas fa-exchange',
                        'submodules' => [
                            ['name' => 'Manage Transfers', 'icon' => 'fas fa-list', 'route_name' => 'inventory_transfers.index'],
                            ['name' => 'Pending Transfers', 'icon' => 'fas fa-clock', 'route_name' => 'inventory_transfers.pending', 'badge_key' => 'inventory_transfersPendingCount'],
                            ['name' => 'Approved Transfers', 'icon' => 'fas fa-check', 'route_name' => 'inventory_transfers.approved', 'badge_key' => 'inventory_transfersApprovedCount'],
                            ['name' => 'Rejected Transfers', 'icon' => 'fas fa-ban', 'route_name' => 'inventory_transfers.rejected', 'badge_key' => 'inventory_transfersRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Products',
                        'icon' => 'fas fa-boxes',
                        'submodules' => [
                            ['name' => 'Create Product', 'icon' => 'fas fa-plus', 'route_name' => 'inventory_products.create'],
                            ['name' => 'Manage Products', 'icon' => 'fas fa-list', 'route_name' => 'inventory_products.index'],
                        ],
                    ],
                    [
                        'name' => 'Purchase Orders',
                        'icon' => 'fas fa-hand-holding-usd',
                        'submodules' => [
                            ['name' => 'Manage Orders', 'icon' => 'fas fa-list', 'route_name' => 'inventory_purchases.index'],
                            ['name' => 'Pending Orders', 'icon' => 'fas fa-clock', 'route_name' => 'inventory_purchases.pending', 'badge_key' => 'inventory_purchasesPendingCount'],
                            ['name' => 'Approved Orders', 'icon' => 'fas fa-check', 'route_name' => 'inventory_purchases.approved', 'badge_key' => 'inventory_purchasesApprovedCount'],
                            ['name' => 'Rejected Orders', 'icon' => 'fas fa-ban', 'route_name' => 'inventory_purchases.rejected', 'badge_key' => 'inventory_purchasesRejectedCount'],
                            ['name' => 'Deleted Orders', 'icon' => 'fas fa-trash', 'route_name' => 'inventory_purchases.deleted', 'badge_key' => 'inventory_purchasesDeletedCount'],
                        ],
                    ],
                    [
                        'name' => 'GRV (Inventory)',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Manage Inventory GRVs', 'icon' => 'fas fa-list', 'route_name' => 'goods_receiveds.index'],
                        ],
                    ],
                    [
                        'name' => 'Inventory',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Create Inventory', 'icon' => 'fas fa-plus', 'route_name' => 'inventories.create'],
                            ['name' => 'Manage Inventory', 'icon' => 'fas fa-list', 'route_name' => 'inventories.index'],
                            ['name' => 'Disposed Items', 'icon' => 'fas fa-list', 'route_name' => 'disposes.index'],
                        ],
                    ],
                    [
                        'name' => 'Dispatches (Inventory)',
                        'icon' => 'fas fa-list',
                        'submodules' => [
                            ['name' => 'Manage Dispatches', 'icon' => 'fas fa-list', 'route_name' => 'inventory_dispatches.index'],
                            ['name' => 'Pending Dispatches', 'icon' => 'fas fa-clock', 'route_name' => 'inventory_dispatches.pending', 'badge_key' => 'inventory_dispatchesPendingCount'],
                            ['name' => 'Approved Dispatches', 'icon' => 'fas fa-check', 'route_name' => 'inventory_dispatches.approved', 'badge_key' => 'inventory_dispatchesApprovedCount'],
                            ['name' => 'Rejected Dispatches', 'icon' => 'fas fa-ban', 'route_name' => 'inventory_dispatches.rejected', 'badge_key' => 'inventory_dispatchesRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Tyre Management
            // =========================
            [
                'group' => 'Tyre Management',
                'modules' => [
                    [
                        'name' => 'Tyre Transfers',
                        'icon' => 'fas fa-exchange',
                        'submodules' => [
                            ['name' => 'Manage Transfers', 'icon' => 'fas fa-list', 'route_name' => 'tyre_transfers.index'],
                            ['name' => 'Pending Transfers', 'icon' => 'fas fa-clock', 'route_name' => 'tyre_transfers.pending', 'badge_key' => 'tyre_transfersPendingCount'],
                            ['name' => 'Approved Transfers', 'icon' => 'fas fa-check', 'route_name' => 'tyre_transfers.approved', 'badge_key' => 'tyre_transfersApprovedCount'],
                            ['name' => 'Rejected Transfers', 'icon' => 'fas fa-ban', 'route_name' => 'tyre_transfers.rejected', 'badge_key' => 'tyre_transfersRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Products',
                        'icon' => 'fas fa-boxes',
                        'submodules' => [
                            ['name' => 'Create Product', 'icon' => 'fas fa-plus', 'route_name' => 'tyre_products.create'],
                            ['name' => 'Manage Products', 'icon' => 'fas fa-list', 'route_name' => 'tyre_products.index'],
                        ],
                    ],
                    [
                        'name' => 'Purchase Orders',
                        'icon' => 'fas fa-hand-holding-usd',
                        'submodules' => [
                            ['name' => 'Manage Orders', 'icon' => 'fas fa-list', 'route_name' => 'tyre_purchases.index'],
                            ['name' => 'Pending Orders', 'icon' => 'fas fa-clock', 'route_name' => 'tyre_purchases.pending', 'badge_key' => 'tyre_purchasesPendingCount'],
                            ['name' => 'Approved Orders', 'icon' => 'fas fa-check', 'route_name' => 'tyre_purchases.approved', 'badge_key' => 'tyre_purchasesApprovedCount'],
                            ['name' => 'Rejected Orders', 'icon' => 'fas fa-ban', 'route_name' => 'tyre_purchases.rejected', 'badge_key' => 'tyre_purchasesRejectedCount'],
                            ['name' => 'Deleted Orders', 'icon' => 'fas fa-trash', 'route_name' => 'tyre_purchases.deleted', 'badge_key' => 'tyre_purchasesDeletedCount'],
                        ],
                    ],
                    [
                        'name' => 'GRV (Tyres)',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Manage Tyre GRVs', 'icon' => 'fas fa-list', 'route_name' => 'goods_receiveds.tyres'],
                        ],
                    ],
                    [
                        'name' => 'Tyres',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Create Tyre', 'icon' => 'fas fa-plus', 'route_name' => 'tyres.create'],
                            ['name' => 'Manage Tyres', 'icon' => 'fas fa-list', 'route_name' => 'tyres.index'],
                            ['name' => 'Tyre Assignments', 'icon' => 'fas fa-list', 'route_name' => 'tyre_assignments.index'],
                            ['name' => 'Disposed Items', 'icon' => 'fas fa-list', 'route_name' => 'disposes.index'],
                        ],
                    ],
                    [
                        'name' => 'Retreads',
                        'icon' => 'fas fa-th-list',
                        'submodules' => [
                            ['name' => 'Create Retread', 'icon' => 'fas fa-plus', 'route_name' => 'retreads.create'],
                            ['name' => 'Manage Retread', 'icon' => 'fas fa-list', 'route_name' => 'retreads.index'],
                            ['name' => 'Pending Retreads', 'icon' => 'fas fa-clock', 'route_name' => 'retreads.pending', 'badge_key' => 'retreadsPendingCount'],
                            ['name' => 'Approved Retreads', 'icon' => 'fas fa-check', 'route_name' => 'retreads.approved', 'badge_key' => 'retreadsApprovedCount'],
                            ['name' => 'Rejected Retreads', 'icon' => 'fas fa-ban', 'route_name' => 'retreads.rejected', 'badge_key' => 'retreadsRejectedCount'],
                        ],
                    ],
                    [
                        'name' => 'Dispatches (Tyres)',
                        'icon' => 'fas fa-list',
                        'submodules' => [
                            ['name' => 'Manage Dispatches', 'icon' => 'fas fa-list', 'route_name' => 'tyre_dispatches.index'],
                            ['name' => 'Pending Dispatches', 'icon' => 'fas fa-clock', 'route_name' => 'tyre_dispatches.pending', 'badge_key' => 'tyre_dispatchesPendingCount'],
                            ['name' => 'Approved Dispatches', 'icon' => 'fas fa-check', 'route_name' => 'tyre_dispatches.approved', 'badge_key' => 'tyre_dispatchesApprovedCount'],
                            ['name' => 'Rejected Dispatches', 'icon' => 'fas fa-ban', 'route_name' => 'tyre_dispatches.rejected', 'badge_key' => 'tyre_dispatchesRejectedCount'],
                        ],
                    ],
                ],
            ],

            // =========================
            // Business Settings (dynamic company list in blade)
            // =========================
            [
                'group' => 'Business Settings',
                'modules' => [
                    [
                        'name' => 'Company Profile',
                        'icon' => 'fas fa-cog',
                        'route_name' => 'company-profile',
                        'route_params' => ['company' => '{company_id}'],
                    ],
                    [
                        'name' => 'Create new business',
                        'icon' => 'fas fa-plus-circle',
                        'route_name' => 'companies.index',
                    ],
                ],
            ],

            // =========================
            // Profile Settings
            // =========================
            [
                'group' => 'Profile Settings',
                'modules' => [
                    [
                        'name' => 'My Profile',
                        'icon' => 'fas fa-user',
                        'route_name' => 'profile',
                        'route_params' => ['user' => '{user_id}'],
                    ],
                    [
                        'name' => 'Audits',
                        'icon' => 'fas fa-history',
                        'route_name' => 'audits.all',
                    ],
                    [
                        'name' => 'Logout',
                        'icon' => 'fas fa-sign-out-alt',
                        'route_name' => 'logout',
                    ],
                ],
            ],
        ];

        foreach ($menu as $gIndex => $g) {
            $groupName = $g['group'];
            $group = ModuleGroup::updateOrCreate(
                ['slug' => Str::slug($groupName)],
                ['name' => $groupName, 'sort_order' => $gIndex, 'is_active' => true]
            );

            foreach ($g['modules'] as $mIndex => $m) {
                $module = Module::updateOrCreate(
                    ['module_group_id' => $group->id, 'slug' => Str::slug($m['name'])],
                    [
                        'name' => $m['name'],
                        'icon' => $m['icon'] ?? null,
                        'route_name' => $m['route_name'] ?? null,
                        'route_params' => $m['route_params'] ?? null,
                        'url' => $m['url'] ?? null,
                        'sort_order' => $mIndex,
                        'is_active' => true,
                        'visibility' => $m['visibility'] ?? null,
                    ]
                );

                foreach (($m['submodules'] ?? []) as $sIndex => $s) {
                    Submodule::updateOrCreate(
                        ['module_id' => $module->id, 'slug' => Str::slug($s['name'])],
                        [
                            'name' => $s['name'],
                            'icon' => $s['icon'] ?? null,
                            'route_name' => $s['route_name'] ?? null,
                            'route_params' => $s['route_params'] ?? null,
                            'url' => $s['url'] ?? null,
                            'sort_order' => $sIndex,
                            'is_active' => true,
                            'badge_key' => $s['badge_key'] ?? null,
                            'visibility' => $s['visibility'] ?? null,
                        ]
                    );
                }
            }
        }
    }
}