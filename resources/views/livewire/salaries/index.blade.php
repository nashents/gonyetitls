
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
                         
                            <div class="panel-title">
                                <a href="{{route('salaries.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Salary</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <p style="color: green">Only retrieves salaries for active employees.</p>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Salary#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Basic
                                    </th>
                                    <th class="th-sm">Gross
                                    </th>
                                    <th class="th-sm">Net
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($salaries))
                                <tbody>
                                    @forelse ($salaries as $salary)
                                  <tr>
                                    <td>{{$salary->salary_number}}</td>
                                    <td>{{$salary->employee ? $salary->employee->name : ""}} {{$salary->employee ? $salary->employee->surname : ""}}</td>
                                    <td>{{$salary->currency ? $salary->currency->name : ""}}</td>
                                    <td>
                                        @if ($salary->basic)
                                          {{$salary->currency ? $salary->currency->symbol : ""}}{{number_format($salary->basic,2)}}        
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salary->gross)
                                          {{$salary->currency ? $salary->currency->symbol : ""}}{{number_format($salary->gross,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($salary->net)
                                          {{$salary->currency ? $salary->currency->symbol : ""}}{{number_format($salary->net,2)}}        
                                        @endif
                                    </td>
                                   
                                    <td><span class="badge bg-{{$salary->status == 1 ? "success" : "danger"}}">{{$salary->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('salaries.show',$salary->id)}}"  ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="{{route('salaries.edit',$salary->id)}}"  ><i class="fa fa-edit color-success"></i>Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#salaryDeleteModal{{ $salary->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('salaries.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Salaries Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($salaries))
                                        {{ $salaries->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

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

