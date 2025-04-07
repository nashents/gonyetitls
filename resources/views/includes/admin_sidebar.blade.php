
  <style>
    .bg-black-300 {
    background-color: {{Auth::user()->company->color}};
    }
</style>

<div class="left-sidebar fixed-sidebar bg-black-300 box-shadow tour-three">
    <div class="sidebar-content">
        <div class="user-info closed">
            <img src="{{asset('images/uploads/'.Auth::user()->company->logo)}}" alt="{{Auth::user()->name}}" class="img-circle profile-img" style="width: 90px; height:90px">
            <h6 class="title">{{Auth::user()->name}}</h6>
            <small class="info">Company</small>
        </div>
        <!-- /.user-info -->

        <div class="sidebar-nav">
            <ul class="side-nav color-gray">
                <li class="nav-header">
                    <span class="">Main Category</span>
                </li>
                <li>
                    <a href="{{route('dashboard.index')}}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span> </a>
                </li>
                @php
                $departments = Auth::user()->employee->departments;
                foreach($departments as $department){
                    $department_names[] = $department->name;
                }
                $roles = Auth::user()->roles;a
                foreach($roles as $role){
                    $role_names[] = $role->name;
                }
                @endphp
                @if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names))
                <li class="nav-header">
                    <span class="">Administration</span>
                </li>
                @if (Auth::user()->is_admin())
                <li class="has-children">
                    <a href="#"><i class="fas fa-industry"></i> <span>Companies</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('companies.index')}}" ><i class="fas fa-plus "></i> <span>Create Company</span></a></li>
                        <li><a href="{{route('companies.manage')}}" ><i class="fas fa-list "></i> <span>Manage Companies</span></a></li>
                    </ul>
                </li>
                @endif
                <li class="has-children">
                    <a href="#"><i class="fas fa-cog"></i> <span>Masters</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">

                        @if ((in_array('Admin', $role_names) && in_array('Human Resources', $department_names)) || in_array('Super Admin', $role_names))
                        <li>
                            <a href="{{route('branches.index')}}"><i class="fas fa-sitemap"></i> <span>Branches</span> </a>
                        </li>
                        <li>
                            <a href="{{route('departments.index')}}"><i class="far fa-building"></i> <span>Departments</span> </a>
                        </li>
                        <li>
                            <a href="{{route('job_titles.index')}}"><i class="far fa-file"></i> <span>Job Titles</span> </a>
                        </li>
                        <li>
                            <a href="{{route('leave_types.index')}}"><i class="fas fa-calendar"></i> <span>Leave Types</span> </a>
                        </li>
                        @endif
                        @if ((in_array('Admin', $role_names) && in_array('Transport & Logistics', $department_names)) || in_array('Super Admin', $role_names))
                        <li>
                            <a href="{{route('destinations.index')}}"><i class="fas fa-map-pin"></i> <span>Destinations</span> </a>
                        </li>
                        <li>
                            <a href="{{route('cargos.index')}}"><i class="fas fa-truck-loading"></i> <span>Cargos</span> </a>
                        </li>
                        <li>
                            <a href="{{route('countries.index')}}"><i class="fas fa-globe-africa"></i> <span>Countries</span> </a>
                        </li>
                        <li>
                            <a href="{{route('currencies.index')}}"><i class="far fa-money-bill-alt"></i> <span>Currencies</span> </a>
                        </li>
                        <li>
                            <a href="{{route('trip_types.index')}}"><i class="fas fa-road"></i> <span>Trip Types</span> </a>
                        </li>
                        <li>
                            <a href="{{route('vehicle_groups.index')}}"><i class="fas fa-truck"></i> <span>Vehicle Groups</span> </a>
                        </li>
                        <li>
                            <a href="{{route('vehicle_types.index')}}"><i class="fas fa-truck"></i> <span>Vehicle Types</span> </a>
                        </li>

                        <li>
                            <a href="{{route('vendor_types.index')}}"><i class="fas fa-user-friends"></i> <span>Vendor Types</span> </a>
                        </li>
                        @endif
                        @if ((in_array('Admin', $role_names) && in_array('Stores', $department_names)) || in_array('Super Admin', $role_names))
                        <li>
                            <a href="{{route('service_types.index')}}"><i class="fas fa-wrench"></i> <span>Service Types</span> </a>
                        </li>
                        {{-- <li>
                            <a href="{{route('brands.index')}}" ><i class="fas fa-list "></i> <span>Brands</span></a>
                        </li>
                        <li>
                            <a href="{{route('categories.index')}}" ><i class="fas fa-boxes "></i> <span>Categories</span></a>
                        </li>
                        <li>
                            <a href="{{route('stores.index')}}" ><i class="fas fa-warehouse "></i> <span>Stores</span></a>
                        </li>
                        <li>
                            <a href="{{route('attributes.index')}}" ><i class="fas fa-list "></i> <span>Attributes</span></a>
                        </li>
                        <li>
                            <a href="{{route('vehicle_makes.index')}}" ><i class="fas fa-truck "></i> <span>Vehicles</span></a>
                        </li> --}}
                        @endif
                    </ul>
                </li>
                @endif
                @if ((in_array('Admin', $role_names) && in_array('Transport & Logistics', $department_names)) || in_array('Super Admin', $role_names))
                <li class="has-children">
                    <a href="#"><i class="fas fa-handshake-slash"></i> <span>Partners</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('brokers.index')}}"><i class="fas fa-user-friends"></i> <span>Brokers</span></a></li>
                        <li><a href="{{route('customers.index')}}" ><i class="fas fa-user-friends"></i> <span>Customers</span></a></li>
                        <li><a href="{{route('vendors.index')}}"><i class="fas fa-user-friends"></i> <span>Vendors</span></a></li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('brokers.reports')}}" ><i class="fas fa-info-circle "></i> <span>Brokers</span></a></li>
                                <li><a href="{{route('customers.reports')}}" ><i class="fas fa-info-circle "></i> <span>Customers</span></a></li>
                                <li><a href="{{route('vendors.reports')}}" ><i class="fas fa-info-circle "></i> <span>Vendors</span></a></li>
                            </ul>
                        </li>

                    </ul>
                </li>
                @endif

                @php
                    $departments = Auth::user()->employee->departments;
                    foreach($departments as $department){
                        $department_names[] = $department->name;
                    }
                    $roles = Auth::user()->roles;
                    foreach($roles as $role){
                        $role_names[] = $role->name;
                    }
                    $ranks = Auth::user()->employee->ranks;
                        foreach($ranks as $rank){
                            $rank_names[] = $rank->name;
                        }
                @endphp
                @if (in_array('Human Resources', $department_names) || in_array('Super Admin', $role_names))
                <li class="nav-header">
                    <span class="">Human Resource</span>
                </li>
                <li class="has-children">
                    <a href="#"><i class="fas fa-users"></i> <span>Employees</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('employees.create')}}" ><i class="fas fa-user "></i> <span>Employee</span></a></li>
                        <li><a href="{{route('employees.index')}}"><i class="fas fa-list "></i> <span>Manage Employees</span></a></li>
                        <li><a href="{{route('employees.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Employees</span></a></li>
                        <li><a href="{{route('drivers.create')}}" ><i class="fas fa-user "></i> <span>Driver</span></a></li>
                        <li><a href="{{route('drivers.index')}}"><i class="fas fa-list "></i> <span>Manage Drivers</span></a></li>
                        <li><a href="{{route('drivers.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Drivers</span></a></li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('employees.reports')}}" ><i class="fas fa-info-circle "></i> <span>Employees</span></a></li>
                                <li><a href="{{route('drivers.reports')}}" ><i class="fas fa-info-circle "></i> <span>Drivers</span></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif
                <li class="has-children">
                    <a href="#"><i class="fas fa-calendar"></i> <span>Leave Management</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('leaves.index')}}"><i class="fas fa-arrow-right "></i> <span>My Applications</span></a></li>

                        @if (in_array('HOD', $rank_names) || (in_array('Management', $rank_names) && in_array('Human Resources', $department_names)) || in_array('Super Admin', $role_names))
                        <li><a href="{{route('leaves.pending')}}"><i class="fas fa-clock-o "></i> <span>Pending</span></a></li>
                        <li><a href="{{route('leaves.approved')}}"><i class="fas fa-check "></i> <span>Approved</span></a></li>
                        <li><a href="{{route('leaves.rejected')}}"><i class="fas fa-ban "></i> <span>Rejected</span></a></li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('leaves.reports')}}" ><i class="fas fa-info-circle "></i> <span>Applications</span></a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>

                <li>
                    <a href="{{route('notices.index')}}"><i class="fas fa-bullhorn"></i> <span>Notice</span> </a>
                </li>
                <li>
                    <a href="{{route('emails.index')}}"><i class="fas fa-envelope"></i> <span>E-Mail</span> </a>
                </li>


                @php
                $departments = Auth::user()->employee->departments;
                foreach($departments as $department){
                    $department_names[] = $department->name;
                }
                $roles = Auth::user()->roles;
                foreach($roles as $role){
                    $role_names[] = $role->name;
                }
                $ranks = Auth::user()->employee->ranks;
                foreach($ranks as $rank){
                    $rank_names[] = $rank->name;
                }
                @endphp

                  @if (in_array('Super Admin', $role_names))
                <li class="nav-header">
                    <span class="">Income & Expenses</span>
                </li>
                <li>
                    <a href="{{route('cashflows.index')}}"><i class="fas fa-money-bill-wave"></i> <span>Cashflow</span> </a>
                </li>
                @endif

                    <li class="nav-header">
                        <span class="">Transport & Logistics</span>
                    </li>
                    @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                        <li class="has-children">
                            <a href="#"><i class="fas fa-truck"></i> <span>Fleet Management</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('vehicles.create')}}" ><i class="fas fa-plus "></i> <span>Vehicle</span></a></li>
                                <li><a href="{{route('vehicles.index')}}"><i class="fas fa-list "></i> <span>Manage Vehicles</span></a></li>
                                <li><a href="{{route('trailers.index')}}" ><i class="fas fa-plus "></i> <span> Trailers </span></a></li>
                                <li><a href="{{route('trailers.manage')}}"><i class="fas fa-list "></i> <span>Manage Trailers</span></a></li>
                                <li><a href="{{route('assignments.index')}}"><i class="fas fa-tasks "></i> <span>Assignments</span></a></li>
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('vehicles.reports')}}" ><i class="fas fa-info-circle "></i> <span>Vehicles</span></a></li>
                                        <li><a href="{{route('assignments.reports')}}" ><i class="fas fa-info-circle "></i> <span>Assignments</span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="has-children">
                    <a href="#"><i class="fas fa-gas-pump"></i> <span>Fuel Management</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        @php
                            $fuelsPendingCount = App\Models\Fuel::where('authorization','pending')
                            ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                            $fuelsApprovedCount = App\Models\Fuel::where('authorization','approved')
                            ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        //  ->where('created_at', '>', \Carbon\Carbon::now()->startOfWeek())
                        //  ->where('created_at', '<', \Carbon\Carbon::now()->endOfWeek())->get()->count();
                            $fuelsRejectedCount = App\Models\Fuel::where('authorization','rejected')
                            ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                            $fuelsDelectedCount = App\Models\Fuel::onlyTrashed()
                            ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                         @endphp
                     @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                            <li class="has-children">
                                <a href="#"><span>Fuel Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li><a href="{{route('fuels.index')}}" ><i class="fas fa-plus "></i> <span>Create Order</span></a></li>
                                    <li><a href="{{route('fuels.manage')}}" ><i class="fas fa-list "></i> <span>Manage Orders</span></a></li>
                                    @if (in_array('Management', $rank_names) || in_array('Super Admin', $role_names))
                                    <li><a href="{{route('fuels.pending')}}" ><i class="fas fa-clock-o "></i> <span>Pending Orders</span>
                                    @if ($fuelsPendingCount>0)
                                    <span class="label label-success ml-5">{{$fuelsPendingCount}}</span>
                                    @endif
                                    </a></li>
                                    <li><a href="{{route('fuels.approved')}}" ><i class="fas fa-check "></i> <span>Approved Orders</span>
                                    @if ($fuelsApprovedCount>0)
                                    <span class="label label-success ml-5">{{$fuelsApprovedCount}}</span>
                                    @endif
                                    </a></li>
                                    <li><a href="{{route('fuels.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Orders</span>
                                    @if ($fuelsRejectedCount>0)
                                    <span class="label label-success ml-5">{{$fuelsRejectedCount}}</span>
                                    @endif
                                    </a></li>
                                    <li><a href="{{route('fuels.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Orders</span>
                                    @if ($fuelsDelectedCount>0)
                                    <span class="label label-success ml-5">{{$fuelsDelectedCount}}</span>
                                    @endif
                                    </a></li>
                                    @endif
                                </ul>
                            </li>
                    @endif
                        @php
                        $fuelRequesitionPendingCount = App\Models\FuelRequest::where('status','pending')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        $fuelRequesitionApprovedCount = App\Models\FuelRequest::where('status','approved')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                       //  ->where('created_at', '>', \Carbon\Carbon::now()->startOfWeek())
                       //  ->where('created_at', '<', \Carbon\Carbon::now()->endOfWeek())->get()->count();
                        $fuelRequesitionRejectedCount = App\Models\FuelRequest::where('status','rejected')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        $fuelRequesitionDelectedCount = App\Models\FuelRequest::onlyTrashed()
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        @endphp
                        <li class="has-children">
                            <a href="#"><span>Fuel Requisitions</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('fuel_requests.myrequests',Auth::user()->employee->id)}}" ><i class="fas fa-arrow-right "></i> <span>My Requests</span></a></li>
                                @if ( (in_array('Transport & Logistics', $department_names) && in_array('Management', $rank_names)) || in_array('Super Admin', $role_names))
                                <li><a href="{{route('fuel_requests.pending')}}" ><i class="fas fa-clock-o "></i> <span>Pending Requests</span>
                                    @if ($fuelRequesitionPendingCount>0)
                                    <span class="label label-success ml-5">{{$fuelRequesitionPendingCount}}</span>
                                    @endif
                                </a></li>
                                <li><a href="{{route('fuel_requests.approved')}}" ><i class="fas fa-check "></i> <span>Approved Requests</span>
                                    @if ($fuelRequesitionApprovedCount>0)
                                    <span class="label label-success ml-5">{{$fuelRequesitionApprovedCount}}</span>
                                    @endif
                                </a></li>
                                <li><a href="{{route('fuel_requests.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected Requests</span>
                                    @if ($fuelRequesitionRejectedCount>0)
                                    <span class="label label-success ml-5">{{$fuelRequesitionRejectedCount}}</span>
                                    @endif
                                </a></li>
                                <li><a href="{{route('fuel_requests.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Requests</span>
                                    @if ($fuelRequesitionDelectedCount>0)
                                    <span class="label label-success ml-5">{{$fuelRequesitionDelectedCount}}</span>
                                    @endif
                                </a></li>
                                @endif
                            </ul>
                        </li>

                        <li class="has-children">
                            <a href="#"><span>Fuel Allocations</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                @php
                                    $myAllocationCount = App\Models\Allocation::where('employee_id',Auth::user()->employee->id)
                                    ->where('created_at', '>', \Carbon\Carbon::now()->startOfWeek())
                                    ->where('created_at', '<', \Carbon\Carbon::now()->endOfWeek())->get()->count();
                                @endphp
                                <li><a href="{{route('allocations.myallocations',Auth::user()->employee->id)}}" ><i class="fas fa-arrow-right "></i> <span>My Allocation</span>
                                    @if ($myAllocationCount>0)
                                    <span class="label label-success ml-5">{{$myAllocationCount}}</span>
                                    @endif
                                </a></li>

                                @if ((in_array('Transport & Logistcs', $role_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                                    <li><a href="{{route('allocations.index')}}" ><i class="fas fa-plus "></i> <span>Create Allocation</span></a></li>
                                    <li><a href="{{route('allocations.manage')}}" ><i class="fas fa-list "></i> <span>Manage Allocations</span></a></li>
                                    <li><a href="{{route('allocations.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Allocations</span></a></li>
                                @endif
                            </ul>
                        </li>
                        @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                        <li class="has-children">
                            <a href="#"><span>Fuel Suppliers</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('containers.index')}}" ><i class="fas fa-plus "></i> <span>Create Supplier</span></a></li>
                                <li><a href="{{route('containers.manage')}}" ><i class="fas fa-list "></i> <span>Manage Suppliers</span></a></li>
                                <li><a href="{{route('containers.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Suppliers</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('allocations.reports')}}" ><i class="fas fa-info-circle "></i> <span>Allocations</span></a></li>
                                <li><a href="{{route('fuel_requests.reports')}}" ><i class="fas fa-info-circle "></i> <span>Fuel Requests</span></a></li>
                                <li><a href="{{route('containers.reports')}}" ><i class="fas fa-info-circle "></i> <span>Containers</span></a></li>
                            </ul>
                        </li>
                        @endif
                        </ul>
                    </li>
                    @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                    @php
                        $tripPendingCount = App\Models\Trip::where('authorization','pending')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        $tripApprovedCount = App\Models\Trip::where('authorization','approved')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                       //  ->where('created_at', '>', \Carbon\Carbon::now()->startOfWeek())
                       //  ->where('created_at', '<', \Carbon\Carbon::now()->endOfWeek())->get()->count();
                        $tripCount = App\Models\Trip::where('authorization','rejected')
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        $tripCount = App\Models\Trip::onlyTrashed()
                        ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                        @endphp
                    <li class="has-children">
                         <a href="#"><i class="fas fa-road"></i> <span>Trip Management</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li class="has-children">
                                <a href="#"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li><a href="{{route('expenses.index')}}" ><i class="fas fa-list "></i> <span>Expenses</span></a></li>
                                    <li class="has-children">
                                        <a href="#"><i class="fas fa-map"></i> <span>Points</span> <i class="fas fa-angle-right arrow"></i></a>
                                        <ul class="child-nav">
                                            <li><a href="{{route('loading_points.index')}}" ><i class="fas fa-map-marker"></i> <span>Loading Points</span></a></li>
                                            <li><a href="{{route('offloading_points.index')}}" ><i class="fas fa-map-marker "></i> <span>Offloading Points</span></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-children">
                                <a href="#"><i class="fas fa-road"></i> <span>Trips</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                            @php
                                $tripsPendingCount = App\Models\Trip::where('authorization','pending')
                                ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                                $tripsApprovedCount = App\Models\Trip::where('authorization','approved')
                                ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                                //  ->where('created_at', '>', \Carbon\Carbon::now()->startOfWeek())
                                //  ->where('created_at', '<', \Carbon\Carbon::now()->endOfWeek())->get()->count();
                                $tripsRejectedCount = App\Models\Trip::where('authorization','rejected')
                                ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                                $transportOrdersCount = App\Models\TransportOrder::whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                                $tripsDelectedCount = App\Models\Trip::onlyTrashed()
                                ->whereDate('created_at', \Carbon\Carbon::today())->get()->count();
                            @endphp
                            <li><a href="{{route('trips.create')}}" ><i class="fas fa-plus "></i> <span>Create</span></a></li>
                            <li><a href="{{route('trips.index')}}" ><i class="fas fa-list "></i> <span>Manage</span></a></li>
                            @if (in_array('Management', $rank_names) || in_array('Super Admin', $role_names))
                            <li><a href="{{route('trips.pending')}}" ><i class="fas fa-clock-o "></i> <span>Pending</span>
                            @if ($tripsPendingCount>0)
                            <span class="label label-success ml-5">{{$tripsPendingCount}}</span>
                            @endif
                            </a></li>
                            <li><a href="{{route('trips.approved')}}" ><i class="fas fa-check "></i> <span>Approved</span>
                                @if ($tripsApprovedCount>0)
                                <span class="label label-success ml-5">{{$tripsApprovedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('trips.rejected')}}" ><i class="fas fa-ban "></i> <span>Rejected</span>
                                @if ($tripsRejectedCount>0)
                                <span class="label label-success ml-5">{{$tripsRejectedCount}}</span>
                                @endif
                            </a></li>
                            <li><a href="{{route('trips.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted</span>
                            @if ($tripsDelectedCount>0)
                            <span class="label label-success ml-5">{{$tripsDelectedCount}}</span>
                            @endif
                            </a></li>
                            @endif
                            <li><a href="{{route('transport_orders.index')}}" ><i class="fas fa-list "></i> <span>Transportation Orders</span>
                                @if ($transportOrdersCount>0)
                                <span class="label label-success ml-5">{{$transportOrdersCount}}</span>
                                @endif
                            </a>
                            </li>

                                </ul>
                            </li>
                            <li class="has-children">
                                <a href="#"><i class="fas fa-file"></i> <span>Quotations</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li><a href="{{route('quotations.index')}}" ><i class="fas fa-plus "></i> <span>Create Quotation</span></a></li>
                                    <li><a href="{{route('quotations.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Quotations</span></a></li>
                                </ul>
                            </li>
                            {{-- <li class="has-children">
                                <a href="#"><i class="fas fa-file"></i> <span>Invoices</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li><a href="{{route('invoices.index')}}" ><i class="fas fa-plus "></i> <span>Create Invoice</span></a></li>
                                    <li><a href="{{route('invoices.deleted')}}" ><i class="fas fa-trash "></i> <span>Deleted Invoices</span></a></li>
                                </ul>
                            </li> --}}
                            <li class="has-children">
                                <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                                <ul class="child-nav">
                                    <li><a href="{{route('trips.reports')}}" ><i class="fas fa-info-circle "></i> <span>Trips</span></a></li>
                                    <li><a href="#" ><i class="fas fa-info-circle "></i> <span>Quotations</span></a></li>
                                    <li><a href="#" ><i class="fas fa-info-circle "></i> <span>Invoices</span></a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endif
                        @php
                        $departments = Auth::user()->employee->departments;
                        foreach($departments as $department){
                            $department_names[] = $department->name;
                        }
                        $roles = Auth::user()->roles;
                        foreach($roles as $role){
                            $role_names[] = $role->name;
                        }
                        @endphp
                 @if (in_array('Stores', $department_names)|| in_array('Super Admin', $role_names))
                    {{-- <li class="has-children">
                        <a href="#"><i class="fas fa-ring"></i> <span>Tyre Management</span> <i class="fas fa-angle-right arrow"></i></a>
                        <ul class="child-nav">
                            <li><a href="{{route('tyres.create')}}" ><i class="fas fa-plus "></i> <span>Purchase & Fitment</span></a></li>
                            <li><a href="{{route('tyres.index')}}" ><i class="fas fa-list "></i> <span>Manage Purchases</span></a></li>
                            <li><a href="{{route('tyres.create')}}" ><i class="fas fa-plus "></i> <span>Unassigned</span></a></li>
                            <li><a href="{{route('tyres.all')}}" ><i class="fas fa-plus "></i> <span>All Tyres</span></a></li>
                            <li><a href="{{route('tyres.create')}}" ><i class="fas fa-wrench "></i><i class="fas fa-ring"></i><span>Retreads</span></a></li>
                        </ul>
                    </li> --}}
                    {{-- <li class="nav-header">
                        <span class="">Inventory Management</span>
                    </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-boxes"></i> <span>Products</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('products.index')}}" ><i class="fas fa-plus "></i> <span>Create</span></a></li>
                                <li><a href="{{route('products.manage')}}" ><i class="fas fa-list "></i> <span>Manage Products</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-hand-holding-usd"></i> <span>Purchase</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('products.index')}}" ><i class="fas fa-plus "></i> <span>Purchase</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-line-chart"></i> <span>Stocks</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('stocks.create')}}" ><i class="fas fa-list "></i> <span>Opening Stock</span></a></li>
                                <li><a href="{{route('stocks.index')}}" ><i class="fas fa-list "></i> <span>Closing Stock</span></a></li>
                            </ul>
                        </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-cart-plus"></i> <span>Orders</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                                <li><a href="{{route('orders.create')}}" ><i class="fas fa-plus "></i> <span>Create</span></a></li>
                                <li><a href="{{route('orders.index')}}" ><i class="fas fa-list "></i> <span>Manage Orders</span></a></li>
                            </ul>
                        </li> --}}
                @endif
                        <li class="nav-header">
                            <span class="">Account</span>
                        </li>
                        @if (in_array('Super Admin', $role_names))
                        <li>
                            @if (isset(Auth::user()->company))
                            <a href="{{route('company-profile',Auth::user()->company->id)}}"><i class="fas fa-info-circle"></i> <span>Company Profile</span> </a>
                            @elseif(Auth::user()->employee->company)
                            <a href="{{route('company-profile',Auth::user()->employee->company->id)}}"><i class="fas fa-info-circle"></i> <span>Company Profile</span> </a>
                            @endif

                        </li>
                        @endif
                        <li>
                            <a href="{{route('profile',Auth::user()->id)}}"><i class="fas fa-user"></i> <span>Profile</span> </a>
                        </li>
                        <li>
                            <a href="{{route('logout')}}"><i class="fas fa-sign-out-alt" ></i> <span>Logout</span> </a>
                        </li>
        </div>
        <!-- /.sidebar-nav -->
    </div>
    <!-- /.sidebar-content -->
</div>
