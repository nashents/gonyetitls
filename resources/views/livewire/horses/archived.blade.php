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

                                <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Horse#
                                        </th>
                                        <th class="th-sm">Transporter
                                        </th>
                                        <th class="th-sm">Make
                                        </th>
                                        <th class="th-sm">HRN
                                        </th>
                                        <th class="th-sm">
                                            Mileage
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Next Service

                                        </th>
                                        <th class="th-sm">Fitness
                                        </th>
                                        <th class="th-sm">Availability
                                        </th>
                                       
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($horses))
                                    <tbody>
                                        @forelse ($horses as $horse)
                                      <tr>
                                       
                                        <td>{{$horse->horse_number}}</td>
                                        <td>{{$horse->transporter ? $horse->transporter->name : ""}}</td>
                                        <td>{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}}</td>
                                        <td>{{ucfirst($horse->registration_number)}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</td>
                              
                                        <td>
                                            {{$horse->mileage ? $horse->mileage."Kms" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$horse->next_service ? $horse->next_service."Kms" : ""}}
                                        </td>
                                      
                                          <td><span class="badge bg-{{$horse->service == 0 ? "success" : "danger"}}">{{$horse->service == 0 ? "Fit for use" : "In Service"}}</span></td>
                                        <td><span class="badge bg-{{$horse->status == 1 ? "success" : "danger"}}">{{$horse->status == 1 ? "Available" : "Unavailable"}}</span></td>
                                      
                                      
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" wire:click="restore({{$horse->id}})"><i class="fas fa-undo color-default"></i> Restore</a></li>
                                                </ul>
                                            </div>
                                            @include('horses.delete')

                                    </td>
                                      </tr>
                                      @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Horses Found ....
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
                                        @if (isset($horses))
                                            {{ $horses->links() }} 
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
                       <center> <strong>Are you sure you want to restore this Horse?</strong> </center>
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
