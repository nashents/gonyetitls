<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Cargo Details</a></li>
                <li role="presentation"><a href="#transporters" aria-controls="transporters" role="tab" data-toggle="tab">Transporters</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$cargo->user ? $cargo->user->name : ""}} {{$cargo->user ? $cargo->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$cargo->type}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Group</th>
                                    <td class="w-20 line-height-35">{{$cargo->group}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Name</th>
                                    <td class="w-20 line-height-35">{{$cargo->name}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Measurement</th>
                                    <td class="w-20 line-height-35">{{$cargo->measurement}}</td>
                                </tr>
                             
                                <tr>
                                    <th class="w-10 text-center line-height-35">Risk</th>
                                    <td class="w-20 line-height-35">{{$cargo->risk}}</td>
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="transporters">
                  @livewire('cargos.transporters', ['id' => $cargo->id])
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
