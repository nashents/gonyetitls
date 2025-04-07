<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                                <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                            <th class="th-sm">Vehicle#
                                            </th>
                                            <th class="th-sm">Transporter
                                            </th>
                                            <th class="th-sm">Make
                                            </th>
                                            <th class="th-sm">VRN
                                            </th>
                                            <th class="th-sm">Mileage
                                            </th>
                                            <th class="th-sm">Next Service
                                            </th>
                                            <th class="th-sm">Fitness
                                            </th>
                                            <th class="th-sm">Availability
                                            </th>
                                            <th class="th-sm">Actions
                                            </th>
                                          </tr>
                                    </thead>
                                    @if (isset($vehicles))
                                    <tbody>
                                        @forelse ($vehicles as $vehicle)
                                      <tr>
                                        <td>{{$vehicle->vehicle_number}}</td>
                                        <td>{{$vehicle->transporter ? $vehicle->transporter->name : ""}}</td>
                                        <td>{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "")}} {{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "")}}</td>
                                        <td>{{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</td>
                                        <td>{{$vehicle->mileage ? $vehicle->mileage."Kms" : ""}}</td>
                                        <td>{{$vehicle->next_service ? $vehicle->next_service."Kms" : ""}}</td>
                                        <td><span class="badge bg-{{$vehicle->service == 0 ? "success" : "danger"}}">{{$vehicle->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                                        <td><span class="badge bg-{{$vehicle->status == 1 ? "success" : "danger"}}">{{$vehicle->status == 1 ? "Available" : "Unavailable"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" wire:click="restore({{$vehicle->id}})"><i class="fas fa-undo color-default"></i> Restore</a></li>
                                                </ul>
                                            </div>
                                            @include('vehicles.delete')

                                    </td>
                                      </tr>
                                      @empty
                                        <tr>
                                            <td colspan="10">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Vehicles Found ....
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
                                        @if (isset($vehicles))
                                            {{ $vehicles->links() }} 
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

        <div data-backdrop="static" data-keyboard="false" class="modal fade" id="restoreModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to restore this Vehicle?</strong> </center>
                    </div>
                    <form wire:submit.prevent="update()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fas fa-undo"></i> Restore</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>


    </div>
