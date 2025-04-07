<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Payroll Details</a></li>
                <li role="presentation"><a href="#salaries" aria-controls="salaries" role="tab" data-toggle="tab">Salaries</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Payroll Number</th>
                                <td class="w-20 line-height-35">{{$payroll->payroll_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$payroll->user ? $payroll->user->name : ""}} {{$payroll->user ? $payroll->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Month</th>
                                <td class="w-20 line-height-35">{{$payroll->month}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Year</th>
                                <td class="w-20 line-height-35">{{$payroll->year}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="salaries">
                  @livewire('payrolls.salaries', ['id' => $payroll->id])
                </div> 
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
