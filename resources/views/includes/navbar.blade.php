@php
    $user = Auth::user();
    $employee = $user->employee;
    if (isset($employee->company)) {
        $company = $employee->company;
    }elseif(isset($user->company)) {
        $company = $user->company;
    }
   
    if ($employee) {
        $departments = $employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
    }
    
    $roles = $user->roles;
    foreach($roles as $role){
        $role_names[] = $role->name;
    }
@endphp
<nav class="navbar top-navbar bg-white box-shadow">
    <div class="container-fluid">
        <div class="row">
            <div class="navbar-header no-padding">
                <a class="navbar-brand" href="{{route('dashboard.index')}}">
                    @if (isset($employee))
                    <img src="{{asset('images/uploads/'.$company->logo)}}" alt="{{$company->name}} "  class="logo">
                    @elseif(isset($user->company))
                    <img src="{{asset('images/uploads/'.$user->company->logo)}}" alt="{{$user->company->name}} " class="logo">
                    @elseif(isset($user->transporter))
                    <img src="{{asset('images/uploads/'.$user->transporter->company->logo)}}" alt="{{$user->transporter->company->name}} " class="logo">
                    @elseif(isset($user->customer))
                    <img src="{{asset('images/uploads/'.$user->customer->company->logo)}}" alt="{{$user->customer->company->name}} " class="logo">
                    @elseif(isset($user->agent))
                    <img src="{{asset('images/uploads/'.$user->agent->company->logo)}}" alt="{{$user->agent->company->name}} " class="logo">
                    @endif
                </a>
                <span class="small-nav-handle hidden-sm hidden-xs"><i class="fa fa-outdent"></i></span>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <button type="button" class="navbar-toggle mobile-nav-toggle" >
                    <i class="fa fa-bars"></i>
                </button>
            </div>
            <!-- /.navbar-header -->

            <div class="collapse navbar-collapse" id="navbar-collapse-1">
                <ul class="nav navbar-nav" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    <li class="hidden-sm hidden-xs"><a href="#" class="user-info-handle"><i class="fa fa-user"></i></a></li>
                    <li class="hidden-sm hidden-xs"><a href="#" class="full-screen-handle"><i class="fa fa-arrows-alt"></i></a></li>
                    <li><a href="#">Version: {{config('app.version')}}</a></li>
                   
                </ul>
                <!-- /.nav navbar-nav -->
               
               
                <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    @php
                        // Get the current date and time
                        if ($employee) {
                            $expires = $employee->company->expiry_date; 
                        if (isset($expires)) {
                        $expiry_date = Carbon\Carbon::parse($expires);
                        }else{
                        $expiry_date = Carbon\Carbon::now()->endOfMonth();
                        }

                        $today = Carbon\Carbon::today();
                        // Get the last day of the current month
                    
                        $diff = $today->diffInDays($expiry_date);
                        }
                    @endphp
            
                   
                   @if ($employee)

                    @if (isset($diff))
                        @if ($diff > 7)
                            <li style="color: green; margin:12px" > License expires in {{$diff}} day(s) </li>
                        @elseif ($diff <= 7 && $diff > 1 )
                        <li style="color: orange; margin:12px" > License expires in {{$diff}} day(s) </li>  
                        @elseif($diff == 1 )
                        <li style="color: red; margin:12px" > License expires in {{$diff}} day(s) </li>  
                        @elseif($diff == 0 )
                        <li style="color: red; margin:12px" > License expires today </li>  
                        @else
                        <li style="color: red; margin:12px" > License expired {{Carbon\Carbon::parse($expiry_date)->format('Y-m-d')}} </li>  
                        @endif
                    @endif
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle bg-default tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Create new <span class="caret"></span></a>
                        <ul class="dropdown-menu" style="overflow-x:auto; width:50px; height:400px;">
                            @if (in_array('Human Resources', $department_names)|| in_array('Super Admin', $role_names))
                            <li><a href="{{route('drivers.create')}}"><i class="fa fa-plus-square-o"></i>Driver</a></li>
                            <li><a href="{{route('employees.create')}}"><i class="fa fa-plus-square-o"></i>Employee</a></li>
                            @endif
                            @if (in_array('Finance', $department_names)|| in_array('Super Admin', $role_names))
                            <li><a href="{{route('invoices.create')}}"><i class="fa fa-plus-square-o"></i>Invoice</a></li>
                            <li><a href="{{route('quotations.create')}}"><i class="fa fa-plus-square-o"></i>Quotation</a></li>
                            <li><a href="{{route('assets.create')}}"><i class="fa fa-plus-square-o"></i>Asset</a></li>
                            @endif
                            @if (in_array('Transport & Logistics', $department_names) || in_array('Super Admin', $role_names))
                            <li><a href="{{route('horses.create')}}"><i class="fa fa-plus-square-o"></i>Horse</a></li>
                            <li><a href="{{route('trailers.index')}}"><i class="fa fa-plus-square-o"></i>Trailer</a></li>
                            <li><a href="{{route('trips.create')}}"><i class="fa fa-plus-square-o"></i>Trip</a></li>
                            <li><a href="{{route('vehicles.create')}}"><i class="fa fa-plus-square-o"></i>Vehicle</a></li>
                            @endif
                            @if (in_array('Workshop', $department_names) || in_array('Super Admin', $role_names))
                                <li><a href="{{route('bookings.create')}}"><i class="fa fa-plus-square-o"></i>Booking</a></li>
                            @endif
                            @if (in_array('Stores', $department_names) || in_array('Super Admin', $role_names))
                                <li><a href="{{route('inventories.create')}}"><i class="fa fa-plus-square-o"></i>Inventory</a></li>
                                <li><a href="{{route('tyres.create')}}"><i class="fa fa-plus-square-o"></i>Tyre</a></li>
                            @endif
                        </ul>
                    </li>
                   <li class="dropdown">
                    <a href="#" class="dropdown-toggle bg-primary tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-line-chart"></i> Reports <span class="caret"></span></a>
                    <ul class="dropdown-menu" style="overflow-x:auto; width:50px; height:400px;" >
                        @if ((in_array('Finance', $department_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                        <li><a href="{{route('reports.index')}}"><i class="fa fa-plus-square-o"></i>Financial Statements</a></li>
                        <li><a href="{{route('debtors.reports')}}"><i class="fa fa-plus-square-o"></i>Debtors</a></li>
                        @endif
                        @if ((in_array('Transport & Logistics', $department_names) && in_array('Admin', $role_names)) || in_array('Super Admin', $role_names))
                            @if (!Auth::user()->driver)
                                <li><a href="{{route('trips.reports')}}"><i class="fa fa-plus-square-o"></i>Trips</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#"><strong>Age Pyramids</strong></a></li>
                                <li><a href="{{route('customers.age')}}"><i class="fa fa-plus-square-o"></i>Customers Age</a></li>
                                <li><a href="{{route('drivers.age')}}"><i class="fa fa-plus-square-o"></i>Drivers Age</a></li>
                                <li><a href="{{route('employees.age')}}"><i class="fa fa-plus-square-o"></i>Employees Age</a></li>
                                <li><a href="{{route('horses.age')}}"><i class="fa fa-plus-square-o"></i>Horses Age</a></li>
                                <li><a href="{{route('trailers.age')}}"><i class="fa fa-plus-square-o"></i>Trailers Age</a></li>
                                <li><a href="{{route('vehicles.age')}}"><i class="fa fa-plus-square-o"></i>Vehicles Age</a></li>
                                <li><a href="{{route('vendors.age')}}"><i class="fa fa-plus-square-o"></i>Vendors Age</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#"><strong>Next Service</strong></a></li>
                                <li><a href="{{route('horses.mileage')}}"><i class="fa fa-plus-square-o"></i>Horses</a></li>
                                <li><a href="{{route('trailers.mileage')}}"><i class="fa fa-plus-square-o"></i>Trailers</a></li>
                                <li><a href="{{route('vehicles.mileage')}}"><i class="fa fa-plus-square-o"></i>Vehicles</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#"><strong>Perfomance</strong></a></li>
                                <li><a href="{{route('drivers.performance')}}"><i class="fa fa-plus-square-o"></i>Drivers</a></li>
                                <li><a href="{{route('horses.performance')}}"><i class="fa fa-plus-square-o"></i>Horses</a></li>
                                <li><a href="#"><strong>Horses</strong></a></li>
                                <li><a href="{{route('horses.statement.index')}}"><i class="fa fa-plus-square-o"></i>P & L Statement</a></li>
                                {{-- <li><a href="{{route('trailers.performance')}}"><i class="fa fa-plus-square-o"></i>Trailers</a></li>
                                <li><a href="{{route('vehicles.performance')}}"><i class="fa fa-plus-square-o"></i>Vehicles</a></li> --}}
                            @endif
                        @endif
                        
                       
                    </ul>
                </li>
                <li class="dropdown">
                    @php
                            $reminders = App\Models\Fitness::whereDate('first_reminder_at','<=', Carbon\Carbon::today())
                            ->where('first_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('expires_at','>=', now()->toDateTimeString())
                            ->where('closed', 0)
                            ->orWhereDate('second_reminder_at','<=', Carbon\Carbon::today())
                            ->where('second_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('expires_at','>=', now()->toDateTimeString())
                            ->where('closed', 0)
                            ->orWhereDate('third_reminder_at','<=', Carbon\Carbon::today())
                            ->where('third_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('closed', 0)
                            ->where('expires_at','>=', now()->toDateTimeString())
                            ->get();

                            $reminders_count = App\Models\Fitness::whereDate('first_reminder_at','<=', Carbon\Carbon::today())
                            ->where('first_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('expires_at','>=', now()->toDateTimeString())
                            ->where('closed', 0)
                            ->orWhereDate('second_reminder_at','<=', Carbon\Carbon::today())
                            ->where('second_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('expires_at','>=', now()->toDateTimeString())
                             ->where('closed', 0)
                            ->orWhereDate('third_reminder_at','<=', Carbon\Carbon::today())
                            ->where('third_reminder_at_status', FALSE)
                            ->where('user_id', $user->id)
                            ->where('expires_at','>=', now()->toDateTimeString())
                            ->where('closed', 0)
                            ->orWhereDate('expires_at','<', Carbon\Carbon::today())
                            ->where('user_id', $user->id)
                            ->where('closed', 0)
                            ->get()->count();

                            $expired_reminders = App\Models\Fitness::where('user_id', $user->id)
                            ->where('closed', 0)
                            ->whereDate('expires_at','<', Carbon\Carbon::today())
                            ->get();
                        
                            $horses = App\Models\Horse::all();

                 
                    @endphp
                    <a href="#" class="dropdown-toggle tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bell"></i>
                        @if ($reminders_count > 0)
                        <span class="badge badge-danger">{{ $reminders_count }}</span>
                        @endif
                    </a>
                    @if ($reminders->count() > 10)
                    <ul class="dropdown-menu" style='overflow-x:auto;  height:400px;'  >
                        <li  style="margin: 10px">
                            <center>
                                <li><a href="{{route('reminders.index')}}"><i class="fa fa-plus-square-o"></i> New Reminder</a></li>
                            </center>
                        </li>
                        @if (isset($reminders) && $reminders->count() > 0 )
                        
                            <div class="clearfix"></div>
                            <li role="separator" class="divider"></li>
                          
                            <li>
                                <a href="#"><strong style="color:gray">Valid Reminders</strong></a>
                            </li>
                            @foreach ($reminders as $reminder)
                            <li>
                                <a href="{{ route('fitnesses.show',$reminder->id) }}"><i class="fa fa-bell" style="color: green"></i>{{ $reminder->reminder_item ? $reminder->reminder_item->name : "" }}  
                                    @if ($reminder->horse)
                                    for {{ $reminder->horse ? $reminder->horse->registration_number : "" }}
                                    @elseif ($reminder->vehicle)
                                    for {{ $reminder->vehicle ? $reminder->vehicle->registration_number : "" }}
                                    @elseif ($reminder->trailer)
                                    for  {{ $reminder->trailer ? $reminder->trailer->registration_number : "" }}
                                
                                    @elseif ($reminder->employee)
                                    for  {{ $reminder->employee ? $reminder->employee->name : "" }} {{ $reminder->employee ? $reminder->employee->surname : "" }}
                                    @endif
                                    expires on {{ Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                </a>
                            </li>
                            @endforeach
                        @endif
                        
                       
                        @if (isset($expired_reminders) && $expired_reminders->count() > 0 )
                            <li role="separator" class="divider"></li>
                            <li>
                                <a href="#"><strong style="color:gray">Expired Reminders</strong></a>
                            </li>
                            @foreach ($expired_reminders as $reminder)
                            <li>
                                <a href="{{ route('fitnesses.show',$reminder->id) }}"><i class="fa fa-bell" style="color: red"></i>{{ $reminder->reminder_item ? $reminder->reminder_item->name : "" }}  
                                    @if ($reminder->horse)
                                    for {{ $reminder->horse ? $reminder->horse->registration_number : "" }}
                                    @elseif ($reminder->vehicle)
                                    for {{ $reminder->vehicle ? $reminder->vehicle->registration_number : "" }}
                                    @elseif ($reminder->trailer)
                                    for  {{ $reminder->trailer ? $reminder->trailer->registration_number : "" }}
                                
                                    @elseif ($reminder->employee)
                                    for  {{ $reminder->employee ? $reminder->employee->name : "" }} {{ $reminder->employee ? $reminder->employee->surname : "" }}
                                    @endif
                                    expired on {{ Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                </a>
                            </li>
                            @endforeach  
                        @endif
                        
                    </ul>
                    @else
                    <ul class="dropdown-menu" >
                        <li  style="margin: 10px">
                            <center>
                                <li><a href="{{route('reminders.index')}}"><i class="fa fa-plus-square-o"></i> New Reminder</a></li>
                            </center>
                        </li>
                        @if (isset($reminders) && $reminders->count() > 0 )
                        
                            <div class="clearfix"></div>
                            <li role="separator" class="divider"></li>
                          
                            <li>
                                <a href="#"><strong style="color:gray">Valid Reminders</strong></a>
                            </li>
                            @foreach ($reminders as $reminder)
                            <li>
                                <a href="{{ route('fitnesses.show',$reminder->id) }}"><i class="fa fa-bell" style="color: green"></i>{{ $reminder->reminder_item ? $reminder->reminder_item->name : "" }}  
                                    @if ($reminder->horse)
                                    for {{ $reminder->horse ? $reminder->horse->registration_number : "" }}
                                    @elseif ($reminder->vehicle)
                                    for {{ $reminder->vehicle ? $reminder->vehicle->registration_number : "" }}
                                    @elseif ($reminder->trailer)
                                    for  {{ $reminder->trailer ? $reminder->trailer->registration_number : "" }}
                                
                                    @elseif ($reminder->employee)
                                    for  {{ $reminder->employee ? $reminder->employee->name : "" }} {{ $reminder->employee ? $reminder->employee->surname : "" }}
                                    @endif
                                    expires on {{ Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                </a>
                            </li>
                            @endforeach
                        @endif
                        
                       
                        @if (isset($expired_reminders) && $expired_reminders->count() > 0 )
                            <li role="separator" class="divider"></li>
                            <li>
                                <a href="#"><strong style="color:gray">Expired Reminders</strong></a>
                            </li>
                            @foreach ($expired_reminders as $reminder)
                            <li>
                                <a href="{{ route('fitnesses.show',$reminder->id) }}"><i class="fa fa-bell" style="color: red"></i>{{ $reminder->reminder_item ? $reminder->reminder_item->name : "" }}  
                                    @if ($reminder->horse)
                                    for {{ $reminder->horse ? $reminder->horse->registration_number : "" }}
                                    @elseif ($reminder->vehicle)
                                    for {{ $reminder->vehicle ? $reminder->vehicle->registration_number : "" }}
                                    @elseif ($reminder->trailer)
                                    for  {{ $reminder->trailer ? $reminder->trailer->registration_number : "" }}
                                
                                    @elseif ($reminder->employee)
                                    for  {{ $reminder->employee ? $reminder->employee->name : "" }} {{ $reminder->employee ? $reminder->employee->surname : "" }}
                                    @endif
                                    expired on {{ Carbon\Carbon::parse($reminder->expires_at)->format('Y-m-d') }}
                                </a>
                            </li>
                            @endforeach  
                        @endif
                        
                    </ul>
                    @endif
             
                </li>
                
                @endif
          
                    {{-- <li><a href="#" class=""><i class="fa fa-bell"></i><span class="badge badge-danger">0</span></a></li> --}}
                    <!-- /.dropdown -->
                    <li class="dropdown tour-two">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ucfirst($user->name)}} {{ucfirst($user->surname)}}<span class="caret"></span></a>
                        <ul class="dropdown-menu profile-dropdown">
                            <li class="profile-menu bg-gray">
                                <div class="">
                                    @if ($employee)
                                    <img src="{{asset('images/uploads/'.$company->logo)}}" alt="{{$company->name}}" class="img-circle profile-img">
                                    <div class="profile-name">
                                        <h6>{{$company->name}}</h6>
                                    </div>
                                    @elseif($user->company)
                                    <img src="{{asset('images/uploads/'.$user->company->logo)}}" alt="{{$user->company->name}}" class="img-circle profile-img">
                                    <div class="profile-name">
                                        <h6>{{$user->company->name}}</h6>
                                    </div>
                                    @endif
                                   
                                    <div class="clearfix"></div>
                                </div>
                            </li>
                            @php
                                $roles = $user->roles;
                                    foreach($roles as $role){
                                        $role_names[] = $role->name;
                                    }
                                
                            @endphp
                            @if (in_array('Super Admin', $role_names))
                            @if ($employee)
                                <li><a href="{{route('company-profile',$company->id)}}"><i class="fa fa-cog"></i>Business Settings</a></li>
                            @else   
                                <li><a href="{{route('company-profile',$user->company->id)}}"><i class="fa fa-cog"></i>Business Settings</a></li>
                            @endif
                               
                            @endif
                     
                            <li role="separator" class="divider"></li>
                            <li class="profile-menu bg-gray">
                                <div class="">
                                    <img src="{{asset('images/uploads/'.$user->profile)}}" alt="{{ucfirst($user->name)}} {{ucfirst($user->surname)}}" class="img-circle profile-img"  style="width: 50px; height:50px">
                                    <div class="profile-name">
                                        <h6>{{ucfirst($user->name)}} {{ucfirst($user->surname)}}</h6>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </li>
                            <li><a href="{{route('profile',$user->id)}}"><i class="fa fa-cog"></i>Profile Settings</a></li>
                            <li><a href="{{route('logout')}}" class="color-danger"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                      
                    </li>
                    <!-- /.dropdown -->
                    {{-- <li><a href="#" class="hidden-xs hidden-sm open-right-sidebar"><i class="fa fa-ellipsis-v"></i></a></li> --}}
                </ul>
                <!-- /.nav navbar-nav navbar-right -->
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</nav>
