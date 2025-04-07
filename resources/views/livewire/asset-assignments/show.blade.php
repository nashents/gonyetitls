<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Assignment Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Asset#</th>
                                <td class="w-20 line-height-35">{{$assignment->asset ? $assignment->asset->asset_number : ""}} </td>
                            </tr>
    
                            <tr>
                                <th class="w-10 text-center line-height-35">Asset</th>
                                <td class="w-20 line-height-35">{{$assignment->asset->product->brand ? $assignment->asset->product->brand->name : ""}} {{$assignment->asset->product ? $assignment->asset->product->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Serial Number</th>
                                <td class="w-20 line-height-35">{{$assignment->asset ? $assignment->asset->serial_number : ""}} </td>
                            </tr>
                            
                            <tr>
                                <th class="w-10 text-center line-height-35">Employee</th>
                                <td class="w-20 line-height-35">{{$assignment->employee ? $assignment->employee->name : ""}} {{$assignment->employee ? $assignment->employee->surname : ""}}</td>
                            </tr>
                           @if ($assignment->department)
                            <tr>
                                <th class="w-10 text-center line-height-35">Department</th>
                                <td class="w-20 line-height-35">{{$assignment->department ? $assignment->department->name : ""}}</td>
                            </tr>
                           @endif
                           
                           @if ($assignment->branch)
                            <tr>
                                <th class="w-10 text-center line-height-35">Branch</th>
                                <td class="w-20 line-height-35">{{$assignment->branch ? $assignment->branch->name : ""}}</td>
                            </tr>
                           @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$assignment->start_date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$assignment->specifications}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$assignment->status == 1 ? "success" : "danger"}}">{{$assignment->status == 1 ? "Active" : "Inactive"}}</span></td>
                            </tr>

                        </tbody>
                    </table>

                </div>
               
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>


                <!-- /.section-title -->
            </div>
          
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
