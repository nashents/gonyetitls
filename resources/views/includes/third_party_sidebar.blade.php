@if (Auth::user()->customer)
<style>
    .bg-black-300 {
    background-color: {{Auth::user()->customer->company->color}};
    }
</style>
@elseif(Auth::user()->transporter)
<style>
    .bg-black-300 {
    background-color: {{Auth::user()->transporter->company->color}};
    }
</style>
@elseif(Auth::user()->agent)
<style>
    .bg-black-300 {
    background-color: {{Auth::user()->agent->company->color}};
    }
</style>
@elseif(Auth::user()->broker)
<style>
    .bg-black-300 {
    background-color: {{Auth::user()->broker->company->color}};
    }
</style>
@endif
<div class="left-sidebar fixed-sidebar bg-black-300 box-shadow tour-three">
    <div class="sidebar-content">
        <div class="user-info closed">
            <img src="{{asset('images/uploads/'.Auth::user()->profile)}}" alt="{{Auth::user()->name}} {{Auth::user()->surname ? Auth::user()->surname : ""}}" class="img-circle profile-img" style="width: 90px; height:90px">
            <h6 class="title">{{Auth::user()->name}} {{Auth::user()->surname ? Auth::user()->surname : ""}}</h6>
            {{-- <small class="info">Company</small> --}}
        </div>
        <!-- /.user-info -->

        <div class="sidebar-nav">
            <ul class="side-nav color-gray">
                <li class="nav-header">
                    <span class="">Main Category</span>
                </li>
                <li>
                    <a href="{{route('dashboard.third_parties')}}"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span> </a>
                </li>
                    @if (Auth::user()->transporter)
                    <li class="nav-header">
                        <span class="">Transport & Logistics</span>
                    </li>
                        <li class="has-children">
                            <a href="#"><i class="fas fa-truck"></i> <span>Fleet Management</span> <i class="fas fa-angle-right arrow"></i></a>
                            <ul class="child-nav">
                              
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-cog"></i> <span>Master</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('horse_groups.index')}}"><i class="fas fa-truck"></i> <span>Horse Groups</span></a></li>
                                        <li><a href="{{route('horse_makes.index')}}"><i class="fas fa-truck"></i> <span>Horse Makes</span></a></li>
                                        <li><a href="{{route('horse_types.index')}}"><i class="fas fa-truck"></i> <span>Horse Types</span></a></li>
                                        <li><a href="{{route('trailer_groups.index')}}"><i class="fas fa-trailer"></i> <span>Trailer Groups</span></a></li>
                                        <li><a href="{{route('trailer_types.index')}}"><i class="fas fa-trailer"></i> <span>Trailer Types</span></a></li>
                                    </ul>
                                </li>
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-truck"></i> <span>Horses</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('horses.create')}}" ><i class="fas fa-plus "></i> <span>Add Horse</span></a></li>
                                        <li><a href="{{route('horses.index')}}"><i class="fas fa-list "></i> <span>Manage Horses</span></a></li>
                                    </ul>
                                </li>
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-trailer"></i> <span>Trailers</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('trailers.index')}}" ><i class="fas fa-plus "></i> <span> Add Trailer </span></a></li>
                                        <li><a href="{{route('trailers.manage')}}"><i class="fas fa-list "></i> <span>Manage Trailers</span></a></li>
                                    </ul>
                                </li>
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-user-plus"></i> <span>Assignments</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('assignments.index')}}" ><i class="fas fa-plus "></i> <span>Driver - Horse </span></a></li>
                                    </ul>
                                </li>
                                <li class="has-children">
                                    <a href="#"><i class="fas fa-line-chart"></i> <span>Reports</span> <i class="fas fa-angle-right arrow"></i></a>
                                    <ul class="child-nav">
                                        <li><a href="{{route('horses.reports')}}" ><i class="fas fa-info-circle "></i> <span>Horses</span></a></li>
                                        <li><a href="{{route('assignments.reports')}}" ><i class="fas fa-info-circle "></i> <span>Assignments</span></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endif
                  
                <li class="nav-header">
                    <span class="">Trip Management</span>
                </li>
                <li class="has-children">
                    <a href="#"><i class="fas fa-road"></i> <span>Trips</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('trips.third_parties')}}" ><i class="fas fa-list "></i> <span>Manage Trips</span></a></li>
                       
                    </ul>
                </li>
          
                        <li class="nav-header">
                            <span class="">Account</span>
                        </li>
                        <li>
                            <a href="{{route('logout')}}"><i class="fas fa-sign-out-alt" ></i> <span>Logout</span> </a>
                        </li>
        </div>
        <!-- /.sidebar-nav -->
    </div>
    <!-- /.sidebar-content -->
</div>
