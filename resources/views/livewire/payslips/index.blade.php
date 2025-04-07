<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <table id="payroll_salariesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Month
                                    </th>
                                    <th class="th-sm">Year
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>
                                  </tr>
                                </thead>
                                @if ($payroll_salaries->count()>0)
                                <tbody>
                                    @foreach ($payroll_salaries as $payroll_salary)
                                  <tr>
                                    <td>{{$payroll_salary->payroll ? $payroll_salary->payroll->month : ""}}</td>
                                    <td>{{$payroll_salary->payroll ? $payroll_salary->payroll->year : ""}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('payslip.preview', $payroll_salary->id) }}"><i class="fa fa-eye color-success"></i> Preview</a></li>
                                            </ul>
                                        </div>
                                     
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
  
</div>

