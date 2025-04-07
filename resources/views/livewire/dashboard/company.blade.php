<div>
    <section class="section">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-primary" href="{{route('branches.index')}}">
                        <span class="number counter">{{$branch_count}}</span>
                        <span class="name">Branches</span>
                        <span class="bg-icon"><i class="fa fa-building-o"></i></span>
                    </a>

                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-danger" href="{{route('departments.index')}}">
                        <span class="number counter">{{$department_count}}</span>
                        <span class="name">Departments</span>
                        <span class="bg-icon"><i class="fa fa-building"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                    <a class="dashboard-stat bg-warning" href="{{route('employees.index')}}">
                        <span class="number counter">{{$employee_count}}</span>
                        <span class="name">Employees</span>
                        <span class="bg-icon"><i class="fa fa-users"></i></span>
                    </a>
                </div>
                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->


                <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

            </div>



            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
</div>
