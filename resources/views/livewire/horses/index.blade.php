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
                                <div class="panel-title">
                                    <a href="{{route('horses.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Horse</a>
                                    <a href="" data-toggle="modal" data-target="#horsesImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                    <a href="#" wire:click="exportHorsesExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportHorsesCSV()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportHorsesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                 
                                </div>
                            </div>
                            <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                                <div class="col-md-3" style="float: right; padding-right:0px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search horses...">
                                    </div>
                                </div>
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
                                        <th class="th-sm">Revenue
                                        </th>
                                        <th class="th-sm">Expenses
                                        </th>
                                        <th class="th-sm">CPK
                                        </th>
                                        <th class="th-sm">
                                            Mileage
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            Next Service

                                        </th>
                                        <th class="th-sm">
                                            Hours
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
                                            @foreach ($currencies as $currency)
                                                @php
                                                    $revenue = App\Models\Trip::where('horse_id',$horse->id)->whereYear('created_at', date('Y'))->where('authorization', 'approved')->where('trip_status','!=', 'Cancelled')
                                                                                ->where('currency_id',$currency->id)->sum('freight');
                                                @endphp
                                                @if (isset($revenue) && $revenue > 0)
                                                    {{ $currency->name }} {{ $currency->symbol }}{{number_format($revenue,2)}} <br>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> 
                                            @foreach ($currencies as $currency)
                                                @php
                                                    $expenses = App\Models\Bill::where('horse_id',$horse->id)->whereYear('created_at', date('Y'))->where('authorization', 'approved')
                                                                                ->where('currency_id',$currency->id)->where('total','!=','')->where('total','!=', Null)->sum('total');
                                                @endphp
                                                @if (isset($expenses) && $expenses > 0)
                                                    {{ $currency->name }} {{ $currency->symbol }}{{number_format($expenses,2)}} <br>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>{{$this->calculateCPK($horse->id)}}</td>
                                        <td>
                                            {{$horse->mileage ? $horse->mileage."Kms" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$horse->next_service ? $horse->next_service."Kms" : ""}}
                                        </td>
                                        <td>
                                            {{$horse->hours ? $horse->hours."Hours" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$horse->next_service_hours ? $horse->next_service_hours."Hours" : ""}}
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
                                                    <li><a href="{{route('horses.show', $horse->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('horses.edit', $horse->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#horseDeleteModal{{$horse->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @if ($horse->status == 1)
                                                    <li><a href="{{route('horses.deactivate', $horse->id)}}"  ><i class="fa fa-toggle-on color-danger"></i>Deactivate</a></li>
                                                    @else
                                                    <li><a href="{{route('horses.activate', $horse->id)}}"  ><i class="fa fa-toggle-off color-success"></i>Activate</a></li>
                                                    @endif
                                                    @if ($horse->service == 1)
                                                    <li><a href="{{route('horses.service', $horse->id)}}"  ><i class="fa fa-remove color-success"></i>Close Ticket(s)</a></li>
                                                    @endif
                                                    @if ($horse->archive == 0)
                                                    <li><a href="{{route('horses.archive', $horse->id)}}"  ><i class="fa fa-archive color-primary"></i>Archive</a></li>
                                                    @endif

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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="horsesImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Horses <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form action="{{route('horses.import')}}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Upload Horse(s) Excel File</label>
                            <input type="file" class="form-control" name="file" placeholder="Upload horse File" >
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div><!-- Modal -->


    </div>
