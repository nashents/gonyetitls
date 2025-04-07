<style>
    .bg-black-300 {
    background-color: {{Auth::user()->company->color}};
    }
</style>
<div class="left-sidebar fixed-sidebar bg-black-300 box-shadow tour-three">
    <div class="sidebar-content">
        <div class="user-info closed">
            <img src="{{asset('images/uploads/'.Auth::user()->profile)}}" alt="{{Auth::user()->name}} {{Auth::user()->surname}}" class="img-circle profile-img" style="width: 90px; height:90px">
            <h6 class="title">{{Auth::user()->name}} {{Auth::user()->surname}}</h6>
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

                <li class="nav-header">
                    <span class="">Administration</span>
                </li>
                <li class="has-children">
                    <a href="#"><i class="fas fa-cog"></i> <span>Masters</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li>
                            <a href="{{route('branches.index')}}"><i class="fas fa-sitemap"></i> <span>Branches</span> </a>
                        </li>
                        <li>
                            <a href="{{route('departments.index')}}"><i class="far fa-building"></i> <span>Departments</span> </a>
                        </li>
                        <li>
                            <a href="{{route('job_titles.index')}}"><i class="far fa-file"></i> <span>Job Titles</span> </a>
                        </li>


                    </ul>
                </li>
                <li class="nav-header">
                    <span class="">Human Resource</span>
                </li>
                <li class="has-children">
                    <a href="#"><i class="fas fa-users"></i> <span>Employees</span> <i class="fas fa-angle-right arrow"></i></a>
                    <ul class="child-nav">
                        <li><a href="{{route('employees.create')}}" ><i class="fas fa-user "></i> <span>Employee</span></a></li>
                        <li><a href="{{route('employees.index')}}"><i class="fas fa-list "></i> <span>Manage Employees</span></a></li>
                    </ul>
                </li>

                        <li class="nav-header">
                            <span class="">Account</span>
                        </li>
                        <li>
                            @if (isset(Auth::user()->company))
                            <a href="{{route('company-profile',Auth::user()->company->id)}}"><i class="fas fa-info-circle"></i> <span>Company Profile</span> </a>
                            @endif

                        </li>

                        <li>
                            <a href="{{route('logout')}}"><i class="fas fa-sign-out-alt" ></i> <span>Logout</span> </a>
                        </li>
        </div>
        <!-- /.sidebar-nav -->
    </div>
    <!-- /.sidebar-content -->
</div>
