@if($companyColor)
    <style>
        .bg-black-300 {
            background-color: {{ $companyColor }};
        }
    </style>
@endif
<div id="sidebar" style="overflow-y: auto; height: 100vh;" class="left-sidebar fixed-sidebar bg-black-300 box-shadow tour-three" >
    <div class="sidebar-content">
        <div class="user-info closed">
            @if ($user)
            <img src="{{asset('images/uploads/'.$user->profile)}}" alt="{{$user->name}} {{$user->surname}}" class="img-circle profile-img" style="width: 90px; height:90px">
            @endif
            <h6 class="title">{{$user ? $user->name  : ""}} {{$user ? $user->surname : ""}}</h6>
            <small class="info">{{$employee ? $employee->post : ""}}</small>
        </div>
        <div class="sidebar-nav">
            <ul class="side-nav color-gray">
                <li class="nav-header"><span class="">Main Category</span></li>
                <li class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}"><a  href="{{route('dashboard.index')}}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span> </a></li>
                @if ($isAdmin || $isSuperAdmin)
                    @if ($is_admin)
                        <li class="has-children {{ request()->routeIs('companies.index') ? 'active' : '' }}">
                            <a href="javascript:void(0)"><i class="fas fa-building"></i> <span>Companies</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav {{ request()->routeIs('companies.index') ? 'show' : '' }}">
                                <li><a href="{{route('companies.index')}}" ><i class="fas fa-list "></i> <span>Manage Companies</span></a></li>
                            </ul>
                        </li>
                    @endif
                @endif
                <li class="{{ request()->routeIs('reminders.index') ? 'active' : '' }}"><a href="{{route('reminders.index')}}" ><i class="fas fa-bell"></i><span> Reminders</span></a></li>
                <li class="nav-header"><span class="">Human Resource</span></li>
                @if ($inHR || $isSuperAdmin)
                    @if (($isAdmin && $inHR) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav"> 
                                <li class="{{ request()->routeIs('allowances.index') ? 'active' : '' }}">
                                    <a href="{{route('allowances.index')}}"><i class="fas fa-list"></i> <span>Allowances</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('branches.index') ? 'active' : '' }}">
                                    <a href="{{route('branches.index')}}"><i class="fas fa-list"></i> <span>Branches</span> </a>
                                </li>
                                @if ($is_admin)
                                    <li class="{{ request()->routeIs('departments.index') ? 'active' : '' }}">
                                        <a href="{{route('departments.index')}}"><i class="fas fa-list"></i> <span>Departments</span> </a>
                                    </li>
                                @endif
                                <li class="{{ request()->routeIs('deductions.index') ? 'active' : '' }}">
                                        <a href="{{route('deductions.index')}}"><i class="fas fa-list"></i> <span>Deductions</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('earnings.index') ? 'active' : '' }}">
                                        <a href="{{route('earnings.index')}}"><i class="fas fa-list"></i> <span>Earnings</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('grades.index') ? 'active' : '' }}">
                                    <a href="{{route('grades.index')}}"><i class="fas fa-list"></i> <span>Grades</span> </a>
                                </li>
                                <li>
                                <li class="{{ request()->routeIs('job_titles.index') ? 'active' : '' }}">
                                    <a href="{{route('job_titles.index')}}"><i class="fas fa-list"></i> <span>Job Titles</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('qualifications.index') ? 'active' : '' }}">
                                    <a href="{{route('qualifications.index')}}"><i class="fas fa-list"></i> <span>Qualifications</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('leave_types.index') ? 'active' : '' }}">
                                    <a href="{{route('leave_types.index')}}"><i class="fas fa-list"></i> <span>Leave Types</span> </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="has-children {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-users"></i> <span>Employees</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('employees.create')}}" ><i class="fas fa-plus "></i> <span>Create Employee</span></a></li>
                            <li><a href="{{route('employees.index')}}"><i class="fas fa-list "></i> <span>Manage Employees</span></a></li>
                            <li><a href="{{route('employees.leaves.index')}}"><i class="fas fa-list "></i> <span>Manage Leave Days</span></a></li>
                            <li><a href="{{route('employees.archived')}}" ><i class="fas fa-archive "></i> <span>Archived Employees</span></a></li>
                            <li><a href="{{route('employees.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Employees</span></a></li>
                        </ul>
                    </li>
                    <li class="{{ request()->routeIs('department_heads.index') ? 'active' : '' }}">
                        <a href="{{route('department_heads.index')}}"><i class="fas fa-user-plus"></i> <span>Head of Departments</span> </a>
                    </li>
                @endif
                @if ($inTransport || $inHR || $isSuperAdmin)
                    <li class="has-children {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-users"></i> <span>Drivers</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('drivers.create')}}" ><i class="fas fa-plus "></i> <span>Create Driver</span></a></li>
                            <li><a href="{{route('drivers.index')}}"><i class="fas fa-list "></i> <span>Manage Drivers</span></a></li>
                            <li><a href="{{route('drivers.archived')}}" ><i class="fas fa-archive "></i> <span>Archived Employees</span></a></li>
                        
                        </ul>
                    </li>
                @endif
                <li class="has-children {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                    <a href="javascript:void(0)"><i class="fas fa-calendar"></i> <span>Leave Management</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('leaves.index')}}"><i class="fas fa-plus "></i> <span>Apply for leave</span></a></li>
                        <li><a href="{{route('leaves.myteam')}}"><i class="fas fa-users "></i> <span>My Team</span></a></li>

                        @if (isset($hrdepartment_head) || ($isAdmin && $inHR) || ($isManagement && $inHR) || $isSuperAdmin)
                        <li><a href="{{route('leaves.manage')}}"><i class="fas fa-list "></i> <span>Manage Applications</span></a></li>
                        <li><a href="{{route('leaves.pending')}}"><i class="fas fa-clock "></i> <span>Pending Applications</span>
                            @if ($leavesPendingCount>0)
                            <span class="label label-success ml-5">{{$leavesPendingCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('leaves.approved')}}"><i class="fas fa-check "></i> <span>Approved Applications</span>
                            @if ($leavesApprovedCount>0)
                            <span class="label label-success ml-5">{{$leavesApprovedCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('leaves.rejected')}}"><i class="fas fa-ban "></i> <span>Rejected Applications</span>
                            @if ($leavesRejectedCount>0)
                            <span class="label label-success ml-5">{{$leavesRejectedCount}}</span>
                            @endif
                        </a></li>
                        @endif
                    </ul>
                </li>
                {{-- <li class="{{ request()->routeIs('notices.index') ? 'active' : '' }}"><a href="{{route('notices.index')}}"><i class="fas fa-bullhorn"></i> <span>Notice</span> </a></li> --}}
                <li class="{{ request()->routeIs('emails.index') ? 'active' : '' }}"><a href="{{route('emails.index')}}"><i class="fas fa-envelope"></i> <span> Inbox</span> </a></li> 
                <li class="nav-header"><span class="">Salaries & Payroll</span></li>
                @if (($isAdmin && $inHR) || $isSuperAdmin)
                    <li class="has-children">
                        <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="{{ request()->routeIs('allowances.index') ? 'active' : '' }}"><a href="{{route('allowances.index')}}"><i class="fas fa-list"></i> <span>Allowances</span></a></li>
                            <li class="{{ request()->routeIs('deductions.index') ? 'active' : '' }}"><a href="{{route('deductions.index')}}"><i class="fas fa-list"></i> <span>Deductions</span></a></li>
                            <li class="{{ request()->routeIs('earnings.index') ? 'active' : '' }}"><a href="{{route('earnings.index')}}"><i class="fas fa-list"></i> <span>Earnings</span></a></li>
                            <li class="{{ request()->routeIs('loan_types.index') ? 'active' : '' }}"><a href="{{route('loan_types.index')}}"><i class="fas fa-list"></i> <span>Loan Type</span></a></li>
                            @if ($is_admin)
                                <li class="{{ request()->routeIs('tax_brackets.index') ? 'active' : '' }}"><a href="{{route('tax_brackets.index')}}"><i class="fas fa-list"></i> <span>Tax Table</span></a></li>
                            @endif
                            <li class="{{ request()->routeIs('payroll-config.index') ? 'active' : '' }}">
                                <a href="{{route('payroll-config.index')}}"><i class="fas fa-sliders-h"></i> <span>Payroll Config</span></a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li class="{{ request()->routeIs('payslips.index') ? 'active' : '' }}"><a href="{{route('payslips.index')}}"><i class="fas fa-file"></i> <span>My Payslip</span> </a></li>
                <li class="has-children {{ request()->routeIs('loans.*') ? 'active' : '' }}" >
                    <a href="javascript:void(0)"><i class="fas fa-credit-card"></i> <span>Loans</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('loans.myloans')}}"><i class="fas fa-arrow-right "></i> <span>My Applications</span></a></li>
                        @if (isset($fndepartment_head) || ($isManagement && $inHR) || ($isManagement && $inFinance) || $isSuperAdmin)
                        <li><a href="{{route('loans.index')}}"><i class="fas fa-list "></i> <span>Manage Loans</span></a></li>
                        <li><a href="{{route('loans.pending')}}"><i class="fas fa-clock "></i> <span>Pending Loans</span>
                            @if ($loansPendingCount>0)
                            <span class="label label-success ml-5">{{$loansPendingCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('loans.approved')}}"><i class="fas fa-check "></i> <span>Approved Loans</span>
                            @if ($loansApprovedCount>0)
                            <span class="label label-success ml-5">{{$loansApprovedCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('loans.rejected')}}"><i class="fas fa-ban "></i> <span>Rejected Loans</span>
                            @if ($loansRejectedCount>0)
                            <span class="label label-success ml-5">{{$loansRejectedCount}}</span>
                            @endif
                        </a></li>
                        @endif
                    </ul>
                </li>
                @if (($isAdmin && $inHR) || $isSuperAdmin)
                    <li class="has-children {{ request()->routeIs('salaries.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-donate"></i> <span>Salaries</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('salaries.create')}}" ><i class="fas fa-plus "></i> <span>Create Salary</span></a></li>
                            <li><a href="{{route('salaries.index')}}"><i class="fas fa-list "></i> <span>Manage Salaries</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('payrolls.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file"></i> <span>Payroll</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('payrolls.index')}}"><i class="fas fa-list "></i> <span>Manage Payrolls</span></a></li>
                            @if (isset($hrdepartment_head) || (($isManagement || $isAdmin) && $inHR) || $isSuperAdmin)
                            <li><a href="{{route('payrolls.pending')}}"><i class="fas fa-clock "></i> <span>Pending Payrolls</span>
                                @if ($payrollsPendingCount>0)
                                <span class="label label-success ml-5">{{$payrollsPendingCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('payrolls.approved')}}"><i class="fas fa-check "></i> <span>Approved Payrolls</span>
                                @if ($payrollsApprovedCount>0)
                                <span class="label label-success ml-5">{{$payrollsApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('payrolls.rejected')}}"><i class="fas fa-ban "></i> <span>Rejected Payrolls</span>
                                @if ($payrollsRejectedCount>0)
                                <span class="label label-success ml-5">{{$payrollsRejectedCount}}</span>
                                @endif
                            </a></li>
                            @endif
                        </ul>
                    </li>
                    {{-- Payroll Runs (lifecycle-driven) --}}
                    <li class="has-children {{ request()->routeIs('payroll-runs.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-tasks"></i> <span>Payroll Runs</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="{{ request()->routeIs('payroll-runs.index') ? 'active' : '' }}">
                                <a href="{{route('payroll-runs.index')}}"><i class="fas fa-list"></i> <span>All Runs</span></a>
                            </li>
                        </ul>
                    </li>
                @endif
                {{-- Salary Advances — visible to all employees (own) and HR/Finance --}}
                <li class="has-children {{ request()->routeIs('salary-advances.*') ? 'active' : '' }}">
                    <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Salary Advances</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li class="{{ request()->routeIs('salary-advances.index') ? 'active' : '' }}">
                            <a href="{{route('salary-advances.index')}}"><i class="fas fa-list"></i> <span>My Advances</span></a>
                        </li>
                    </ul>
                </li>
                @if ($inFinance || $isSuperAdmin)
                    <li class="nav-header">
                        <span class="">Sales & Payments</span>
                    </li>
                    @if (($isAdmin && $inFinance) && ($isAdmin && $inHR) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav"> 
                                <li class="{{ request()->routeIs('currencies.index') ? 'active' : '' }}"><a href="{{route('currencies.index')}}"><i class="fas fa-money-bill-alt"></i> <span>Currencies</span> </a></li> 
                                <li class="{{ request()->routeIs('payment_methods.index') ? 'active' : '' }}"><a href="{{route('payment_methods.index')}}"><i class="fas fa-list"></i> <span>Payment Methods</span> </a></li> 
                            
                            </ul>
                        </li>
                    @endif
                    <li class="has-children {{ request()->routeIs('quotations.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice"></i> <span>Quotations</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('quotations.create')}}" ><i class="fas fa-plus "></i> <span>Create Quotation</span></a></li>
                            <li><a href="{{route('quotations.index')}}" ><i class="fas fa-list "></i> <span>Manage Quotations</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('invoices.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice-dollar"></i> <span>Invoices</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('invoices.create')}}" ><i class="fas fa-plus "></i> <span>Create Invoice</span></a></li>
                            <li><a href="{{route('invoices.index')}}" ><i class="fas fa-list "></i> <span>Manage Invoices</span></a></li>
                            @if (isset($fndepartment_head)  || (($isManagement || $isAdmin) && $inFinance) || $isSuperAdmin)
                            <li><a href="{{route('invoices.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Invoices</span>
                                @if ($invoicesPendingCount>0)
                                <span class="label label-success ml-5">{{$invoicesPendingCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('invoices.approved')}}" ><i class="fas fa-check "></i> <span>Approved Invoices</span>
                                @if ($invoicesApprovedCount>0)
                                <span class="label label-success ml-5">{{$invoicesApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('invoices.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Invoices</span>
                                @if ($invoicesRejectedCount>0)
                                <span class="label label-success ml-5">{{$invoicesRejectedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('invoices.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Invoices</span>
                                @if ($invoicesDeletedCount>0)
                                <span class="label label-success ml-5">{{$invoicesDeletedCount}}</span>
                                @endif
                            </a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('customer_statements.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice-dollar"></i> <span>Customer Statements</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{ route('customer_statements.index') }}" ><i class="fas fa-list "></i> <span>Manage Statements</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('credit_notes.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice-dollar"></i> <span>Credit Notes</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('credit_notes.create')}}" ><i class="fas fa-plus "></i> <span>Create</span></a></li>
                            <li><a href="{{route('credit_notes.index')}}" ><i class="fas fa-list "></i> <span>Manage C Notes</span></a></li>
                            @if (isset($fndepartment_head)  || ($isManagement && $inFinance) || $isSuperAdmin)
                            <li><a href="{{route('credit_notes.pending')}}" ><i class="fas fa-clock "></i> <span>Pending C Notes</span>
                                @if ($credit_notesPendingCount>0)
                                <span class="label label-success ml-5">{{$credit_notesPendingCount}}</span>
                                @endif 
                            </a></li>
                            <li><a href="{{route('credit_notes.approved')}}" ><i class="fas fa-check "></i> <span>Approved C Notes</span>
                                @if ($credit_notesApprovedCount>0)
                                <span class="label label-success ml-5">{{$credit_notesApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('credit_notes.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected C Notes</span>
                                @if ($credit_notesRejectedCount>0)
                                <span class="label label-success ml-5">{{$credit_notesRejectedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('credit_notes.rejected')}}" ><i class="fas fa-trash "></i> <span>Deleted C Notes</span>
                                @if ($credit_notesDeletedCount>0)
                                <span class="label label-success ml-5">{{$credit_notesDeletedCount}}</span>
                                @endif
                            </a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('payments.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-credit-card"></i> <span>Payments</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('payments.index')}}" ><i class="fas fa-list "></i> <span>Manage Payments</span></a></li>
                            <li class="{{ request()->routeIs('receipts.index') ? 'active' : '' }}"><a href="{{route('receipts.index')}}" ><i class="fas fa-list "></i> <span>Manage Receipts</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('product_services.all',['category' => 'invoices']) ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-boxes"></i> <span>Products & Services</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('product_services.all',['category' => 'invoices'])}}" ><i class="fas fa-list "></i> <span>Manage P & S</span></a></li>
                        </ul>
                    </li>
                    <li class="{{ request()->routeIs('customers.index') ? 'active' : '' }}"><a href="{{route('customers.index')}}" ><i class="fas fa-user-friends"></i> <span>Customers</span></a></li>
                    <li class="{{ request()->routeIs('accounts.receivable') ? 'active' : '' }}"><a href="{{route('accounts.receivable')}}" ><i class="fas fa-list"></i> <span>Accounts Receivable</span></a></li>
                    
                @endif
                <li class="nav-header"><span class="">Purchases</span></li>
                @if ($inFinance || $isSuperAdmin)
                    <li class="has-children {{ request()->routeIs('bills.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>Bills</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('bills.create')}}" ><i class="fas fa-plus "></i> <span>Create Bill</span></a></li>
                            <li><a href="{{route('bills.index')}}"><i class="fas fa-list "></i> <span>Manage Bills</span></a></li>
                            @if (isset($fndepartment_head)  || ($isManagement && $inFinance) || $isSuperAdmin)
                            <li><a href="{{route('bills.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Bills</span>
                                @if ($billsPendingCount>0)
                                <span class="label label-success ml-5">{{$billsPendingCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('bills.approved')}}" ><i class="fas fa-check "></i> <span>Approved Bills</span>
                                @if ($billsApprovedCount>0)
                                <span class="label label-success ml-5">{{$billsApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('bills.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Bills</span>
                                @if ($billsRejectedCount>0)
                                <span class="label label-success ml-5">{{$billsRejectedCount}}</span>
                                @endif
                            </a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('vendor_statements.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice-dollar"></i> <span>Vendor Statements</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{ route('vendor_statements.index') }}" ><i class="fas fa-list "></i> <span>Manage Statements</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('product_services.all',['category' => 'bills']) ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-boxes"></i> <span>Products & Services</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('product_services.all',['category' => 'bills'])}}" ><i class="fas fa-list "></i> <span>Manage P & S</span></a></li>
                        </ul>
                    </li>
                    <li class="{{ request()->routeIs('vendors.index') ? 'active' : '' }}"><a href="{{route('vendors.index')}}"><i class="fas fa-user-friends"></i> <span>Vendors</span></a></li>
                    <li class="{{ request()->routeIs('accounts.payable') ? 'active' : '' }}"><a href="{{route('accounts.payable')}}" ><i class="fas fa-list"></i> <span>Accounts Payable</span></a></li>
                @endif
                <li class="has-children {{ request()->routeIs('requisitions.*') ? 'active' : '' }}" >
                    <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Requisitions</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('requisitions.index')}}" ><i class="fas fa-list "></i> <span>Manage Requisitions</span></a></li>
                        @if ($isManagement  || isset($fndepartment_head)  || $isAdmin || $isSuperAdmin)
                        <li><a href="{{route('requisitions.pending')}}"><i class="fas fa-clock "></i> <span>Pending Requisitions</span>
                            @if ($requisitionsPendingCount>0)
                            <span class="label label-success ml-5">{{$requisitionsPendingCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('requisitions.approved')}}"><i class="fas fa-check "></i> <span>Approved Requisitions</span>
                            @if ($requisitionsApprovedCount>0)
                            <span class="label label-success ml-5">{{$requisitionsApprovedCount}}</span>
                            @endif
                        </a></li>
                        <li><a href="{{route('requisitions.rejected')}}"><i class="fas fa-ban "></i> <span>Rejected Requisitions</span>
                            @if ($requisitionsRejectedCount>0)
                            <span class="label label-success ml-5">{{$requisitionsRejectedCount}}</span>
                            @endif
                        </a></li>
                        @endif
                    </ul>
                </li>
                @if ($inFinance || $isSuperAdmin)
                    <li class="nav-header"><span class="">Accounting</span></li>
                    <li class="{{ request()->routeIs('transactions.index') ? 'active' : '' }}"><a href="{{route('transactions.index')}}"><i class="fas fa-money-check"></i> <span>Transactions</span></a></li>
                    <li class="has-children {{ request()->routeIs('accounts.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-balance-scale"></i> <span>Charts of Accounts</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            {{-- <li><a href="{{route('account_types.index')}}" ><i class="fas fa-list "></i> <span>Account Types</span></a></li> --}}
                            <li><a href="{{route('accounts.index')}}"><i class="fas fa-list "></i> <span> Manage Accounts</span></a></li>
                            @if ($is_admin)
                                <li><a href="{{route('accounts.tax')}}"><i class="fas fa-list "></i> <span> Manage Sales Taxes</span></a></li>
                            @endif
                        </ul>
                    </li>
                    <li  class="{{ request()->routeIs('bank_accounts.index') ? 'active' : '' }}" ><a href="{{route('bank_accounts.index')}}"><i class="fas fa-bank"></i> <span>Bank Accounts</span></a></li>
                    <li  class="{{ request()->routeIs('exchange_rates.index') ? 'active' : '' }}" ><a href="{{route('exchange_rates.index')}}"><i class="fas fa-exchange"></i> <span>Currency Exchange Rates</span></a></li>
                @endif
                @if ($isSuperAdmin  || $inFinance)
                    <li class="nav-header"><span class="">Asset Management</span></li>
                    <li class="has-children">
                        <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('categories.index')}}" ><i class="fas fa-list "></i> <span>Manage Categories</span></a></li>
                            <li><a href="{{route('attributes.index')}}"><i class="fas fa-list "></i> <span>Manage Attributes</span></a></li>
                            <li><a href="{{route('brands.index')}}"><i class="fas fa-list "></i> <span>Manage Brands</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-boxes"></i> <span>Products</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('products.create')}}" ><i class="fas fa-plus "></i> <span>Create Product</span></a></li>
                            <li><a href="{{route('products.index')}}"><i class="fas fa-list "></i> <span>Manage Products</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('purchases.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Purchase Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('purchases.index')}}" ><i class="fas fa-list "></i> <span>Manage Orders</span></a></li>
                            @if (isset($fndepartment_head)  || ($isManagement && $inFinance) || $isSuperAdmin)
                            <li>
                                <a href="{{route('purchases.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Orders</span>
                                    @if ($asset_purchasesPendingCount>0)
                                    <span class="label label-success ml-5">{{$asset_purchasesPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('purchases.approved')}}" ><i class="fas fa-check "></i> <span>Approved Orders</span>
                                    @if ($asset_purchasesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$asset_purchasesApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('purchases.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Orders</span>
                                    @if ($asset_purchasesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$asset_purchasesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('purchases.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Orders</span>
                                    @if ($asset_purchasesDeletedCount>0)
                                    <span class="label label-success ml-5">{{$asset_purchasesDeletedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children" {{ request()->routeIs('goods_receiveds.assets') ? 'active' : '' }}>
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>GRV (Assets)</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('goods_receiveds.assets')}}"><i class="fas fa-list "></i> <span>Manage Assets GRVs</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('assets.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>Assets</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('assets.create')}}" ><i class="fas fa-plus "></i> <span>Create Asset</span></a></li>
                            <li><a href="{{route('assets.index')}}"><i class="fas fa-list "></i> <span>Manage Assets</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('asset_dispatches.*') ? 'active' : '' }}">
                    
                        <a href="javascript:void(0)"><i class="fas fa-list"></i> <span>Dispatches (Assets) </span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('asset_dispatches.index')}}" ><i class="fas fa-list "></i> <span>Manage Dispatches</span></a></li>
                            @if ($isManagement || $isAdmin || $isSuperAdmin)
                                <li>
                                    <a href="{{route('asset_dispatches.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Dispatches</span>
                                        @if ($asset_dispatchesPendingCount>0)
                                        <span class="label label-success ml-5">{{$asset_dispatchesPendingCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('asset_dispatches.approved')}}" ><i class="fas fa-check "></i> <span>Approved Dispatches</span>
                                        @if ($asset_dispatchesApprovedCount>0)
                                        <span class="label label-success ml-5">{{$asset_dispatchesApprovedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('asset_dispatches.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Dispatches</span>
                                        @if ($asset_dispatchesRejectedCount>0)
                                        <span class="label label-success ml-5">{{$asset_dispatchesRejectedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if ($inHSEQ || $isSuperAdmin)
                    <li class="nav-header"><span class="">SHEQ</span></li>
                    @if (($isAdmin && $inHSEQ) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav"> 
                                <li  class="{{ request()->routeIs('loss_categories.index') ? 'active' : '' }}">
                                    <a href="{{route('loss_categories.index')}}"><i class="fas fa-list"></i> <span>Cause Categories</span> </a>
                                </li>
                                <li  class="{{ request()->routeIs('loss_groups.index') ? 'active' : '' }}">
                                    <a href="{{route('loss_groups.index')}}"><i class="fas fa-list"></i> <span>Cause Groups</span> </a>
                                </li>
                                <li>
                                <li  class="{{ request()->routeIs('losses.index') ? 'active' : '' }}">
                                    <a href="{{route('losses.index')}}"><i class="fas fa-list"></i> <span>Loss Causes</span> </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="has-children {{ request()->routeIs('incidents.*') ? 'active' : '' }}"  >
                        <a href="javascript:void(0)"><i class="fas fa-exclamation-triangle"></i> <span>Incidents</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('incidents.create')}}" ><i class="fas fa-plus "></i> <span>Create Incidents</span></a></li>
                            <li><a href="{{route('incidents.index')}}" ><i class="fas fa-list "></i> <span>Manage Incidents</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('*.age') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-hourglass"></i> <span>Age Pyramid</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('customers.age')}}" ><i class="fas fa-list "></i> <span>Customers</span></a></li>
                            <li><a href="{{route('drivers.age')}}" ><i class="fas fa-list "></i> <span>Drivers</span></a></li>
                            <li><a href="{{route('employees.age')}}" ><i class="fas fa-list "></i> <span>Employees</span></a></li>
                            <li><a href="{{route('horses.age')}}" ><i class="fas fa-list "></i> <span>Horses</span></a></li>
                            <li><a href="{{route('trailers.age')}}" ><i class="fas fa-list "></i> <span>Trailers</span></a></li>
                            <li><a href="{{route('vehicles.age')}}" ><i class="fas fa-list "></i> <span>Vehicles</span></a></li>
                            <li><a href="{{route('vendors.age')}}" ><i class="fas fa-list "></i> <span>Vendors</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('compliances.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-check"></i> <span>Compliance</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('compliances.index')}}" ><i class="fas fa-list "></i> <span>Driver - Route Compliance</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children ">
                        <a href="javascript:void(0)"><i class="fas fa-school"></i> <span>Training Workshops</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="{{ request()->routeIs('training_items.index') ? 'active' : '' }}"><a href="{{route('training_items.index')}}" ><i class="fas fa-list "></i> <span>What to train?</span></a></li>
                            <li class="{{ request()->routeIs('training_departments.index') ? 'active' : '' }}"><a href="{{route('training_departments.index')}}" ><i class="fas fa-list "></i> <span>Who to train?</span></a></li>
                            <li class="{{ request()->routeIs('training_requirements.index') ? 'active' : '' }}"><a href="{{route('training_requirements.index')}}" ><i class="fas fa-list "></i> <span>Who needs training?</span></a></li>
                            <li class="{{ request()->routeIs('training_plans.index') ? 'active' : '' }}"><a href="{{route('training_plans.index')}}" ><i class="fas fa-list "></i> <span>Training Plan</span></a></li>
                            <li class="{{ request()->routeIs('trainings.index') ? 'active' : '' }}"><a href="{{route('trainings.index')}}" ><i class="fas fa-list "></i> <span>Training Program</span></a></li>
                        </ul>
                    </li>
                    @if (isset($hseq_department))
                        <li class="has-children {{ request()->routeIs('documents.all',['id' => $hseq_department->id, 'category' => 'department']) ? 'active' : '' }}">
                            <a href="javascript:void(0)"><i class="fas fa-file"></i> <span>Documents</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('documents.all',['id' => $hseq_department->id, 'category' => 'department'])}}" ><i class="fas fa-list "></i> <span>Manage Documents</span></a></li> 
                            </ul>
                        </li>
                    @endif
                @endif
                @if ($inSecurity || $isSuperAdmin)
                    <li class="nav-header">
                        <span class="">General Access</span>
                    </li>
                    <li class="has-children {{ request()->routeIs('gate_passes.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-door-open"></i> <span>Gatepass</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('gate_passes.index')}}" ><i class="fas fa-list "></i> <span>Manage Gatepasses</span></a></li>
                        
                            <li><a href="{{route('gate_passes.pending',['department'=>'security'])}}" ><i class="fas fa-clock "></i> <span>Pending Gatepasses</span>
                                @if ($gate_passesPendingCount>0)
                                <span class="label label-success ml-5">{{$gate_passesPendingCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('gate_passes.approved',['department'=>'security'])}}" ><i class="fas fa-check "></i> <span>Approved Gatepasses</span>
                                @if ($gate_passesApprovedCount>0)
                                <span class="label label-success ml-5">{{$gate_passesApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li>
                                <a href="{{route('gate_passes.rejected',['department'=>'security'])}}" ><i class="fas fa-ban "></i> <span>Rejected Gatepasses</span>
                                    @if ($gate_passesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$gate_passesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('groups.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-users"></i> <span>Groups</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('groups.index')}}" ><i class="fas fa-list "></i> <span>Manage Groups</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('visitors.index') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-user-friends"></i> <span>Visitors</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('visitors.index')}}" ><i class="fas fa-list "></i> <span>Manage Visitors</span></a></li>
                        </ul>
                    </li>
                @endif
                @if ($inTransport || in_array('Workshop', $department_names) || $isSuperAdmin)
                    @if (!$user->driver)
                        <li class="nav-header">
                            <span class="">Fleet Management</span>
                        </li>
                        @if ($isAdmin || $isSuperAdmin)
                            <li class="has-children">
                                <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li class="{{ request()->routeIs('clusters.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('clusters.index')}}"><i class="fas fa-list"></i> <span>Fleet Clusters</span></a></li>
                                    <li class="{{ request()->routeIs('horse_groups.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('horse_groups.index')}}"><i class="fas fa-list"></i> <span>Horse Groups</span></a></li>
                                    <li class="{{ request()->routeIs('horse_makes.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('horse_makes.index')}}"><i class="fas fa-list"></i> <span>Horse Makes</span></a></li>
                                    <li class="{{ request()->routeIs('horse_types.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('horse_types.index')}}"><i class="fas fa-list"></i> <span>Horse Types</span></a></li>
                                    <li class="{{ request()->routeIs('trailer_groups.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('trailer_groups.index')}}"><i class="fas fa-list"></i> <span>Trailer Groups</span></a></li>
                                    <li class="{{ request()->routeIs('trailer_types.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('trailer_types.index')}}"><i class="fas fa-list"></i> <span>Trailer Types</span></a></li>
                                    <li class="{{ request()->routeIs('vehicle_groups.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('vehicle_groups.index')}}"><i class="fas fa-list"></i> <span>Vehicle Groups</span> </a></li>
                                    <li class="{{ request()->routeIs('vehicle_makes.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('vehicle_makes.index')}}"><i class="fas fa-list"></i> <span>Vehicle Makes</span> </a></li>
                                    <li class="{{ request()->routeIs('vehicle_types.index') ? 'active' : '' }}" style="padding-left:10px"><a href="{{route('vehicle_types.index')}}"><i class="fas fa-list"></i> <span>Vehicle Types</span> </a></li>
                                    <li class="has-children " style="padding-left:10px">
                                        <a href="javascript:void(0)"><i class="fas fa-tasks"></i> <span>Fleet Inspections</span> <i class="fas fa-angle-right arrow"></i></a>
                                        <ul class="child-nav">
                                            <li class="{{ request()->routeIs('checklist_categories.index') ? 'active' : '' }}" style="padding-left: 10px"><a href="{{route('checklist_categories.index')}}" ><i class="fas fa-list "></i> <span>Checklists</span></a></li>
                                            <li class="{{ request()->routeIs('checklist_sub_categories.index') ? 'active' : '' }}" style="padding-left: 10px"><a href="{{route('checklist_sub_categories.index')}}"><i class="fas fa-list "></i> <span>Checklist Items Groups</span></a></li>
                                            <li class="{{ request()->routeIs('checklist_items.index') ? 'active' : '' }}" style="padding-left: 10px"><a href="{{route('checklist_items.index')}}"><i class="fas fa-list "></i> <span>Checklist Items</span></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        <li class="has-children {{ request()->routeIs('horses.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><i class="fas fa-truck"></i> <span>Horses</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('horses.create')}}" ><i class="fas fa-plus "></i> <span>Create Horse</span></a></li>
                                <li><a href="{{route('horses.index')}}"><i class="fas fa-list "></i> <span>Manage Horses</span></a></li>
                                <li><a href="{{route('horses.archived')}}" ><i class="fas fa-archive "></i> <span>Archived Horses</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('trailers.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><i class="fas fa-trailer"></i> <span>Trailers</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('trailers.index')}}" ><i class="fas fa-list "></i> <span> Manage Trailers </span></a></li>
                                <li><a href="{{route('trailer_links.index')}}"><i class="fas fa-list "></i> <span>Trailer Links</span></a></li>
                                <li><a href="{{route('trailers.archived')}}" ><i class="fas fa-archive "></i> <span>Archived Trailers</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><i class="fas fa-car"></i> <span>Vehicles</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('vehicles.create')}}" ><i class="fas fa-plus "></i> <span> Create Vehicle </span></a></li>
                                <li><a href="{{route('vehicles.index')}}"><i class="fas fa-list "></i> <span>Manage Vehicles</span></a></li>
                                <li><a href="{{route('vehicles.archived')}}" ><i class="fas fa-archive "></i> <span>Archived Vehicles</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-user-plus"></i> <span>Assignments</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('assignments.index') ? 'active' : '' }}"><a href="{{route('assignments.index')}}" ><i class="fas fa-plus "></i> <span>Driver - Horse </span></a></li>
                                <li class="{{ request()->routeIs('trailer_assignments.index') ? 'active' : '' }}"><a href="{{route('trailer_assignments.index')}}" ><i class="fas fa-plus "></i> <span>Horse - Trailer </span></a></li>
                                <li class="{{ request()->routeIs('vehicle_assignments.index') ? 'active' : '' }}"><a href="{{route('vehicle_assignments.index')}}"><i class="fas fa-plus "></i> <span>Employee - Vehicle</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('checklists.index') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><i class="fas fa-search"></i> <span>Fleet Inspections</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('checklists.index')}}"><i class="fas fa-tasks "></i> <span>Manage Inspections</span></a></li>
                            </ul>
                        </li>
                    @endif
                @endif
                <li class="nav-header"><span class="">Fuel Management</span></li>
                @if ($inTransport || $isSuperAdmin)
                    @if (!$user->driver)
                        <li class="has-children {{ request()->routeIs('containers.index') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><span>Fueling Stations</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('containers.index')}}" ><i class="fas fa-list "></i> <span>Manage Stations</span></a></li>
                                <li><a href="{{route('transfers.fuel')}}" ><i class="fas fa-list "></i> <span>Fuel Transfers</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('top_ups.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><span>Fuel Stations TopUps</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('top_ups.index')}}" ><i class="fas fa-list "></i> <span>Fuel Top Ups</span></a></li>
                                @if (($inTransport && $isAdmin) || $isSuperAdmin)
                                <li>
                                    <a href="{{route('top_ups.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Top Ups</span>
                                        @if ($top_upsPendingCount>0)
                                        <span class="label label-success ml-5">{{$top_upsPendingCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('top_ups.approved')}}" ><i class="fas fa-check "></i> <span>Approved Top Ups</span>
                                        @if ($top_upsApprovedCount>0)
                                        <span class="label label-success ml-5">{{$top_upsApprovedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('top_ups.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Top Ups</span>
                                        @if ($top_upsRejectedCount>0)
                                        <span class="label label-success ml-5">{{$top_upsRejectedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('fuels.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><span>Fuel Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('fuels.index')}}" ><i class="fas fa-list "></i> <span>Manage Fuel Orders</span></a></li>
                                @if (($isManagement && $inTransport) || isset($tldepartment_head)  || $isSuperAdmin)
                                <li><a href="{{route('fuels.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Fuel Orders</span>
                                @if ($fuelsPendingCount>0)
                                <span class="label label-success ml-5">{{$fuelsPendingCount}}</span>
                                @endif
                                </a></li>
                                <li><a href="{{route('fuels.approved')}}" ><i class="fas fa-check "></i> <span>Approved Fuel Orders</span>
                                @if ($fuelsApprovedCount>0)
                                <span class="label label-success ml-5">{{$fuelsApprovedCount}}</span>
                                @endif
                                </a></li>
                                <li><a href="{{route('fuels.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Fuel Orders</span>
                                @if ($fuelsRejectedCount>0)
                                <span class="label label-success ml-5">{{$fuelsRejectedCount}}</span>
                                @endif
                                </a></li>
                                <li><a href="{{route('fuels.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Fuel Orders</span>
                                @if ($fuelsDelectedCount>0)
                                <span class="label label-success ml-5">{{$fuelsDelectedCount}}</span>
                                @endif
                                </a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
                <li class="has-children {{ request()->routeIs('allocations.*') ? 'active' : '' }}" >
                    <a href="javascript:void(0)"><span>Fuel Allocations</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('allocations.myallocations',$employee->id)}}" ><i class="fas fa-arrow-right "></i> <span>My Allocation</span>
                            @if ($myAllocationCount>0)
                            <span class="label label-success ml-5">{{$myAllocationCount}}</span>
                            @endif
                        </a></li>
                        @if (($inTransport && $isAdmin) || (in_array('Transport & Logistcs', $role_names) && in_array('HOD', $rank_names)) || $isSuperAdmin)
                            <li><a href="{{route('allocations.index')}}" ><i class="fas fa-list "></i> <span>Manage Allocation</span></a></li>
                        @endif
                    </ul>
                </li>
                <li class="has-children {{ request()->routeIs('fuel_requests.*') ? 'active' : '' }}" >
                    <a href="javascript:void(0)"><span>Fuel Requisitions</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('fuel_requests.myrequests',$employee->id)}}" ><i class="fas fa-arrow-right "></i> <span>My Requests</span></a></li>
                        @if (($inTransport && $isManagement) || isset($tldepartment_head) || $isSuperAdmin || ($inTransport && $isAdmin))
                            <li>
                                <a href="{{route('fuel_requests.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Requests</span>
                                    @if ($fuelRequesitionPendingCount>0)
                                        <span class="label label-success ml-5">{{$fuelRequesitionPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('fuel_requests.approved')}}" ><i class="fas fa-check "></i> <span>Approved Requests</span>
                                    @if ($fuelRequesitionApprovedCount>0)
                                        <span class="label label-success ml-5">{{$fuelRequesitionApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('fuel_requests.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Requests</span>
                                    @if ($fuelRequesitionRejectedCount>0)
                                        <span class="label label-success ml-5">{{$fuelRequesitionRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @if ($inFinance || $inTransport || $isSuperAdmin)
                    <li class="nav-header"><span class="">Trip Management</span></li>
                    @if (($isAdmin && $inTransport) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('agents.index') ? 'active' : '' }}"><a href="{{route('agents.index')}}"><i class="fas fa-list"></i> <span>Agents</span></a></li>
                                <li class="{{ request()->routeIs('borders.index') ? 'active' : '' }}"><a href="{{route('borders.index')}}" ><i class="fas fa-bars"></i> <span>Borders</span></a></li>
                                <li class="{{ request()->routeIs('brokers.index') ? 'active' : '' }}"><a href="{{route('brokers.index')}}"><i class="fas fa-list"></i> <span>Brokers</span></a></li>  
                                <li class="{{ request()->routeIs('cargos.index') ? 'active' : '' }}"><a href="{{route('cargos.index')}}"><i class="fas fa-truck-loading"></i> <span>Cargos</span> </a></li>
                                <li class="{{ request()->routeIs('clearing_agents.index') ? 'active' : '' }}"><a href="{{route('clearing_agents.index')}}" ><i class="fas fa-building"></i> <span>Clearing Agents</span></a></li>
                                <li class="{{ request()->routeIs('countries.index') ? 'active' : '' }}"><a href="{{route('countries.index')}}"><i class="fas fa-globe-africa"></i> <span>Countries</span> </a></li>
                                <li class="{{ request()->routeIs('consignees.index') ? 'active' : '' }}"><a href="{{route('consignees.index')}}" ><i class="fas fa-users"></i> <span>Consignees</span></a></li>
                                <li class="{{ request()->routeIs('corridors.index') ? 'active' : '' }}"><a href="{{route('corridors.index')}}" ><i class="fas fa-road"></i> <span>Corridors</span></a></li>
                                <li class="{{ request()->routeIs('deductions.index') ? 'active' : '' }}"><a href="{{route('deductions.index')}}" ><i class="fas fa-list "></i> <span>Deductions</span></a></li>
                                <li class="{{ request()->routeIs('destinations.index') ? 'active' : '' }}"><a href="{{route('destinations.index')}}"><i class="fas fa-map-pin"></i> <span>Destinations</span> </a></li>
                                <li class="{{ request()->routeIs('expenses.index') ? 'active' : '' }}"><a href="{{ route('expenses.index') }}"><i class="fas fa-list"></i> <span>Expenses</span> </a></li>
                                <li class="{{ request()->routeIs('loading_points.index') ? 'active' : '' }}"><a href="{{route('loading_points.index')}}" ><i class="fas fa-map-marker"></i> <span>Loading Points</span></a></li>
                                <li class="{{ request()->routeIs('offloading_points.index') ? 'active' : '' }}"><a href="{{route('offloading_points.index')}}" ><i class="fas fa-map-marker "></i> <span>Offloading Points</span></a></li>
                                <li class="{{ request()->routeIs('provinces.index') ? 'active' : '' }}"><a href="{{route('provinces.index')}}"><i class="fas fa-globe-africa"></i> <span>Provinces</span> </a></li>
                                <li class="{{ request()->routeIs('works.index') ? 'active' : '' }}"><a href="{{route('works.index')}}" ><i class="fas fa-list"></i> <span>Rehandling Jobs</span></a></li>
                                <li class="{{ request()->routeIs('routes.index') ? 'active' : '' }}"><a href="{{route('routes.index')}}" ><i class="fas fa-road"></i> <span>Road Routes</span></a></li>
                                @if ($isSuperAdmin)
                                    <li class="{{ request()->routeIs('teams.index') ? 'active' : '' }}"><a href="{{route('teams.index')}}" ><i class="fas fa-users"></i> <span>Teams</span></a></li>
                                @endif
                                @if ($inFinance || $isSuperAdmin)
                                    <li class="{{ request()->routeIs('rates.index') ? 'active' : '' }}"><a href="{{route('rates.index')}}"><i class="fas fa-list"></i> <span>Trip Rates</span></a></li>  
                                @endif
                                <li class="{{ request()->routeIs('trip_types.index') ? 'active' : '' }}"><a href="{{route('trip_types.index')}}"><i class="fas fa-road"></i> <span>Trip Types</span> </a></li>
                                <li class="{{ request()->routeIs('truck_stops.index') ? 'active' : '' }}"><a href="{{route('truck_stops.index')}}" ><i class="fas fa-stop"></i> <span>Truck Stops</span></a></li>
                                <li class="{{ request()->routeIs('locations.index') ? 'active' : '' }}"><a href="{{route('locations.index')}}" ><i class="fas fa-map-marker"></i> <span>Worksites</span></a></li>
                            </ul>
                        </li>
                    @endif
                    @if ($employee->vehicle_assignment || $isSuperAdmin)
                        <li><a href="{{route('logs.index')}}"><i class="fas fa-book"></i> <span>Log Book</span></a></li>
                    @endif
                    @if (!$user->driver)
                        <li class="has-children {{ request()->routeIs('transporters.*') ? 'active' : '' }}">
                            <a href="javascript:void(0)"><i class="fas fa-truck"></i> <span>Transporters</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('transporters.index')}}" ><i class="fas fa-list "></i> <span>Manage Transporters</span></a></li>
                                @if ((($isAdmin || $isManagement) && $inTransport) || isset($tldepartment_head) || $isSuperAdmin)
                                <li><a href="{{route('transporters.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Transporters</span>
                                    @if ($transportersPendingCount>0)
                                    <span class="label label-success ml-5">{{$transportersPendingCount}}</span>
                                    @endif
                                    </a></li>
                                    <li><a href="{{route('transporters.approved')}}" ><i class="fas fa-check "></i> <span>Approved Transporters</span>
                                        @if ($transportersApprovedCount>0)
                                        <span class="label label-success ml-5">{{$transportersApprovedCount}}</span>
                                        @endif
                                    </a></li>
                                    <li><a href="{{route('transporters.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Transporters</span>
                                        @if ($transportersRejectedCount>0)
                                        <span class="label label-success ml-5">{{$transportersRejectedCount}}</span>
                                        @endif
                                </a></li>
                                @endif  
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                            <a href="javascript:void(0)"><i class="fas fa-clock"></i> <span>Shifts</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('shifts.index')}}" ><i class="fas fa-list "></i> <span>Manage Shifts</span></a></li>          
                                <li><a href="{{route('shifts.reports')}}" ><i class="fas fa-line-chart"></i> <span>Shifts Reports</span></a></li>
                            </ul>
                        </li>
                    @endif
                    <li class="has-children {{ request()->routeIs('trips.*') ? 'active' : '' }} " >
                        <a href="javascript:void(0)"><i class="fas fa-road"></i> <span>Trips</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('trips.create')}}" ><i class="fas fa-plus "></i> <span>Create Trip</span></a></li>
                            <li><a href="{{route('trips.index')}}" ><i class="fas fa-list "></i> <span>Manage Trips</span></a></li>
                            @if ($isManagement || isset($tldepartment_head) || $isSuperAdmin)
                                <li>
                                    <a href="{{route('trips.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Trips</span>
                                    @if ($tripsPendingCount>0)
                                        <span class="label label-success ml-5">{{$tripsPendingCount}}</span>
                                    @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('trips.approved')}}" ><i class="fas fa-check "></i> <span>Approved Trips</span>
                                        @if ($tripsApprovedCount>0)
                                            <span class="label label-success ml-5">{{$tripsApprovedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('trips.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Trips</span>
                                        @if ($tripsRejectedCount>0)
                                            <span class="label label-success ml-5">{{$tripsRejectedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('trips.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Trips</span>
                                        @if ($tripsDelectedCount>0)
                                            <span class="label label-success ml-5">{{$tripsDelectedCount}}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            <li class="{{ request()->routeIs('trip_groups.index') ? 'active' : '' }}"><a href="{{route('trip_groups.index')}}" ><i class="fas fa-list"></i> <span>Tracking Groups</span></a></li> 
                        </ul>
                    </li>
                    @if (!$user->driver)
                        <li class="has-children"  >
                            <a href="javascript:void(0)"><i class="fas fa-door-open"></i> <span>Gatepass</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('gate_passes.pending',['department'=>'logistics']) ? 'active' : '' }}"><a href="{{route('gate_passes.pending',['department'=>'logistics'])}}" ><i class="fas fa-clock "></i> <span>Pending Gatepasses</span>
                                    @if ($logistics_gate_passesPendingCount>0)
                                    <span class="label label-success ml-5">{{$logistics_gate_passesPendingCount}}</span>
                                    @endif
                                </a></li>
                                <li class="{{ request()->routeIs('gate_passes.approved',['department'=>'logistics']) ? 'active' : '' }}"><a href="{{route('gate_passes.approved',['department'=>'logistics'])}}" ><i class="fas fa-check "></i> <span>Approved Gatepasses</span>
                                    @if ($logistics_gate_passesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$logistics_gate_passesApprovedCount}}</span>
                                    @endif
                                </a></li>
                                <li class="{{ request()->routeIs('gate_passes.rejected',['department'=>'logistics']) ? 'active' : '' }}"><a href="{{route('gate_passes.rejected',['department'=>'logistics'])}}" ><i class="fas fa-ban "></i> <span>Rejected Gatepasses</span>
                                    @if ($logistics_gate_passesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$logistics_gate_passesRejectedCount}}</span>
                                    @endif
                                </a></li>
                            </ul>
                        </li>
                        <li class="has-children {{ request()->routeIs('recoveries.*') ? 'active' : '' }}" >
                            <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Recoveries</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('recoveries.create')}}" ><i class="fas fa-plus "></i> <span>Create Recovery</span></a></li>
                                <li><a href="{{route('recoveries.index')}}" ><i class="fas fa-list "></i> <span>Manage Recoveries</span></a></li>
                                @if ($isManagement || isset($tldepartment_head) || $isSuperAdmin)
                                    <li>
                                        <a href="{{route('recoveries.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Recoveries</span>
                                            @if ($recoveriesPendingCount>0)
                                                <span class="label label-success ml-5">{{$recoveriesPendingCount}}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{route('recoveries.approved')}}" ><i class="fas fa-check "></i> <span>Approved Recoveries</span>
                                            @if ($recoveriesApprovedCount>0)
                                                <span class="label label-success ml-5">{{$recoveriesApprovedCount}}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{route('recoveries.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Recoveries</span>
                                        @if ($recoveriesRejectedCount>0)
                                            <span class="label label-success ml-5">{{$recoveriesRejectedCount}}</span>
                                        @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif       
                @if ($inFinance || in_array('Workshop', $department_names) || in_array('Stores', $department_names) || $isSuperAdmin)
                    <li class="nav-header"><span class="">Workshop Management</span></li>
                    @if ( isset($wsdepartment_head) || ($isAdmin && in_array('Workshop', $department_names)) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('service_types.index') ? 'active' : '' }}">
                                    <a href="{{route('service_types.index')}}"><i class="fas fa-list"></i> <span>Job Types</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('inspection_groups.index') ? 'active' : '' }}">
                                    <a href="{{route('inspection_groups.index')}}"><i class="fas fa-list"></i> <span> Inspection Item Groups</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('inspection_types.index') ? 'active' : '' }}">
                                    <a href="{{route('inspection_types.index')}}"><i class="fas fa-list"></i> <span>Inspection Items</span> </a>
                                </li>
                                <li class="{{ request()->routeIs('stations.index') ? 'active' : '' }}">
                                    <a href="{{route('stations.index')}}"><i class="fas fa-list"></i> <span>Workshop Stations</span> </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <li class="has-children {{ request()->routeIs('bookings.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-tasks"></i> <span>Bookings</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('bookings.create')}}" ><i class="fas fa-plus "></i> <span>Create Booking</span></a></li>
                            <li><a href="{{route('bookings.index')}}" ><i class="fas fa-list "></i> <span>Manage Bookings</span></a></li>
                            
                            @if ($isManagement || isset($wsdepartment_head) || ($isAdmin && in_array('Workshop', $department_names)) || $isSuperAdmin)
                            <li>
                                <a href="{{route('bookings.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Bookings</span>
                                    @if ($bookingsPendingCount>0)
                                    <span class="label label-success ml-5">{{$bookingsPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('bookings.approved')}}" ><i class="fas fa-check "></i> <span>Approved Bookings</span>
                                    @if ($bookingsApprovedCount>0)
                                    <span class="label label-success ml-5">{{$bookingsApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('bookings.rejected')}}" ><i class="fas fa-ban"></i> <span>Rejected Bookings</span>
                                    @if ($bookingsRejectedCount>0)
                                    <span class="label label-success ml-5">{{$bookingsRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('tickets.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-file-invoice"></i> <span>Tickets</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            @if ( isset($stdepartment_head) || isset($wsdepartment_head) || ($isAdmin && in_array('Workshop', $department_names)) || ($isAdmin && in_array('Stores', $department_names))  || $isSuperAdmin)
                            <li><a href="{{route('tickets.index')}}" ><i class="fas fa-tasks "></i> <span>Manage Tickets</span></a></li>
                            @endif
                            @if (in_array('Workshop', $department_names))
                            <li>
                                <a href="{{route('tickets.cards', $employee->id)}}" ><i class="fas fa-tasks "></i> <span>My Tickets</span>
                                    @if ($jobCardsCount>0)
                                        <span class="label label-success ml-5">{{$jobCardsCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('inspections.index') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-search"></i> <span>Ticket Inspections</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            @if ( isset($stdepartment_head) || isset($wsdepartment_head) || ($isAdmin && in_array('Workshop', $department_names)) || ($isAdmin && in_array('Stores', $department_names))  || $isSuperAdmin)
                                <li><a href="{{route('inspections.index')}}" ><i class="fas fa-tasks "></i> <span>Manage Inspections</span></a></li>
                            @endif
                            @if (in_array('Workshop', $department_names))
                                <li>
                                    <a href="{{route('inspections.my-inspections', $employee->id)}}" ><i class="fas fa-tasks "></i> <span>My Inspections</span>
                                        @if ($inspectionsCount>0)
                                            <span class="label label-success ml-5">{{$inspectionsCount}}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                            
                        </ul>
                    </li>
                    @if ($isAdmin || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-door-open"></i> <span>Gatepass</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('gate_passes.pending',['department'=>'workshop']) ? 'active' : '' }}">
                                    <a href="{{route('gate_passes.pending',['department'=>'workshop'])}}" ><i class="fas fa-clock "></i> <span>Pending Gatepasses</span>
                                        @if ($workshop_gate_passesPendingCount>0)
                                            <span class="label label-success ml-5">{{$workshop_gate_passesPendingCount}}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('gate_passes.approved',['department'=>'workshop']) ? 'active' : '' }}"><a href="{{route('gate_passes.approved',['department'=>'workshop'])}}" ><i class="fas fa-check "></i> <span>Approved Gatepasses</span>
                                    @if ($workshop_gate_passesApprovedCount>0)
                                        <span class="label label-success ml-5">{{$workshop_gate_passesApprovedCount}}</span>
                                    @endif
                                </a></li>
                                <li class="{{ request()->routeIs('gate_passes.rejected',['department'=>'workshop']) ? 'active' : '' }}"><a href="{{route('gate_passes.rejected',['department'=>'workshop'])}}" ><i class="fas fa-ban "></i> <span>Rejected Gatepasses</span>
                                    @if ($workshop_gate_passesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$workshop_gate_passesRejectedCount}}</span>
                                    @endif
                                </a></li>
                            </ul>
                        </li>
                    @endif
                @endif
                @if (in_array('Stores', $department_names) || $isSuperAdmin)
                    <li class="nav-header"><span class="">Stores & Inventory Management</span></li>
                    @if ( isset($stdepartment_head) || ($isAdmin && in_array('Stores', $department_names)) || $isSuperAdmin)
                        <li class="has-children">
                            <a href="javascript:void(0)"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li class="{{ request()->routeIs('attributes.index') ? 'active' : '' }}" ><a href="{{route('attributes.index')}}"><i class="fas fa-list "></i> <span> Attributes</span></a></li>
                                <li class="{{ request()->routeIs('bins.index') ? 'active' : '' }}"><a href="{{route('bins.index')}}"><i class="fas fa-list "></i> <span> Bins</span></a></li>
                                <li class="{{ request()->routeIs('brands.index') ? 'active' : '' }}"><a href="{{route('brands.index')}}"><i class="fas fa-list "></i> <span> Brands</span></a></li>
                                <li class="{{ request()->routeIs('categories.index') ? 'active' : '' }}"><a href="{{route('categories.index')}}" ><i class="fas fa-list "></i> <span> Categories</span></a></li>
                                <li class="{{ request()->routeIs('racks.index') ? 'active' : '' }}"><a href="{{route('racks.index')}}"><i class="fas fa-list "></i> <span> Racks</span></a></li>
                                <li class="{{ request()->routeIs('stores.index') ? 'active' : '' }}"><a href="{{route('stores.index')}}" ><i class="fas fa-list "></i> <span>Stores</span></a></li>
                            </ul>
                        </li>
                    @endif
                    <li class="has-children">
                        <a href="javascript:void(0)"><i class="fas fa-exchange"></i> <span>Inventory Transfers</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="{{ request()->routeIs('inventory_transfers.index') ? 'active' : '' }}"><a href="{{route('inventory_transfers.index')}}" ><i class="fas fa-list "></i> <span>Manage Transfers</span></a></li>
                            <li class="{{ request()->routeIs('inventory_transfers.pending') ? 'active' : '' }}"><a href="{{route('inventory_transfers.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Transfers</span>
                                @if ($inventory_transfersPendingCount>0)
                                    <span class="label label-success ml-5">{{$inventory_transfersPendingCount}}</span>
                                @endif
                            </a></li>
                            <li class="{{ request()->routeIs('inventory_transfers.approved') ? 'active' : '' }}"><a href="{{route('inventory_transfers.approved')}}" ><i class="fas fa-check "></i> <span>Approved Transfers</span>
                                @if ($inventory_transfersApprovedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_transfersApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li class="{{ request()->routeIs('inventory_transfers.rejected') ? 'active' : '' }}"><a href="{{route('inventory_transfers.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Transfers</span>
                                @if ($inventory_transfersRejectedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_transfersRejectedCount}}</span>
                                @endif
                            </a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('inventory_products.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-boxes"></i> <span>Products</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">

                            <li><a href="{{route('inventory_products.create')}}" ><i class="fas fa-plus "></i> <span>Create Product</span></a></li>
                            <li><a href="{{route('inventory_products.index')}}"><i class="fas fa-list "></i> <span>Manage Products</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('inventory_purchases.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Purchase Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('inventory_purchases.index')}}" ><i class="fas fa-list "></i> <span>Manage Orders</span></a></li>
                            @if ($isManagement || isset($wsdepartment_head) || isset($stdepartment_head) || $isSuperAdmin)
                            <li>
                                <a href="{{route('inventory_purchases.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Orders</span>
                                    @if ($inventory_purchasesPendingCount>0)
                                    <span class="label label-success ml-5">{{$inventory_purchasesPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('inventory_purchases.approved')}}" ><i class="fas fa-check "></i> <span>Approved Orders</span>
                                    @if ($inventory_purchasesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_purchasesApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('inventory_purchases.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Orders</span>
                                    @if ($inventory_purchasesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_purchasesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('inventory_purchases.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Orders</span>
                                    @if ($inventory_purchasesDeletedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_purchasesDeletedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children" {{ request()->routeIs('goods_receiveds.index') ? 'active' : '' }}>
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>GRV (Inventory)</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li ><a href="{{route('goods_receiveds.index')}}"><i class="fas fa-list "></i> <span>Manage Inventory GRVs</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children" {{ request()->routeIs('inventories.*') ? 'active' : '' }}>
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>Inventory</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li ><a href="{{route('inventories.create')}}" ><i class="fas fa-plus "></i> <span>Create Inventory</span></a></li>
                            <li ><a href="{{route('inventories.index')}}"><i class="fas fa-list "></i> <span>Manage Inventory</span></a></li>
                            <li > <a href="{{route('disposes.index')}}"><i class="fas fa-list "></i> <span>Disposed Items</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('inventory_dispatches.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-list"></i> <span>Dispatches (Inventory) </span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('inventory_dispatches.index')}}" ><i class="fas fa-list "></i> <span>Manage Dispatches</span></a></li>
                            @if ($isManagement || $isAdmin || $isSuperAdmin)
                            <li>
                                <a href="{{route('inventory_dispatches.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Dispatches</span>
                                    @if ($inventory_dispatchesPendingCount>0)
                                    <span class="label label-success ml-5">{{$inventory_dispatchesPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('inventory_dispatches.approved')}}" ><i class="fas fa-check "></i> <span>Approved Dispatches</span>
                                    @if ($inventory_dispatchesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_dispatchesApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('inventory_dispatches.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Dispatches</span>
                                    @if ($inventory_dispatchesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$inventory_dispatchesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="nav-header">
                        <span class="">Tyre Management</span>
                    </li>
                    <li class="has-children">
                        <a href="javascript:void(0)"><i class="fas fa-exchange"></i> <span>Tyre Transfers</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="{{ request()->routeIs('tyre_transfers.index') ? 'active' : '' }}"><a href="{{route('tyre_transfers.index')}}" ><i class="fas fa-list "></i> <span>Manage Transfers</span></a></li>
                            <li class="{{ request()->routeIs('tyre_transfers.pending') ? 'active' : '' }}"><a href="{{route('tyre_transfers.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Transfers</span>
                                @if ($tyre_transfersPendingCount>0)
                                    <span class="label label-success ml-5">{{$tyre_transfersPendingCount}}</span>
                                @endif
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tyre_transfers.approved') ? 'active' : '' }}"><a href="{{route('tyre_transfers.approved')}}" ><i class="fas fa-check "></i> <span>Approved Transfers</span>
                                @if ($tyre_transfersApprovedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_transfersApprovedCount}}</span>
                                @endif
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('tyre_transfers.rejected') ? 'active' : '' }}"><a href="{{route('tyre_transfers.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Transfers</span>
                                @if ($tyre_transfersRejectedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_transfersRejectedCount}}</span>
                                @endif
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('tyre_products.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-boxes"></i> <span>Products</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('tyre_products.create')}}" ><i class="fas fa-plus "></i> <span>Create Product</span></a></li>
                            <li><a href="{{route('tyre_products.index')}}"><i class="fas fa-list "></i> <span>Manage Products</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('tyre_purchases.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-hand-holding-usd"></i> <span>Purchase Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('tyre_purchases.index')}}" ><i class="fas fa-list "></i> <span>Manage Orders</span></a></li>
                            @if ($isManagement || isset($wsdepartment_head) || isset($stdepartment_head) || $isSuperAdmin)
                            <li>
                                <a href="{{route('tyre_purchases.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Orders</span>
                                    @if ($tyre_purchasesPendingCount>0)
                                    <span class="label label-success ml-5">{{$tyre_purchasesPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('tyre_purchases.approved')}}" ><i class="fas fa-check "></i> <span>Approved Orders</span>
                                    @if ($tyre_purchasesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_purchasesApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('tyre_purchases.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Orders</span>
                                    @if ($tyre_purchasesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_purchasesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('tyre_purchases.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Orders</span>
                                    @if ($tyre_purchasesDeletedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_purchasesDeletedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children" {{ request()->routeIs('goods_receiveds.tyres') ? 'active' : '' }}>
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>GRV (Tyres)</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li ><a href="{{route('goods_receiveds.tyres')}}"><i class="fas fa-list "></i> <span>Manage Tyre GRVs</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children"  class="{{ request()->routeIs('tyres.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>Tyres</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('tyres.create')}}" ><i class="fas fa-plus "></i> <span>Create Tyre</span></a></li>
                            <li ><a href="{{route('tyres.index')}}"><i class="fas fa-list "></i> <span>Manage Tyres</span></a></li>
                            <li ><a href="{{route('tyre_assignments.index')}}"><i class="fas fa-list "></i> <span>Tyre Assignments</span></a></li>
                            <li ><a href="{{route('disposes.index')}}"><i class="fas fa-list "></i> <span>Disposed Items</span></a></li>
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('retreads.*') ? 'active' : '' }}" >
                        <a href="javascript:void(0)"><i class="fas fa-th-list"></i> <span>Retreads</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('retreads.create')}}" ><i class="fas fa-plus "></i> <span>Create Retread</span></a></li>
                            <li><a href="{{route('retreads.index')}}"><i class="fas fa-list "></i> <span>Manage Retread</span></a></li>
                            @if ($isManagement || isset($wsdepartment_head) || isset($stdepartment_head) || $isSuperAdmin)
                            <li>
                                <a href="{{route('retreads.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Retreads</span>
                                    @if ($retreadsPendingCount>0)
                                    <span class="label label-success ml-5">{{$retreadsPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('retreads.approved')}}" ><i class="fas fa-check "></i> <span>Approved Retreads</span>
                                    @if ($retreadsApprovedCount>0)
                                    <span class="label label-success ml-5">{{$retreadsApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('retreads.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Retreads</span>
                                    @if ($retreadsRejectedCount>0)
                                    <span class="label label-success ml-5">{{$retreadsRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <li class="has-children {{ request()->routeIs('tyre_dispatches.*') ? 'active' : '' }}">
                        <a href="javascript:void(0)"><i class="fas fa-list"></i> <span>Dispatches (Tyres) </span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('tyre_dispatches.index')}}" ><i class="fas fa-list "></i> <span>Manage Dispatches</span></a></li>
                            @if ($isManagement || $isAdmin || $isSuperAdmin)
                            <li>
                                <a href="{{route('tyre_dispatches.pending')}}" ><i class="fas fa-clock "></i> <span>Pending Dispatches</span>
                                    @if ($tyre_dispatchesPendingCount>0)
                                    <span class="label label-success ml-5">{{$tyre_dispatchesPendingCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('tyre_dispatches.approved')}}" ><i class="fas fa-check "></i> <span>Approved Dispatches</span>
                                    @if ($tyre_dispatchesApprovedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_dispatchesApprovedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{route('tyre_dispatches.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Dispatches</span>
                                    @if ($tyre_dispatchesRejectedCount>0)
                                    <span class="label label-success ml-5">{{$tyre_dispatchesRejectedCount}}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                @endif  
                @if ($isManagement || in_array('Directors', $rank_names)|| $isSuperAdmin)
                    <li class="nav-header">
                        <span class="">Business Settings</span>
                    </li>
                @if ($is_admin)
                    <li  class="{{ request()->routeIs('company-profile',$admin_company->id) ? 'active' : '' }}">
                        <a href="{{route('company-profile',$admin_company->id)}}"><i class="fas fa-cog"></i><span> {{ $admin_company->name }}</span> </a>
                    </li>
                @endif
                @if ($companies->count() >0)
                    @foreach ($companies as $company)
                        <li class="{{ request()->routeIs('company-profile',$company->id) ? 'active' : '' }}">
                            <a href="{{route('company-profile',$company->id)}}"><i class="fas fa-cog"></i><span> {{ $company->name }}</span> </a>
                        </li>
                    @endforeach
                @endif
                <li class="{{ request()->routeIs('companies.index') ? 'active' : '' }}">
                    <a href="{{route('companies.index')}}"><i class="fas fa-plus-circle"></i> <span>Create new business</span> </a>
                </li>
                @endif
                <li class="nav-header">
                    <span class="">Profile Settings</span>
                </li>
                <li class="{{ request()->routeIs('profile',$user->id) ? 'active' : '' }}">
                    <a href="{{route('profile',$user->id)}}"><i class="fas fa-user"></i> <span>My Profile</span> </a>
                </li>
                @if ($isSuperAdmin)
                <li  class="{{ request()->routeIs('audits.all') ? 'active' : '' }}">
                    <a href="{{route('audits.all')}}"><i class="fas fa-history"></i> <span>Audits</span> </a>
                </li>
                @endif
                <li>
                    <a href="{{route('logout')}}"><i class="fas fa-sign-out-alt" ></i> <span>Logout</span> </a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-nav -->
    </div>
    <!-- /.sidebar-content -->
</div>



