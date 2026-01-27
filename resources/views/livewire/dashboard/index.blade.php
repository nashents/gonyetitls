<div>
    <section class="section">
        <div class="container-fluid">
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

        @if ($driver)
               <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('driver.trips',$driver->id)}}">
                        <span class="number counter">{{$driver_trips}}</span>
                        <span class="name">Trips</span>
                        <span class="bg-icon"><i class="fa fa-road"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('driver.inspections',$driver->id)}}">
                        <span class="number counter">{{$driver_inspections}}</span>
                        <span class="name">Inspections</span>
                        <span class="bg-icon"><i class="fa fa-search"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('driver.breakdowns',$driver->id)}}">
                        <span class="number counter">{{$driver_breakdowns}}</span>
                        <span class="name">Breakdown Reports</span>
                        <span class="bg-icon"><i class="fa fa-wrench"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('driver.recoveries',$driver->id)}}">
                        <span class="number counter">{{$driver_recoveries}}</span>
                        <span class="name">Recoveries</span>
                        <span class="bg-icon"><i class="fas fa-list"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

            </div>
            <br>
        @endif
            
        @if ((in_array('Human Resources', $department_names) || in_array('Super Admin', $role_names)))
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('branches.index')}}">
                        <span class="number counter">{{$branch_count}}</span>
                        <span class="name">Branches</span>
                        <span class="bg-icon"><i class="fa fa-network-wired"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="#">
                        <span class="number counter">{{$department_count}}</span>
                        <span class="name">Departments</span>
                        <span class="bg-icon"><i class="fa fa-building"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('employees.index')}}">
                        <span class="number counter">{{$employee_count}}</span>
                        <span class="name">Employees</span>
                        <span class="bg-icon"><i class="fa fa-users"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('drivers.index')}}">
                        <span class="number counter">{{$driver_count}}</span>
                        <span class="name">Drivers</span>
                        <span class="bg-icon"><i class="fas fa-users"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

            </div>
            <br>
            @endif

            @if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('customers.index')}}">
                        <span class="number counter">{{$customer_count}}</span>
                        <span class="name">Customers</span>
                        <span class="bg-icon"><i class="fa fa-building"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>   
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('invoices.index')}}">
                        <span class="number counter">{{$invoice_count}}</span>
                        <span class="name">Invoices</span>
                        <span class="bg-icon"><i class="fa fa-list"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>   
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('vendors.index')}}">
                        <span class="number counter">{{$vendor_count}}</span>
                        <span class="name">Vendors</span>
                        <span class="bg-icon"><i class="fas fa-building"></i></span>
                    </a>

                </div>

                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('bills.index')}}">
                        <span class="number counter">{{$bill_count}}</span>
                        <span class="name">Bills</span>
                        <span class="bg-icon"><i class="fa fa-list"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>  
            </div>
        

            @endif 
        <br>

        @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
        @if (!Auth::user()->driver)
            <div class="row">
                 <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('transporters.index')}}">
                        <span class="number counter">{{$transporter_count}}</span>
                        <span class="name">Transporters</span>
                        <span class="bg-icon"><i class="fa fa-building-o"></i></span>
                    </a>

                    <!-- /.src-code -->
                </div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('horses.index')}}">
                        <span class="number counter">{{$horse_count}}</span>
                        <span class="name">Horses</span>
                        <span class="bg-icon"><i class="fas fa-truck"></i></span>
                    </a>

                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
                  <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-default" href="{{route('assignments.index')}}">
                        <span class="number counter">{{$assignment_count}}</span>
                        <span class="name">Assignments</span>
                        <span class="bg-icon"><i class="fa fa-tasks"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>

                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('trailers.index')}}">
                        <span class="number counter">{{$trailer_count}}</span>
                        <span class="name">Trailers</span>
                        <span class="bg-icon"><i class="fa fa-trailer"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('vehicles.index')}}">
                        <span class="number counter">{{$vehicle_count}}</span>
                        <span class="name">Vehicles</span>
                        <span class="bg-icon"><i class="fa fa-car"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>
              
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-secondary" href="{{route('checklists.index')}}">
                        <span class="number counter">{{$inspection_count}}</span>
                        <span class="name">Fleet Inspections</span>
                        <span class="bg-icon"><i class="fa fa-search"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->


                    <!-- /.src-code -->
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

               
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

            </div>
            <br>
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('shifts.index')}}">
                        <span class="number counter">{{$shift_count}}</span>
                        <span class="name">Shifts</span>
                        <span class="bg-icon"><i class="fa fa-clock"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('trips.index')}}">
                        <span class="number counter">{{$trip_count}}</span>
                        <span class="name">Trips</span>
                        <span class="bg-icon"><i class="fas fa-road"></i></span>
                    </a>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('containers.index')}}">
                        <span class="number counter">{{$fuel_supplier_count}}</span>
                        <span class="name">Fueling Stations</span>
                        <span class="bg-icon"><i class="fas fa-gas-pump"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('fuels.index')}}">
                        <span class="number counter">{{$fuel_order_count}}</span>
                        <span class="name">Fuel Orders</span>
                        <span class="bg-icon"><i class="fas fa-list"></i></span>
                    </a>
                </div>
               
            </div>
           
            <br>
            @endif
            @endif
          
        @if (in_array('Stores', $department_names) || in_array('Super Admin', $role_names))
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('tyres.index')}}">
                        <span class="number counter">{{$tyre_count}}</span>
                        <span class="name">Tyres</span>
                        <span class="bg-icon"><i class="fas fa-ring"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('inventory_products.index')}}">
                        <span class="number counter">{{$product_count}}</span>
                        <span class="name">Inventory Products</span>
                        <span class="bg-icon"><i class="fas fa-warehouse"></i></span>
                    </a>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('inventory_purchases.index')}}">
                        <span class="number counter">{{$inventory_purchases_count}}</span>
                        <span class="name">Inventory POs</span>
                        <span class="bg-icon"><i class="fas fa-list"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-success" href="{{route('inventory_dispatches.index')}}">
                        <span class="number counter">{{$inventory_dispatches_count}}</span>
                        <span class="name">Inventory Dispatches</span>
                        <span class="bg-icon"><i class="fas fa-list"></i></span>
                    </a>
                    <!-- /.dashboard-stat -->
                    <!-- /.src-code -->
                </div>
            </div>
            
            @endif

            @if (in_array('Workshop', $department_names) || in_array('Super Admin', $role_names))

            <br>
            <div class="row">
                @if ((in_array('Workshop', $department_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <a class="dashboard-stat bg-primary" href="{{route('bookings.index')}}">
                            <span class="number counter">{{$booking_count}}</span>
                            <span class="name">Bookings</span>
                            <span class="bg-icon"><i class="fas fa-edit"></i></span>
                        </a>
                        <!-- /.dashboard-stat -->
                        <!-- /.src-code -->
                    </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <a class="dashboard-stat bg-danger" href="{{route('tickets.index')}}">
                            <span class="number counter">{{$ticket_count}}</span>
                            <span class="name">Tickets</span>
                            <span class="bg-icon"><i class="fas fa-tasks"></i></span>
                        </a>
                        <!-- /.src-code -->
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <a class="dashboard-stat bg-warning" href="{{route('inspections.index')}}">
                            <span class="number counter">{{$inspection_count}}</span>
                            <span class="name">Inspections</span>
                            <span class="bg-icon"><i class="fas fa-tasks"></i></span>
                        </a>
                        <!-- /.src-code -->
                    </div>
                @else
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a class="dashboard-stat bg-primary" href="{{route('tickets.cards', Auth::user()->employee->id)}}">
                            <span class="number counter">{{$my_tickets_count}}</span>
                            <span class="name">My Tickets</span>
                            <span class="bg-icon"><i class="fas fa-file"></i></span>
                        </a>
                        <!-- /.src-code -->
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <a class="dashboard-stat bg-danger" href="{{route('inspections.my-inspections', Auth::user()->employee->id)}}">
                            <span class="number counter">{{$my_inspections_count}}</span>
                            <span class="name">My Inspections</span>
                            <span class="bg-icon"><i class="fas fa-search"></i></span>
                        </a>
                        <!-- /.src-code -->
                    </div>
                @endif
            </div>
            @endif

          
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.section -->

   

      @if ((in_array('Finance', $department_names)) || in_array('Super Admin', $role_names))
    <section class="section pt-10">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Income & Expenses {{$currency_name}} / Month</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="sales_expenses" class="op-chart"></div>
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Income & Expenses {{$currency_name}} / Year</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="sales_expenses_year" class="op-chart"></div>
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
              
                <!-- /.col-md-12 -->
            </div>
    
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
@endif

@if (Auth::user()->driver)
<section class="section ">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>My Trips</h5>
                        </div>
                    </div>
                    <div class="panel-body overflow-x-auto" style="height:550px;">
                        <div class="panel-title">
                            <h5>{{Auth::user()->name}} {{Auth::user()->surname}} Trips ({{date('Y')}})</h5>
                        </div>
                        @livewire('dashboard.driver-trips')
                    </div>
                    <!-- /.panel-body -->

                    <!-- /.src-code -->
                </div>
                <!-- /.panel -->
            </div>

        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
    
@endif

@if (in_array('Finance', $department_names) || in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
@if (!Auth::user()->driver)
<section class="section ">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Trips</h5>
                        </div>
                    </div>
                    <div class="panel-body overflow-x-auto" style="height:550px;">
                        <div class="panel-title">
                            <h5>Latest 5 records</h5>
                        </div>
                        @livewire('dashboard.trips')
                    </div>
                    <!-- /.panel-body -->

                    <!-- /.src-code -->
                </div>
                <!-- /.panel -->
            </div>

            
          
            <div class="col-md-6">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Total trips per month</h5>
                        </div>
                    </div>
                    <div class="panel-body p-20">

                        <div id="total_trips" class="op-chart"></div>

           
                        <!-- /.col-md-12 -->
                    </div>
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-md-8 -->

         
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>


<section class="section ">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Top 5 perfoming drivers in {{date('F')}}</h5>
                        </div>
                    </div>
                    <div class="panel-body overflow-x-auto">
                        <div class="panel-title">
                            <h5>Top 5 records</h5>
                        </div>
                      
                        <table class="table table-striped">
                        <thead>
                            <tr>
                            <th>Employee#</th>
                            <th>Fullname</th>
                            <th>Total Trips</th>
                            <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($top_drivers as $driver)
                            <tr>
                                <td>{{ $driver->employee?->employee_number }}</td>
                                <td>{{ trim(($driver->employee?->name ?? '').' '.($driver->employee?->surname ?? '')) }}</td>
                                <td>{{ $driver->trips_count ? $driver->trips_count . ' Trip(s)' : '' }}</td>
                                <td>
                                @if($company_currency)
                                    {{ $company_currency->name }} {{ $company_currency->symbol }}{{ number_format($driver->total_revenue, 2) }}
                                @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                    <!-- /.panel-body -->

                    <!-- /.src-code -->
                </div>
                <!-- /.panel -->
            </div>

            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Top 5 perfoming horses in {{date('F')}}</h5>
                        </div>
                    </div>
                    <div class="panel-body overflow-x-auto">
                        <div class="panel-title">
                            <h5>Top 5 records</h5>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                <th>Horse#</th>
                                <th>HRN</th>
                                <th>Total Trips</th>
                                <th>Fuel Usage</th>
                                <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($top_horses as $horse)
                                <tr>
                                    <td>{{ $horse->horse_number ?? '' }}</td>

                                    <td>
                                    {{ $horse->horse_make?->name ?? '' }}
                                    {{ $horse->horse_model?->name ?? '' }}
                                    {{ $horse->registration_number ?? '' }}
                                    {{ $horse->fleet_number ? '(' . $horse->fleet_number . ')' : '' }}
                                    </td>

                                    <td>{{ $horse->trips_count ? $horse->trips_count . ' Trip(s)' : '' }}</td>

                                    <td>{{ $horse->fuel_usage ? number_format($horse->fuel_usage, 2) . ' Litre(s)' : '' }}</td>

                                    <td>
                                    @if($company_currency && $horse->total_revenue)
                                        {{ $company_currency->name }} {{ $company_currency->symbol }}{{ number_format($horse->total_revenue, 2) }}
                                    @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                    </div>
                    <!-- /.panel-body -->

                    <!-- /.src-code -->
                </div>
                <!-- /.panel -->
            </div>
            <!-- /.col-md-8 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>

<section class="section ">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="panel border-primary no-border border-3-top">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Distance Travelled / Month</h5>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="kilometers_moved" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        <!-- /.src-code -->
                    </div>
                </div>
                <!-- /.panel -->
            </div>

            <div class="col-md-6">
                <div class="panel border-primary no-border border-3-top">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Volume & Tonnage Moved / Month</h5>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="volume_tonnage" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        <!-- /.src-code -->
                    </div>
                </div>
                <!-- /.panel -->
            </div>

            <!-- /.col-md-8 -->

            <!-- /.col-md-4 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>

<section class="section ">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="panel border-primary no-border border-3-top">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Volume & Tonnage Loss / Month</h5>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="trip_loss" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                        <!-- /.src-code -->
                    </div>
                </div>
                <!-- /.panel -->
            </div>
            <div class="col-md-6">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h5>Transporters</h5>
                        </div>
                    </div>
                    <div class="panel-body overflow-x-auto">
                        <div class="panel-title">
                            <h5>Latest 5 records</h5>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Transporter#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phonenumber</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transporters as $transporter)
                                <tr>
                                    <td>{{$transporter->transporter_number}}</td>
                                    <td>{{$transporter->name}}</td>
                                    <td>{{$transporter->email}}</td>
                                    <td>{{$transporter->phonenumber}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.panel-body -->

                    <!-- /.src-code -->
                </div>
                <!-- /.panel -->
            </div>
          

            <!-- /.col-md-8 -->

         
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>



@endif
@endif
@if (in_array('Human Resources', $department_names) || in_array('Super Admin', $role_names))
    <section class="section ">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-8">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Employees</h5>
                             
                            </div>
                        </div>
                        <div class="panel-body overflow-x-auto">
                            <div class="panel-title">
                                <h5>Latest 5 records</h5>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee#</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent_employees as $employee)
                                    <tr>
                                        <td>{{$employee->employee_number}}</td>
                                        <td>{{$employee->name}}</td>
                                        <td>{{$employee->surname}}</td>
                                        <td>{{$employee->post}}</td>
                                        <td>
                                            @if ($employee->departments->count()>0)
                                            {{$employee->departments->first()->name}}     
                                            @endif
                                           
                                           </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.panel-body -->

                        <!-- /.src-code -->
                    </div>
                    <!-- /.panel -->
                </div>

                <!-- /.col-md-8 -->

              

                <div class="col-md-4">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Department Heads</h5>
                            </div>
                        </div>
                        <div class="panel-body overflow-x-auto">
                            @if ($hods->count()>0)
                            @foreach ($hods as $hod)
                            @php
                                $employee = App\Models\Employee::find($hod->employee_id);
                                $department = App\Models\Department::find($hod->department_id);
                            @endphp
                            <div class="col-xs-12 p-n">
                                <div class="col-xs-6 p-n">
                                    {{$employee ? $employee->name : "Eployee Record Not Found"}} {{$employee ? $employee->surname : ""}}
                                </div>
                                <!-- /.col-md-6 -->
                                <div class="col-xs-6 p-n">
                                   {{$department ? $department->name : ""}}
                                </div>
                            </div>
                            @endforeach
                            @endif

                        <!-- /.col-xs-12 -->

                        <!-- /.col-xs-12 -->

                        <!-- /.col-xs-12 -->

                        <!-- /.col-xs-12 -->

                            <!-- /.col-xs-12 -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                </div>
                <!-- /.col-md-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

   




    <section class="section ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Employee Labour Turnover</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="labour_tunover" class="op-chart"></div>
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>

                <!-- /.col-md-8 -->

                <div class="col-md-6">

                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Employee Gender Ratio</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="gender" class="op-chart"></div>
                        </div>
                    </div>
                </div>

     
                <!-- /.col-md-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    @endif
    @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
    @if (!Auth::user()->driver)
    <section class="section">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-6">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Fuel Orders</h5>
                            </div>
                        </div>
                        <div class="panel-body overflow-x-auto" style="overflow-x:auto; width:100%; height:540px;">
                            <div class="panel-title">
                                <h5>Latest 5 records</h5>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order#</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th >Station
                                        </th>
                                        <th >FillUp
                                        </th>
                                        <th >Quantity
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fuel_orders as $fuel)
                                    <tr>
                                        <td>{{$fuel->order_number}}</td>
                                        <td>
                                            @php
                                            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                            @endphp
                                            @if ((preg_match($pattern, $fuel->date)) )
                                                {{ \Carbon\Carbon::parse($fuel->date)->format('d M Y g:i A')}}
                                            @else
                                            {{$fuel->date}}
                                            @endif    
                                           </td>
                                        <td>
                                            @if ($fuel->horse)
                                            Horse | {{$fuel->horse ? $fuel->horse->registration_number : ""}} {{$fuel->horse ? "| ".$fuel->horse->fleet_number : ""}} | {{$fuel->horse->horse_make ? $fuel->horse->horse_make->name : ""}} {{$fuel->horse->horse_model ? $fuel->horse->horse_model->name : ""}} 
                                            @if (isset($fuel->trip))
                                            <br>
                                                @php
                                                    $from = App\Models\Destination::find($fuel->trip->from);
                                                    $to = App\Models\Destination::find($fuel->trip->from);
                                                @endphp
                                                  Trip | {{$fuel->trip ? $fuel->trip->trip_number : ""}}{{$fuel->trip->trip_ref ? "/".$fuel->trip->trip_ref : ""}}
                                                @if (isset($from))
                                                    {{$from->country ? $from->country->name : ""}}   {{$from->city}} - 
                                                @endif
                                                @if (isset($to))
                                                    {{$to->country ? $from->country->name : ""}} {{$to->city}}
                                                @endif
                                        
                                            @endif
                                            @elseif($fuel->asset)
                                                Asset | {{$fuel->asset->product->brand ? $fuel->asset->product->brand->name : ""}} {{$fuel->asset->product ? $fuel->asset->product->name : ""}}
                                            @elseif($fuel->vehicle) 
                                                Vehicle | {{  $fuel->vehicle ? $fuel->vehicle->registration_number : "" }} {{$fuel->vehicle->vehicle_make ? $fuel->vehicle->vehicle_make->name : ""}} {{$fuel->vehicle->vehicle_model ? $fuel->vehicle->vehicle_model->name : ""}} 
                                            @endif
                                          </td>
                                          <td>{{ucfirst($fuel->container ? $fuel->container->name : "")}}</td>
                                          <td>{{$fuel->fillup == "1" ? "Initial" : ($fuel->fillup == "0" ? "Top Up" : "")}}</td>
                                          <td>{{$fuel->quantity}}Litres</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.panel-body -->

                        <!-- /.src-code -->
                    </div>
                    <!-- /.panel -->
                </div>

                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Fuel Distribution</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="fuel_chart" class="op-chart"></div>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

              

            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

    <section class="section ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Fuel Orders (Initial & Topup)</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">
    
                            <div id="chart6" class="op-chart"></div>
    
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
                <div class="col-md-4">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Fuel Stations Balances</h5>
                             
                            </div>
                        </div>
                        <div class="panel-body overflow-x-auto" style="overflow-x:auto; width:100%; height:540px;">
                            <div class="panel-title">
                                <h5>Latest 5 records</h5>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Station</th>
                                        <th>Purchase Type</th>
                                        <th>Capacity</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($containers as $container)
                                    <tr>
                                        <td>{{$container->name}}</td>
                                        <td>{{$container->purchase_type}}</td>
                                        <td>{{$container->capacity ? $container->capacity."l" : ""}}</td>
                                        <td>{{$container->balance ? $container->balance."l" : ""}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.panel-body -->
    
                        <!-- /.src-code -->
                    </div>
                    <!-- /.panel -->
                </div>
              
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

    @endif
    @endif

    @if (in_array('Stores', $department_names) || in_array('Workshop', $department_names) || in_array('Super Admin', $role_names))

    <section class="section ">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-6">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Garage Bookings</h5>
                            </div>
                        </div>
                        <div class="panel-body overflow-x-auto" >
                            <div class="panel-title">
                                <h5>Latest 5 records</h5>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Booking#
                                        </th>
                                        <th class="th-sm">Booking For
                                        </th>
                                        <th class="th-sm">Service Type
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Status
                                        </th>
                                      </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                    <tr>
                                        <td>{{ucfirst($booking->booking_number)}}</td>
                                        <td>
                                            @if (isset($booking->horse))
                                            Horse | {{ucfirst($booking->horse->horse_make ? $booking->horse->horse_make->name : "")}} {{ucfirst($booking->horse->horse_model ? $booking->horse->horse_model->name : "" )}} {{ucfirst($booking->horse ? $booking->horse->registration_number : "")}} {{ucfirst($booking->horse ? "| ".$booking->horse->fleet_number : "")}}
                                            @elseif(isset($booking->vehicle))
                                            Vehicle | {{ucfirst($booking->vehicle->vehicle_make ? $booking->vehicle->vehicle_make->name : "")}} {{ucfirst($booking->vehicle->vehicle_model ? $booking->vehicle->vehicle_model->name : "")}} {{ucfirst($booking->vehicle ? $booking->vehicle->registration_number : "")}} {{ucfirst($booking->vehicle ? "| ".$booking->vehicle->fleet_number : "")}}
                                            @elseif(isset($booking->trailer))
                                            Trailer | {{ucfirst($booking->trailer ? $booking->trailer->make : "")}} {{ucfirst($booking->trailer ? $booking->trailer->model : "")}} {{ucfirst($booking->trailer ? $booking->trailer->registration_number : "")}} {{ucfirst($booking->trailer ? "| ".$booking->trailer->fleet_number : "")}}
                                            @endif
                                        </td>
                                        <td>{{ucfirst($booking->service_type ? $booking->service_type->name : "")}}</td>
                                        <td>{{$booking->in_date}} @ {{$booking->in_time}}</td>
                                        <td><span class="badge bg-{{$booking->status == 1 ? "warning" : "success"}}">{{$booking->status == 1 ? "Open" : "Closed"}}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.panel-body -->
                       
                        <!-- /.src-code -->
                    </div>
                    <!-- /.panel -->
                </div>
              
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Garage Bookings Status</h5>
                            </div>
                        </div>
                        <div class="panel-body p-20">

                            <div id="bookings_chart" class="op-chart"></div>
                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                    <!-- /.panel -->
                </div>
              
                

                <!-- /.col-md-8 -->

                <!-- /.col-md-4 -->
            </div>
            
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>

    @endif

    

                <!-- /.col-md-8 -->

                <!-- /.col-md-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
</div>

@section('extra-js')

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/funnel.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>


<script src="{{asset('js/prism/prism.js')}}"></script>
<script src="{{asset('js/amcharts/amcharts.js')}}"></script>
<script src="{{asset('js/amcharts/serial.js')}}"></script>
<script src="{{asset('js/amcharts/pie.js')}}"></script>
<script src="{{asset('js/amcharts/plugins/animate/animate.min.js')}}"></script>
<script src="{{asset('js/amcharts/plugins/export/export.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('js/amcharts/plugins/export/export.css')}}" type="text/css" media="all" />
<script src="{{asset('js/amcharts/themes/light.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
<script src="{{asset('js/chartjs/utils.js')}}"></script>
<script src="{{asset('js/chartjs/globalchartjs.js')}}"></script>

<script>

var MONTHSbar = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        var color = Chart.helpers.color;
        var barChartData = {
            labels: ["January", "February", "March", "April", "May", "June", "July"],
            datasets: [{
                label: 'Dataset 1',
                backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
                borderColor: window.chartColors.red,
                borderWidth: 1,
                data: [
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor()
                ]
            }, {
                label: 'Dataset 2',
                backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
                borderColor: window.chartColors.blue,
                borderWidth: 1,
                data: [
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor()
                ]
            }]

        };



        document.getElementById('randomizeDataBar').addEventListener('click', function() {
            var zero = Math.random() < 0.2 ? true : false;
            barChartData.datasets.forEach(function(dataset) {
                dataset.data = dataset.data.map(function() {
                    return zero ? 0.0 : randomScalingFactor();
                });

            });
            window.myBar.update();
        });

        var colorNamesbar = Object.keys(window.chartColors);
        document.getElementById('addDatasetBar').addEventListener('click', function() {
            var colorName = colorNamesbar[barChartData.datasets.length % colorNamesbar.length];;
            var dsColor = window.chartColors[colorName];
            var newDataset = {
                label: 'Dataset ' + barChartData.datasets.length,
                backgroundColor: color(dsColor).alpha(0.5).rgbString(),
                borderColor: dsColor,
                borderWidth: 1,
                data: []
            };

            for (var index = 0; index < barChartData.labels.length; ++index) {
                newDataset.data.push(randomScalingFactor());
            }

            barChartData.datasets.push(newDataset);
            window.myBar.update();
        });

        document.getElementById('addData').addEventListener('click', function() {
            if (barChartData.datasets.length > 0) {
                var month = MONTHSbar[barChartData.labels.length % MONTHSbar.length];
                barChartData.labels.push(month);

                for (var index = 0; index < barChartData.datasets.length; ++index) {
                    //window.myBar.addData(randomScalingFactor(), index);
                    barChartData.datasets[index].data.push(randomScalingFactor());
                }

                window.myBar.update();
            }
        });

        document.getElementById('removeDataset').addEventListener('click', function() {
            barChartData.datasets.splice(0, 1);
            window.myBar.update();
        });

        document.getElementById('removeData').addEventListener('click', function() {
            barChartData.labels.splice(-1, 1); // remove the label first

            barChartData.datasets.forEach(function(dataset, datasetIndex) {
                dataset.data.pop();
            });

            window.myBar.update();
        });



</script>

<script>

      $(function($) {

        var bookings_chart = AmCharts.makeChart("bookings_chart", {
                    "type": "serial",
                	"theme": "light",
                    "fontFamily": "Poppins",
                    "legend": {
                        "horizontalGap": 10,
                        "maxColumns": 1,
                        "position": "right",
                		"useGraphSettings": true,
                		"markerSize": 10
                    },
                    "dataProvider": [
                        {
                        "month": "Jan",
                        "open": {{$jan_open_bookings}},
                        "closed": {{$jan_closed_bookings}},
                    },
                        {
                        "month": "Feb",
                        "open": {{$feb_open_bookings}},
                        "closed": {{$feb_closed_bookings}},
                    },
                        {
                        "month": "Mar",
                        "open": {{$mar_open_bookings}},
                        "closed": {{$mar_closed_bookings}},
                    },
                        {
                        "month": "Apr",
                        "open": {{$apr_open_bookings}},
                        "closed": {{$apr_closed_bookings}},
                    },
                        {
                        "month": "May",
                        "open": {{$may_open_bookings}},
                        "closed": {{$may_closed_bookings}},
                    },
                        {
                        "month": "Jun",
                        "open": {{$jun_open_bookings}},
                        "closed": {{$jun_closed_bookings}},
                    },
                        {
                        "month": "Jul",
                        "open": {{$jul_open_bookings}},
                        "closed": {{$jul_closed_bookings}},
                    },
                        {
                        "month": "Aug",
                        "open": {{$aug_open_bookings}},
                        "closed": {{$aug_closed_bookings}},
                    },
                        {
                        "month": "Sept",
                        "open": {{$sep_open_bookings}},
                        "closed": {{$sep_closed_bookings}},
                    },
                        {
                        "month": "Oct",
                        "open": {{$oct_open_bookings}},
                        "closed": {{$oct_closed_bookings}},
                    },
                        {
                        "month": "Nov",
                        "open": {{$nov_open_bookings}},
                        "closed": {{$nov_closed_bookings}},
                    },
                        {
                        "month": "Dec",
                        "open": {{$dec_open_bookings}},
                        "closed": {{$dec_closed_bookings}},
                    },
                ],
                    "valueAxes": [{
                        "stackType": "regular",
                        "axisAlpha": 0.3,
                        "gridAlpha": 0
                    }],
                    "graphs": [{
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "Closed",
                        "type": "column",
                		"color": "#000000",
                        "valueField": "closed"
                    }, {
                        "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[value]]</b></span>",
                        "fillAlphas": 0.8,
                        "labelText": "[[value]]",
                        "lineAlpha": 0.3,
                        "title": "Open",
                        "type": "column",
                		"color": "#000000",
                        "valueField": "open"
                    },],
                    "categoryField": "month",
                    "categoryAxis": {
                        "gridPosition": "start",
                        "axisAlpha": 0,
                        "gridAlpha": 0,
                        "position": "left"
                    },
                    "export": {
                    	"enabled": true
                     }

                });


        var chart6 = AmCharts.makeChart("chart6", {
                    "theme": "light",
                    "type": "serial",
                    "fontFamily": "Poppins",
                    "dataProvider": [{
                        "month": "Jan",
                        "topup": {{$jan_topup_fuel}},
                        "initial": {{$jan_initial_fuel}}
                       
                    }, 
                    {
                        "month": "Feb",
                        "topup": {{$feb_topup_fuel}},
                        "initial": {{$feb_initial_fuel}}
                       
                    }, {
                        "month": "Mar",
                        "topup": {{$mar_topup_fuel}},
                        "initial": {{$mar_initial_fuel}}
                        
                    }, {
                        "month": "Apr",
                        "topup": {{$apr_topup_fuel}},
                        "initial": {{$apr_initial_fuel}}
                       
                    }, {
                        "month": "May",
                        "topup": {{$may_topup_fuel}},
                        "initial": {{$may_initial_fuel}}
                       
                    }, {
                        "month": "Jun",
                        "topup": {{$jun_topup_fuel}},
                        "initial": {{$jun_initial_fuel}}
                       
                    }, {
                        "month": "Jul",
                        "topup": {{$jul_topup_fuel}},
                        "initial": {{$jul_initial_fuel}}
                       
                    }, {
                        "month": "Aug",
                        "topup": {{$aug_topup_fuel}},
                        "initial": {{$aug_initial_fuel}}
                       
                    }, 
                    {
                        "month": "Sep",
                        "topup": {{$sep_topup_fuel}},
                        "initial": {{$sep_initial_fuel}}
                      
                    },
                    {
                        "month": "Oct",
                        "topup": {{$oct_topup_fuel}},
                        "initial": {{$oct_initial_fuel}}
                       
                    },
                    {
                        "month": "Nov",
                        "topup": {{$nov_topup_fuel}},
                        "initial": {{$nov_initial_fuel}}
                      
                    },
                    {
                        "month": "Dec",
                        "topup": {{$dec_topup_fuel}},
                        "initial": {{$dec_initial_fuel}}
                       
                    },
                ],
                    "valueAxes": [{
                        "stackType": "3d",
                        // "unit": "L",
                        "position": "left",
                        "title": "Fuel Quantity in Litres",
                    }],
                    "startDuration": 1,
                    "graphs": [
                        {
                        "balloonText": "Topup Fuel Quantity [[category]]: <b>[[value]]</b>",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "title": "Topup",
                        "type": "column",
                        "valueField": "topup"
                    },
                        {
                        "balloonText": "Initial Fuel Quantity [[category]]: <b>[[value]]</b>",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "title": "Initial",
                        "type": "column",
                        "valueField": "initial"
                    } ],
                    "plotAreaFillAlphas": 0.1,
                    "depth3D": 60,
                    "angle": 30,
                    "categoryField": "month",
                    "categoryAxis": {
                        "gridPosition": "start"
                    },
                    "export": {
                    	"enabled": true
                     }
                });

        var sales_expenses_year = AmCharts.makeChart( "sales_expenses_year", {
                  "type": "serial",
                  "addClassNames": true,
                  "theme": "light",
                  "autoMargins": false,
                  "marginLeft": 80,
                  "marginRight": 8,
                  "marginTop": 10,
                  "marginBottom": 56,
                  "fontFamily": "Poppins",
                  "balloon": {
                    "adjustBorderColor": false,
                    "horizontalPadding": 10,
                    "verticalPadding": 8,
                    "color": "#ffffff"
                  },

                  "dataProvider": [ 
                    {
                    "year": 2021,
                    "income": {{$income_2021}},
                    "expenses": {{$expenses_2021}}
                  }, 
                    {
                    "year": 2022,
                    "income": {{$income_2022}},
                    "expenses": {{$expenses_2022}}
                  }, 
                    {
                    "year": 2023,
                    "income": {{$income_2023}},
                    "expenses": {{$expenses_2023}}
                  }, 
                    {
                    "year": 2024,
                    "income": {{$income_2024}},
                    "expenses": {{$expenses_2024}},
                    "dashLengthLine": 5
                  },
                  {
                    "year": 2025,
                    "income": {{$income_2025}},
                    "expenses": {{$expenses_2025}},
                    "dashLengthLine": 5
                  } 
                  ],
                  "valueAxes": [ {
                    "axisAlpha": 0,
                    "position": "left",
                    "title": "Amount"
                  } ],
                  "startDuration": 1,
                  "graphs": [ {
                    "alphaField": "alpha",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "fillAlphas": 1,
                    "title": "Income",
                    "type": "column",
                    "valueField": "income",
                    "dashLengthField": "dashLengthColumn"
                  }, {
                    "id": "graph2",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "bullet": "round",
                    "lineThickness": 3,
                    "bulletSize": 7,
                    "bulletBorderAlpha": 1,
                    "bulletColor": "#FFFFFF",
                    "useLineColorForBulletBorder": true,
                    "bulletBorderThickness": 3,
                    "fillAlphas": 0,
                    "lineAlpha": 1,
                    "title": "Expenses",
                    "valueField": "expenses",
                    "dashLengthField": "dashLengthLine"
                  } ],
                  "categoryField": "year",
                  "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "tickLength": 0,
                     "title": "Months"
                  },
                  "export": {
                    "enabled": true
                  }
                } );


          var sales_expenses = AmCharts.makeChart( "sales_expenses", {
                  "type": "serial",
                  "addClassNames": true,
                  "theme": "light",
                  "autoMargins": false,
                  "marginLeft": 80,
                  "marginRight": 8,
                  "marginTop": 10,
                  "marginBottom": 56,
                  "fontFamily": "Poppins",
                  "balloon": {
                    "adjustBorderColor": false,
                    "horizontalPadding": 10,
                    "verticalPadding": 8,
                    "color": "#ffffff"
                  },

                  "dataProvider": [ 
                    {
                    "month": 'Jan',
                    "income": {{$jan}},
                    "expenses": {{$jan_expense}}
                  },
                    {
                    "month": 'Feb',
                    "income": {{$feb}},
                    "expenses": {{$feb_expense}}
                  },
                    {
                    "month": 'Mar',
                    "income": {{$mar}},
                    "expenses": {{$mar_expense}}
                  },
                    {
                    "month": 'Apr',
                    "income": {{$apr}},
                    "expenses": {{$apr_expense}}
                  },
                    {
                    "month": 'May',
                    "income": {{$may}},
                    "expenses": {{$may_expense}}
                  },
                   
                    {
                    "month": 'Jun',
                    "income": {{$jun}},
                    "expenses": {{$jun_expense}}
                  },
                    {
                    "month": 'Jul',
                    "income": {{$jul}},
                    "expenses": {{$jul_expense}}
                  },
                    {
                    "month": 'Aug',
                    "income": {{$aug}},
                    "expenses": {{$aug_expense}}
                  },
                    {
                    "month": 'Sep',
                    "income": {{$sep}},
                    "expenses": {{$sep_expense}}
                  },
                    {
                    "month": 'Oct',
                    "income": {{$oct}},
                    "expenses": {{$oct_expense}}
                  },
                    {
                    "month": 'Nov',
                    "income": {{$nov}},
                    "expenses": {{$nov_expense}}
                  },
                    {
                    "month": 'Dec',
                    "income": {{$dec}},
                    "expenses": {{$dec_expense}}
                  },
                
                ],
                  "valueAxes": [ {
                    "axisAlpha": 0,
                    "position": "left",
                     "title": "Amount"
                  } ],
                  "startDuration": 1,
                  "graphs": [ {
                    "alphaField": "alpha",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "fillAlphas": 1,
                    "title": "Income",
                    "type": "column",
                    "valueField": "income",
                    "dashLengthField": "dashLengthColumn"
                  }, {
                    "id": "graph2",
                    "balloonText": "<span style='font-size:12px;'>[[title]] in [[category]]:<br><span style='font-size:20px;'>[[value]]</span> [[additional]]</span>",
                    "bullet": "round",
                    "lineThickness": 3,
                    "bulletSize": 7,
                    "bulletBorderAlpha": 1,
                    "bulletColor": "#FFFFFF",
                    "useLineColorForBulletBorder": true,
                    "bulletBorderThickness": 3,
                    "fillAlphas": 0,
                    "lineAlpha": 1,
                    "title": "Expenses",
                    "valueField": "expenses",
                    "dashLengthField": "dashLengthLine"
                  } ],
                  "categoryField": "month",
                  "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "tickLength": 0,
                     "title": "Months"
                  },
                  "export": {
                    "enabled": true
                  }
                } );



                var labour_tunover = AmCharts.makeChart("labour_tunover", {
                    "type": "serial",
                    "theme": "light",
                    "fontFamily": "Poppins",
                    "marginTop":0,
                    "marginRight": 80,
                    "dataProvider": [{
                        "year": "2022",
                        "value": {{$resignation_2022}}
                    }, 
                    {
                        "year": "2023",
                        "value": {{$resignation_2023}}
                    },
                    {
                        "year": "2024",
                        "value": {{$resignation_2024}}
                    },
                    {
                        "year": "2025",
                        "value": {{$resignation_2025}}
                    }
                ],
                    "valueAxes": [{
                        "axisAlpha": 0,
                        "position": "left"
                    }],
                    "graphs": [{
                        "id":"g1",
                        "balloonText": "[[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
                        "bullet": "round",
                        "bulletSize": 8,
                        "lineColor": "#d1655d",
                        "lineThickness": 2,
                        "negativeLineColor": "#637bb6",
                        "type": "smoothedLine",
                        "valueField": "value"
                    }],
                    "chartScrollbar": {
                        "graph":"g1",
                        "gridAlpha":0,
                        "color":"#888888",
                        "scrollbarHeight":55,
                        "backgroundAlpha":0,
                        "selectedBackgroundAlpha":0.1,
                        "selectedBackgroundColor":"#888888",
                        "graphFillAlpha":0,
                        "autoGridCount":true,
                        "selectedGraphFillAlpha":0,
                        "graphLineAlpha":0.2,
                        "graphLineColor":"#c2c2c2",
                        "selectedGraphLineColor":"#888888",
                        "selectedGraphLineAlpha":1

                    },
                    "chartCursor": {
                        "categoryBalloonDateFormat": "YYYY",
                        "cursorAlpha": 0,
                        "valueLineEnabled":true,
                        "valueLineBalloonEnabled":true,
                        "valueLineAlpha":0.5,
                        "fullWidth":true
                    },
                    "dataDateFormat": "YYYY",
                    "categoryField": "year",
                    "categoryAxis": {
                        "minPeriod": "YYYY",
                        "parseDates": true,
                        "minorGridAlpha": 0.1,
                        "minorGridEnabled": true
                    },
                    "export": {
                        "enabled": true
                    }

                });
                labour_tunover.addListener("rendered", zoomChart);
                if(labour_tunover.zoomChart){
                	labour_tunover.zoomChart();
                }

                function zoomChart(){
                    labour_tunover.zoomToIndexes(Math.round(labour_tunover.dataProvider.length * 0.1), Math.round(labour_tunover.dataProvider.length * 0.8));
                }        
                

    var gender = AmCharts.makeChart( "gender", {
                  "type": "pie",
                  "theme": "light",
                  "fontFamily": "Poppins",
                  "dataProvider": [ {
                    "gender": "Male",
                    "value": {{$males}}
                  }, {
                    "gender": "Female",
                    "value": {{$females}}
                  },  ],
                  "valueField": "value",
                  "titleField": "gender",
                  "outlineAlpha": 0.4,
                  "depth3D": 15,
                  "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
                  "angle": 30,
                  "export": {
                    "enabled": true
                  }
                } );


    var chart = AmCharts.makeChart("total_trips", {
                  "type": "serial",
                  "theme": "light",
                  "fontFamily": "Poppins",
                  "marginRight": 70,
                  "dataProvider": [{
                    "month": "Jan",
                    "trips": {{$jan_trips}},
                    "color": "#FF0F00"
                  }, {
                    "month": "Feb",
                    "trips": {{$feb_trips}},
                    "color": "#FF6600"
                  }, {
                    "month": "Mar",
                    "trips": {{$mar_trips}},
                    "color": "#FF9E01"
                  }, {
                    "month": "Apr",
                    "trips": {{$apr_trips}},
                    "color": "#FCD202"
                  }, {
                    "month": "May",
                    "trips": {{$may_trips}},
                    "color": "#F8FF01"
                  }, {
                    "month": "Jun",
                    "trips": {{$jun_trips}},
                    "color": "#B0DE09"
                  }, {
                    "month": "Jul",
                    "trips": {{$jul_trips}},
                    "color": "#04D215"
                  }, {
                    "month": "Aug",
                    "trips": {{$aug_trips}},
                    "color": "#0D8ECF"
                  }, {
                    "month": "Sep",
                    "trips": {{$sep_trips}},
                    "color": "#0D52D1"
                  }, {
                    "month": "Oct",
                    "trips": {{$oct_trips}},
                    "color": "#2A0CD0"
                  }, {
                    "month": "Nov",
                    "trips": {{$nov_trips}},
                    "color": "#8A0CCF"
                  }, {
                    "month": "Dec",
                    "trips": {{$dec_trips}},
                    "color": "#CD0D74"
                  }],
                  "valueAxes": [{
                    "axisAlpha": 0,
                    "position": "left",
                    "title": "Total trips per month"
                  }],
                  "startDuration": 1,
                  "graphs": [{
                    "balloonText": "<b>[[category]]: [[value]]</b>",
                    "fillColorsField": "color",
                    "fillAlphas": 0.9,
                    "lineAlpha": 0.2,
                    "type": "column",
                    "valueField": "trips"
                  }],
                  "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                  },
                  "categoryField": "month",
                  "categoryAxis": {
                    "gridPosition": "start",
                    "labelRotation": 45
                  },
                  "export": {
                    "enabled": true
                  }

                });

                var fuel_chart = AmCharts.makeChart("fuel_chart", {
                    "type": "pie",
                    "theme": "light",
                    "fontFamily": "Poppins",
                    "innerRadius": "40%",
                    "gradientRatio": [-0.4, -0.4, -0.4, -0.4, -0.4, -0.4, 0, 0.1, 0.2, 0.1, 0, -0.2, -0.5],
                    "dataProvider": [{
                        "Fuel Type": "Petrol",
                        "litres": {{$petrol_quantity}}
                    }, {
                        "Fuel Type": "Diesel",
                        "litres": {{$diesel_quantity}}
                    }],
                    "balloonText": "[[value]]",
                    "valueField": "litres",
                    "titleField": "Fuel Type",
                    "balloon": {
                        "drop": true,
                        "adjustBorderColor": false,
                        "color": "#FFFFFF",
                        "fontSize": 16
                    },
                    "export": {
                        "enabled": true
                    }
                });

            });
</script>


<script>
    Highcharts.setOptions({
    global: {
        useUTC: false
    },
    chart: {
        style: {
            fontFamily: 'Poppins'
        }
    }
});


Highcharts.chart('kilometers_moved', {
    chart: {
        zoomType: 'xy'
    },
    title: {
        text: 'Monthly Distance Travelled (Kms)'
    },
   
    xAxis: [{
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        crosshair: true
    }],
    yAxis: [{ // Primary yAxis
        labels: {
            format: '{value} Kms',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        title: {
            text: 'Distance (Kms)',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        }
    }],
    tooltip: {
        shared: true
    },
    legend: {
        layout: 'vertical',
        align: 'left',
        x: 120,
        verticalAlign: 'top',
        y: 100,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
    },
    series: [ {
        name: 'Kilometers',
        type: 'spline',
        data: [
            {{$jan_distance}},
            {{$feb_distance}},
            {{$mar_distance}},
            {{$apr_distance}},
            {{$may_distance}},
            {{$jun_distance}},
            {{$jul_distance}},
            {{$aug_distance}},
            {{$sep_distance}},
            {{$oct_distance}},
            {{$nov_distance}},
            {{$dec_distance}},
          ],
        tooltip: {
            valueSuffix: 'Kms'
        }
    }]
});

Highcharts.chart('volume_tonnage', {
    chart: {
        zoomType: 'xy'
    },
    title: {
        text: 'Monthly Volume & Tonnage Moved'
    },
    
    xAxis: [{
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        crosshair: true
    }],
    yAxis: [{ // Primary yAxis
        labels: {
            format: '{value} Litres',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        title: {
            text: 'Volume',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        }
    }, { // Secondary yAxis
        title: {
            text: 'Tonnage',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        labels: {
            format: '{value} Tons',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        opposite: true
    }],
    tooltip: {
        shared: true
    },
    legend: {
        layout: 'vertical',
        align: 'left',
        x: 120,
        verticalAlign: 'top',
        y: 100,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
    },
    series: [{
        name: 'Tonnage',
        type: 'column',
        yAxis: 1,
        data: [
            {{$jan_weight}},
            {{$feb_weight}},
            {{$mar_weight}},
            {{$apr_weight}},
            {{$may_weight}},
            {{$jun_weight}},
            {{$jul_weight}},
            {{$aug_weight}},
            {{$sep_weight}},
            {{$oct_weight}},
            {{$nov_weight}},
            {{$dec_weight}},
        ],
        tooltip: {
            valueSuffix: ' Tons'
        }

    }, {
        name: 'Volume',
        type: 'spline',
        data: [
            {{$jan_litreage}},
            {{$feb_litreage}},
            {{$mar_litreage}},
            {{$apr_litreage}},
            {{$may_litreage}},
            {{$jun_litreage}},
            {{$jul_litreage}},
            {{$aug_litreage}},
            {{$sep_litreage}},
            {{$oct_litreage}},
            {{$nov_litreage}},
            {{$dec_litreage}},
        ],
        tooltip: {
            valueSuffix: ' Litres'
        }
    }]
});


Highcharts.chart('trip_loss', {
    chart: {
        zoomType: 'xy'
    },
    title: {
        text: 'Monthly Volume & Tonnage Loss'
    },
    
    xAxis: [{
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        crosshair: true
    }],
    yAxis: [{ // Primary yAxis
        labels: {
            format: '{value} Litres',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        title: {
            text: 'Volume',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        }
    }, { // Secondary yAxis
        title: {
            text: 'Tonnage',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        labels: {
            format: '{value} Tons',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        opposite: true
    }],
    tooltip: {
        shared: true
    },
    legend: {
        layout: 'vertical',
        align: 'left',
        x: 120,
        verticalAlign: 'top',
        y: 100,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
    },
    series: [{
        name: 'Tonnage',
        type: 'column',
        yAxis: 1,
        data: [
            {{$jan_weight_loss}},
            {{$feb_weight_loss}},
            {{$mar_weight_loss}},
            {{$apr_weight_loss}},
            {{$may_weight_loss}},
            {{$jun_weight_loss}},
            {{$jul_weight_loss}},
            {{$aug_weight_loss}},
            {{$sep_weight_loss}},
            {{$oct_weight_loss}},
            {{$nov_weight_loss}},
            {{$dec_weight_loss}},
        ],
        tooltip: {
            valueSuffix: ' Tons'
        }

    }, {
        name: 'Volume',
        type: 'spline',
        data: [
            {{$jan_litreage_loss}},
            {{$feb_litreage_loss}},
            {{$mar_litreage_loss}},
            {{$apr_litreage_loss}},
            {{$may_litreage_loss}},
            {{$jun_litreage_loss}},
            {{$jul_litreage_loss}},
            {{$aug_litreage_loss}},
            {{$sep_litreage_loss}},
            {{$oct_litreage_loss}},
            {{$nov_litreage_loss}},
            {{$dec_litreage_loss}},
        ],
        tooltip: {
            valueSuffix: ' Litres'
        }
    }]
});


</script>



@endsection
