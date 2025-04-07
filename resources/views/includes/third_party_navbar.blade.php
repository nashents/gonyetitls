<nav class="navbar top-navbar bg-white box-shadow">
    <div class="container-fluid">
        <div class="row">
            <div class="navbar-header no-padding">
                <a class="navbar-brand" href="{{route('dashboard.index')}}">
                    @if (isset(Auth::user()->customer->company->logo))
                    <img src="{{asset('images/uploads/'.Auth::user()->customer->company->logo)}}" alt="{{Auth::user()->customer->company->name}} "  class="logo">
                    @elseif (isset(Auth::user()->agent->company->logo))
                    <img src="{{asset('images/uploads/'.Auth::user()->agent->company->logo)}}" alt="{{Auth::user()->agent->company->name}} "  class="logo">
                    @elseif (isset(Auth::user()->transporter->company->logo))
                    <img src="{{asset('images/uploads/'.Auth::user()->transporter->company->logo)}}" alt="{{Auth::user()->transporter->company->name}} "  class="logo">
                    @elseif (isset(Auth::user()->broker->company->logo))
                    <img src="{{asset('images/uploads/'.Auth::user()->broker->company->logo)}}" alt="{{Auth::user()->broker->company->name}} "  class="logo">
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
                    {{-- <li class="hidden-sm hidden-xs"><a href="#"><i class="fa fa-search"></i></a></li>
                    <li class="hidden-xs hidden-xs"><!-- <a href="#">My Tasks</a> --></li> --}}
                </ul>
                <!-- /.nav navbar-nav -->

                <ul class="nav navbar-nav navbar-right" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                    {{-- <li class="dropdown">
                        <a href="#" class="dropdown-toggle bg-primary tour-one" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-line-chart"></i> Reports <span class="caret"></span></a>
                        <ul class="dropdown-menu" >
                            <li><a href="{{route('trips.reports')}}"><i class="fa fa-plus-square-o"></i>Trips</a></li>
                            <li><a href="{{route('vehicles.reports')}}"><i class="fa fa-plus-square-o"></i>Vehicles</a></li>
                            <li><a href="{{route('employees.reports')}}"><i class="fa fa-plus-square-o"></i>Employees</a></li>
                        </ul>
                    </li>--}} 
                    <li><a href="#" class=""><i class="fa fa-bell"></i><span class="badge badge-danger">0</span></a></li>
                    <li><a href="#" class=""><i class="fa fa-comments"></i><span class="badge badge-warning">0</span></a></li> 
                    <!-- /.dropdown -->
                    <li class="dropdown tour-two">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ucfirst(Auth::user()->name)}} {{ucfirst(Auth::user()->surname)}}<span class="caret"></span></a>
                        <ul class="dropdown-menu profile-dropdown">
                            <li class="profile-menu bg-gray">
                                <div class="">
                                    <img src="{{asset('images/avatar.png')}}" alt="{{ucfirst(Auth::user()->name)}} {{ucfirst(Auth::user()->surname)}}" class="img-circle profile-img">
                                    <div class="profile-name">
                                        <h6>{{ucfirst(Auth::user()->name)}} {{ucfirst(Auth::user()->surname)}}</h6>
                                        {{-- <a href="{{route('profile',Auth::user()->id)}}">View Profile</a> --}}
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </li>
                            <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li>
                            <li><a href="#"><i class="fa fa-sliders"></i> Account Details</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="{{route('logout')}}" class="color-danger text-center"><i class="fa fa-sign-out"></i> Logout</a></li>
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
