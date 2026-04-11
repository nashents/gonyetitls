{{-- resources/views/includes/navbar.blade.php --}}
{{-- NOTE: This Blade assumes you registered NavbarComposer and it shares:
     $user, $employee, $company, $department_names, $role_names,
     $license (['color','text'] or null),
     $reminders, $expired_reminders, $reminders_count
--}}

<nav class="navbar top-navbar bg-white box-shadow">
    <div class="container-fluid">
        <div class="row">
            <div class="navbar-header no-padding">
                <a class="navbar-brand" href="{{ route('dashboard.index') }}">
                    @if ($company && !empty($company->logo))
                        <img src="{{ asset('images/uploads/'.$company->logo) }}" alt="{{ $company->name }}" class="logo">
                    @endif
                </a>

                <span class="small-nav-handle hidden-sm hidden-xs"><i class="fa fa-outdent"></i></span>

                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-ellipsis-v"></i>
                </button>

                <button type="button" class="navbar-toggle mobile-nav-toggle">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <!-- /.navbar-header -->

            <div class="collapse navbar-collapse" id="navbar-collapse-1">
                <ul class="nav navbar-nav" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    <li class="hidden-sm hidden-xs"><a href="#" class="user-info-handle"><i class="fa fa-user"></i></a></li>
                    <li class="hidden-sm hidden-xs"><a href="#" class="full-screen-handle"><i class="fa fa-arrows-alt"></i></a></li>
                    <li><a href="#">Version: {{ config('app.version') }}</a></li>
                </ul>

                <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">

                    {{-- License banner (employee-company only) --}}
                    @if ($employee && $license)
                        <li style="color: {{ $license['color'] }}; margin:12px">
                            {{ $license['text'] }}
                        </li>
                    @endif

                    {{-- Create new --}}
                    @if ($employee)
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle bg-default tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                Create new <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" style="overflow-x:auto; width:50px; height:400px;">
                                @if (in_array('Human Resources', $department_names) || in_array('Super Admin', $role_names))
                                    <li><a href="{{ route('drivers.create') }}"><i class="fa fa-plus-square-o"></i>Driver</a></li>
                                    <li><a href="{{ route('employees.create') }}"><i class="fa fa-plus-square-o"></i>Employee</a></li>
                                @endif

                                @if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('assets.create') }}"><i class="fa fa-plus-square-o"></i>Asset</a></li>
                                    <li><a href="{{ route('bills.create') }}"><i class="fa fa-plus-square-o"></i>Bill</a></li>
                                    <li><a href="{{ route('invoices.create') }}"><i class="fa fa-plus-square-o"></i>Invoice</a></li>
                                    <li><a href="{{ route('quotations.create') }}"><i class="fa fa-plus-square-o"></i>Quotation</a></li>
                                @endif

                                @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('fuels.index') }}"><i class="fa fa-plus-square-o"></i>Fuel</a></li>
                                    <li><a href="{{ route('horses.create') }}"><i class="fa fa-plus-square-o"></i>Horse</a></li>
                                    <li><a href="{{ route('trailers.index') }}"><i class="fa fa-plus-square-o"></i>Trailer</a></li>
                                    <li><a href="{{ route('shifts.index') }}"><i class="fa fa-plus-square-o"></i>Shift</a></li>
                                    <li><a href="{{ route('trips.create') }}"><i class="fa fa-plus-square-o"></i>Trip</a></li>
                                    <li><a href="{{ route('vehicles.create') }}"><i class="fa fa-plus-square-o"></i>Vehicle</a></li>
                                @endif

                                @if (in_array('Workshop', $department_names) || in_array('Super Admin', $role_names))
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('bookings.create') }}"><i class="fa fa-plus-square-o"></i>Booking</a></li>
                                @endif

                                @if (in_array('Stores', $department_names) || in_array('Super Admin', $role_names))
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ route('inventories.create') }}"><i class="fa fa-plus-square-o"></i>Inventory</a></li>
                                    <li><a href="{{ route('tyres.create') }}"><i class="fa fa-plus-square-o"></i>Tyre</a></li>
                                @endif
                            </ul>
                        </li>

                        {{-- Reports --}}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle bg-primary tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-line-chart"></i> Reports <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" style="overflow-x:auto; width:50px; height:400px;">
                                @if ((in_array('Finance', $department_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                                    <li><a href="{{ route('reports.index') }}"><i class="fa fa-plus-square-o"></i>Financial Statements</a></li>
                                    <li><a href="{{ route('creditors.reports') }}"><i class="fa fa-plus-square-o"></i>Creditors</a></li>
                                    <li><a href="{{ route('debtors.reports') }}"><i class="fa fa-plus-square-o"></i>Debtors</a></li>
                                @endif

                                @if ((in_array('Transport & Logistics', $department_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                                    @if (!Auth::user()->driver)
                                        <li><a href="{{ route('trips.reports') }}"><i class="fa fa-plus-square-o"></i>Trips</a></li>

                                        <li role="separator" class="divider"></li>
                                        <li><a href="#"><strong>Age Pyramids</strong></a></li>
                                        <li><a href="{{ route('customers.age') }}"><i class="fa fa-plus-square-o"></i>Customers Age</a></li>
                                        <li><a href="{{ route('drivers.age') }}"><i class="fa fa-plus-square-o"></i>Drivers Age</a></li>
                                        <li><a href="{{ route('employees.age') }}"><i class="fa fa-plus-square-o"></i>Employees Age</a></li>
                                        <li><a href="{{ route('horses.age') }}"><i class="fa fa-plus-square-o"></i>Horses Age</a></li>
                                        <li><a href="{{ route('trailers.age') }}"><i class="fa fa-plus-square-o"></i>Trailers Age</a></li>
                                        <li><a href="{{ route('vehicles.age') }}"><i class="fa fa-plus-square-o"></i>Vehicles Age</a></li>
                                        <li><a href="{{ route('vendors.age') }}"><i class="fa fa-plus-square-o"></i>Vendors Age</a></li>

                                        <li role="separator" class="divider"></li>
                                        <li><a href="#"><strong>Next Service</strong></a></li>
                                        <li><a href="{{ route('horses.mileage') }}"><i class="fa fa-plus-square-o"></i>Horses</a></li>
                                        <li><a href="{{ route('trailers.mileage') }}"><i class="fa fa-plus-square-o"></i>Trailers</a></li>
                                        <li><a href="{{ route('vehicles.mileage') }}"><i class="fa fa-plus-square-o"></i>Vehicles</a></li>

                                        <li role="separator" class="divider"></li>
                                        <li><a href="#"><strong>Perfomance</strong></a></li>
                                        <li><a href="{{ route('drivers.performance') }}"><i class="fa fa-plus-square-o"></i>Drivers</a></li>
                                        <li><a href="{{ route('horses.performance') }}"><i class="fa fa-plus-square-o"></i>Horses</a></li>

                                        <li><a href="#"><strong>Horses</strong></a></li>
                                        <li><a href="{{ route('horses.statement.index') }}"><i class="fa fa-plus-square-o"></i>P &amp; L Statement</a></li>
                                    @endif
                                @endif
                            </ul>
                        </li>

                        {{-- Operational Notifications (pending authorizations) --}}
                       @php
                            $pendingCounts = $pendingCounts ?? [];

                            $isSuperAdmin = in_array('Super Admin', $role_names);
                            $isAdmin      = in_array('Admin', $role_names);

                            // Only Admins + Super Admin see pending notifications
                            $canSeeDropdown = $isSuperAdmin || $isAdmin;

                            $pendingMap = [
                                'trips' => ['label' => 'Trip', 'route' => 'trips.pending', 'icon'  => 'fa-truck'],
                                'bookings' => ['label' => 'Booking', 'route' => 'bookings.pending', 'icon'  => 'fa-calendar'],
                                'invoices' => ['label' => 'Invoice', 'route' => 'invoices.pending', 'icon'  => 'fa-file-text-o'],
                                'bills' => ['label' => 'Bill', 'route' => 'bills.pending', 'icon'  => 'fa-file-o'],
                                'credit_notes' => ['label' => 'Credit Note', 'route' => 'credit_notes.pending', 'icon'  => 'fa-file-text'],
                                'purchases' => ['label' => 'Purchase', 'route' => 'inventory_purchases.pending', 'icon'  => 'fa-shopping-cart'],
                                'transfers' => ['label' => 'Transfer', 'route' => 'transfers.pending', 'icon'  => 'fa-exchange'],
                                'dispatches' => ['label' => 'Dispatch', 'route' => 'inventory_dispatches.pending', 'icon'  => 'fa-send'],
                                'retreads' => ['label' => 'Retread', 'route' => 'retreads.pending', 'icon'  => 'fa-circle-o-notch'],
                                'recoveries' => ['label' => 'Recovery', 'route' => 'recoveries.pending', 'icon'  => 'fa-life-ring'],
                                'topups' => ['label' => 'Top Up', 'route' => 'top_ups.pending', 'icon'  => 'fa-level-up'],
                                'gate_passes' => ['label' => 'Gate Pass', 'route' => 'gate_passes.pending', 'icon'  => 'fa-sign-out'],
                                'waste_collections' => ['label' => 'Waste Collection', 'route' => 'waste_collections.pending', 'icon'  => 'fa-recycle'],
                                'waste_disposals' => ['label' => 'Waste Disposal', 'route' => 'waste_disposals.pending', 'icon'  => 'fa-trash'],
                                'requisitions' => ['label' => 'Requisition', 'route' => 'requisitions.pending', 'icon'  => 'fa-list-alt'],
                                'payrolls' => ['label' => 'Payroll', 'route' => 'payrolls.pending', 'icon'  => 'fa-money'],
                                'loans' => ['label' => 'Loan', 'route' => 'loans.pending', 'icon'  => 'fa-credit-card'],
                                'leaves' => ['label' => 'Leave', 'route' => 'leaves.pending', 'icon'  => 'fa-plane'],
                                'attendances' => ['label' => 'Attendance', 'route' => 'attendances.pending', 'icon'  => 'fa-clock-o'],
                                'overdue_tickets' => ['label' => 'Overdue Ticket','route' => 'tickets.index', 'icon'  => 'fa-wrench'],

                            ];

                            // Departments are ONLY for visibility (not data filtering)
                            $deptKeys = [
                                'Human Resource' => ['leaves', 'attendances', 'payrolls', 'loans'],
                                'Finance' => ['bills', 'invoices', 'credit_notes', 'requisitions'],
                                'Transport & Logistics' => ['trips', 'bookings', 'gate_passes', 'topups', 'recoveries' /*, 'fuel_requests'*/],
                                'Stores' => ['purchases', 'transfers', 'dispatches', 'retreads', 'topups' /*, 'fuel_requests'*/],
                                'Workshop' => ['bookings','overdue_tickets'],
                                'HSEQ' => [ 'waste_collections', 'waste_disposals'],
                            ];

                            // Build user departments (belongsToMany)
                            $employeeDepartments = collect($employee?->departments ?? [])
                                ->pluck('name')
                                ->filter()
                                ->values();

                            // Allowed keys:
                            // - Super Admin: everything
                            // - Admin: union of keys for all their departments
                            if ($isSuperAdmin) {
                                $allowedKeys = array_keys($pendingMap);
                            } else {
                                $allowedKeys = $employeeDepartments
                                    ->flatMap(fn ($deptName) => $deptKeys[$deptName] ?? [])
                                    ->unique()
                                    ->values()
                                    ->toArray();
                            }

                            // IMPORTANT: total is ALWAYS sum of what they can see
                            $pendingTotal = collect($allowedKeys)
                                ->sum(fn ($k) => (int) ($pendingCounts[$k] ?? 0));

                            // List is ONLY what they can see
                            $sortedPendingMap = collect($pendingMap)
                                ->only($allowedKeys)
                                ->sortBy(fn ($item) => $item['label'])
                                ->toArray();
                        @endphp

                        @if ($canSeeDropdown)
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                                role="button" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-tasks"></i>
                                    @if ($pendingTotal > 0)
                                        <span class="badge badge-warning">{{ $pendingTotal }}</span>
                                    @endif
                                </a>

                                <ul class="dropdown-menu" style="max-height:400px; overflow-y:auto;">
                                    <li class="dropdown-header">
                                        <strong>Operational Notifications</strong>
                                    </li>

                                    @if ($pendingTotal === 0)
                                        <li>
                                            <a href="#">
                                                <i class="fa fa-check text-success"></i>
                                                No pending items
                                            </a>
                                        </li>
                                    @else
                                        @foreach ($sortedPendingMap as $key => $meta)
                                            @php $count = (int) ($pendingCounts[$key] ?? 0); @endphp
                                            @if ($count > 0)
                                                @php
                                                    $routeParams = [];
                                                    if ($meta['route'] === 'gate_passes.pending') {
                                                        $routeParams = ['department' => 'security'];
                                                    }
                                                    if ($key === 'overdue_tickets') {
                                                        $routeParams['overdue'] = 1;
                                                    }else {
                                                        $routeParams['notifications'] = 1;
                                                    }
                                                @endphp
                                                <li>
                                                    <a href="{{ route($meta['route'], $routeParams) }}">
                                                        <i class="fa {{ $meta['icon'] }}"></i>
                                                        {{ $count }} pending {{ $meta['label'] }}{{ $count > 1 ? 's' : '' }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- Notifications / Reminders --}}
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                @if ($reminders_count > 0)
                                    <span class="badge badge-danger">{{ $reminders_count }}</span>
                                @endif
                            </a>

                           @php $useTall = (($reminders?->count() + $expired_reminders?->count() + $mileage_reminders?->count()) > 10); @endphp

                            <ul class="dropdown-menu" style="{{ $useTall ? "overflow-x:auto; height:400px;" : "" }}">
                                <li style="margin: 10px">
                                    <center>
                                        <li><a href="{{ route('reminders.index') }}"><i class="fa fa-plus-square-o"></i> New Reminder</a></li>
                                    </center>
                                </li>

                                @if ($reminders && $reminders->count() > 0)
                                    <div class="clearfix"></div>
                                    <li role="separator" class="divider"></li>

                                    <li><a href="#"><strong style="color:gray">Valid Reminders</strong></a></li>

                                    @foreach ($reminders as $reminder)
                                        <li>
                                            <a href="{{ route('fitnesses.show', $reminder->id) }}">
                                                <i class="fa fa-bell" style="color: green"></i>
                                                {{ optional($reminder->reminder_item)->name }}

                                                @if ($reminder->horse)
                                                    for {{ optional($reminder->horse)->registration_number }}
                                                @elseif ($reminder->vehicle)
                                                    for {{ optional($reminder->vehicle)->registration_number }}
                                                @elseif ($reminder->trailer)
                                                    for {{ optional($reminder->trailer)->registration_number }}
                                                @elseif ($reminder->employee)
                                                    for {{ optional($reminder->employee)->name }} {{ optional($reminder->employee)->surname }}
                                                @endif

                                                expires on {{ \Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif

                                @if ($expired_reminders && $expired_reminders->count() > 0)
                                    <li role="separator" class="divider"></li>

                                    <li><a href="#"><strong style="color:gray">Expired Reminders</strong></a></li>

                                    @foreach ($expired_reminders as $reminder)
                                        <li>
                                            <a href="{{ route('fitnesses.show', $reminder->id) }}">
                                                <i class="fa fa-bell" style="color: red"></i>
                                                {{ optional($reminder->reminder_item)->name }}

                                                @if ($reminder->horse)
                                                    for {{ optional($reminder->horse)->registration_number }}
                                                @elseif ($reminder->vehicle)
                                                    for {{ optional($reminder->vehicle)->registration_number }}
                                                @elseif ($reminder->trailer)
                                                    for {{ optional($reminder->trailer)->registration_number }}
                                                @elseif ($reminder->employee)
                                                    for {{ optional($reminder->employee)->name }} {{ optional($reminder->employee)->surname }}
                                                @endif

                                                expired on {{ \Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif

                                @if ($mileage_reminders && $mileage_reminders->count() > 0)
                                    <li role="separator" class="divider"></li>

                                    <li><a href="#"><strong style="color: gray">Workshop / Service Due</strong></a></li>

                                    @foreach ($mileage_reminders as $asset)
                                        @php
                                            $gap = $asset->mileage_gap;

                                            if ($gap <= 0) {
                                                $icon_color = 'darkred';
                                                $urgency    = 'OVERDUE by ' . number_format(abs($gap)) . ' km';
                                            } elseif ($gap <= 100) {
                                                $icon_color = 'red';
                                                $urgency    = number_format($gap) . ' km remaining';
                                            } elseif ($gap <= 500) {
                                                $icon_color = 'orangered';
                                                $urgency    = number_format($gap) . ' km remaining';
                                            } elseif ($gap <= 1000) {
                                                $icon_color = 'orange';
                                                $urgency    = number_format($gap) . ' km remaining';
                                            } else {
                                                $icon_color = 'goldenrod';
                                                $urgency    = number_format($gap) . ' km remaining';
                                            }

                                            $asset_icon = match($asset->asset_type) {
                                                'horse'   => 'fa-horse',
                                                'vehicle' => 'fa-truck',
                                                'trailer' => 'fa-trailer',
                                                default   => 'fa-wrench',
                                            };
                                        @endphp

                                       <li>
                                            <a href="{{ $asset->asset_route }}">
                                                <i class="fa fa-wrench" style="color: {{ $icon_color }}"></i>
                                                <i class="fa {{ $asset_icon }}" style="color: gray; font-size: 11px"></i>
                                                {{ $asset->asset_label }}
                                                &mdash; {{ $urgency }}
                                                <small style="color: gray">
                                                    (current: {{ number_format($asset->mileage) }} km &bull; service at {{ number_format($asset->next_service) }} km)
                                                </small>
                                            </a>
                                        </li>
                                        @endforeach
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- Profile dropdown --}}
                    @if ($user)
                        <li class="dropdown tour-two">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                {{ ucfirst($user->name) }} {{ ucfirst($user->surname) }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu profile-dropdown">
                                <li class="profile-menu bg-gray">
                                    <div class="">
                                        @if ($company)
                                            <img src="{{ asset('images/uploads/'.$company->logo) }}" alt="{{ $company->name }}" class="img-circle profile-img">
                                            <div class="profile-name">
                                                <h6>{{ $company->name }}</h6>
                                            </div>
                                        @endif
                                        <div class="clearfix"></div>
                                    </div>
                                </li>

                                @if (in_array('Super Admin', $role_names) && $company)
                                    <li><a href="{{ route('company-profile', $company->id) }}"><i class="fa fa-cog"></i>Business Settings</a></li>
                                @endif

                                <li role="separator" class="divider"></li>

                                <li class="profile-menu bg-gray">
                                    <div class="">
                                        @if (!empty($user->profile))
                                            <img src="{{ asset('images/uploads/'.$user->profile) }}" alt="{{ ucfirst($user->name) }} {{ ucfirst($user->surname) }}" class="img-circle profile-img" style="width: 50px; height:50px">
                                        @endif
                                        <div class="profile-name">
                                            <h6>{{ ucfirst($user->name) }} {{ ucfirst($user->surname) }}</h6>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </li>

                                @if ($employee)
                                    <li><a href="{{ route('profile', $user->id) }}"><i class="fa fa-cog"></i>Profile Settings</a></li>
                                @endif

                                <li><a href="{{ route('logout') }}" class="color-danger"><i class="fa fa-sign-out"></i> Logout</a></li>
                            </ul>
                        </li>
                    @endif
                    <!-- /.dropdown -->
                </ul>
                <!-- /.nav navbar-nav navbar-right -->
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</nav>