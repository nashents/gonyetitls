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
                                {{-- <a href="#" wire:click="exportHorsesPerformanceExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportHorsesPerformanceCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportHorsesPerformancePDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> --}}
                            </div>
                        </div>

                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                <div class="col-lg-3">
                                <div class="input-group">
                                  <span class="input-group-addon">Filter By</span>
                                  <select wire:model.debounce.300ms="filter" class="form-control" aria-label="..." >
                                    <option value="">Select Filter</option>
                                    <option value="created_at">Trip Created At</option>
                                    <option value="start_date">Trip Start Date</option>
                                  </select>
                                </div>
                                    <!-- /input-group -->
                                </div>
                                @if ($filter == "created_at")
                                <div class="col-lg-2" style="margin-right: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @elseif ($filter == "start_date")
                                <div class="col-lg-2" style="margin-right: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 42px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="datetime-local" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                @endif
                               
                                <!-- /input-group -->
                            
                            </div>
                                <small style="color: green">Dist(Km) = Total distance travelled in kilometers, Fuel(l) = Total fuel used in litres,
                                    F/C(l/Km) = Fuel Consumption using mileage/distance: Litre per Kilometer,
                                    F/C(l/H) = Fuel Consumption using engine hours: Litre per Engine Hour,
                                    Vol(l) = Total volume/litreage moved in litres, V/Loss(l) Total volume/litreage losses in litres,
                                    V/Loss(%) Total volume/litreage losses in percentage,
                                    W/Loss(t) = Total weight/tonnage losses in tons, W/Loss(%) = Total weight/tonnage losses in percentage.
                                </small>
                                <br>
                                <br>
                            
                            </div>

                                        <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Trip(s)
                                    </th>
                                    <th class="th-sm">Revenue({{$currency->name}})
                                    </th>
                                    <th class="th-sm">Dist(Km) 
                                    </th>
                                    <th class="th-sm">Fuel(l) 
                                    </th>
                                    <th class="th-sm">
                                        F/C(l/Km)
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        F/C(l/H)
                                    </th>
                                    <th class="th-sm">Vol(l)
                                    </th>
                                   <th class="th-sm">
                                        V/Loss(l)
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        V/Loss(%)
                                    </th>
                                    <th class="th-sm">Weight(t)
                                    </th>
                                     <th class="th-sm">
                                        W/Loss(t)
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        W/Loss(%)
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($horses))
                                <tbody>               
                                    @forelse ($horses as $selected_horse)
                                  <tr>
                                    @php
                                         $horse = App\Models\Horse::find($selected_horse->horse_id);
                                    @endphp
                                    <td>
                                        @if (isset($horse))
                                            {{$horse->transporter ? $horse->transporter->name : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($horse))
                                            {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} {{$horse->registration_number}} {{$horse->fleet_number}}
                                        @endif
                                    </td>
                                    <td>{{$selected_horse->total_trips ? $selected_horse->total_trips : ""}}</td>
                                    <td>
                                        {{$this->calculateTotalRevenue($selected_horse->horse_id)}}
                                    </td>
                                    <td>{{$selected_horse->total_kilometers ? $selected_horse->total_kilometers."Kms" : ""}}</td>
                                    <td>{{$selected_horse->total_fuel_quantity ? $selected_horse->total_fuel_quantity."l" : ""}}</td>
                                    <td>
                                        {{$selected_horse->avg_fuel_consumption_mileage ? $selected_horse->avg_fuel_consumption_mileage." L/Km" : ""}} 
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                        {{$selected_horse->avg_fuel_consumption_hours ? $selected_horse->avg_fuel_consumption_hours." L/H"  : ""}}
                                    </td>
                                    <td>
                                        {{$selected_horse->total_volume ? $selected_horse->total_volume."l" : ""}}
                                    </td>
                                    <td>
                                        
                                        {{$selected_horse->total_volume_loss ? $selected_horse->total_volume_loss."l" : ""}}
                                       <hr style="margin-top:5px; margin-bottom:5px">  
                                        {{$this->calculateVolumeLosses($selected_horse)}}
                                    </td>
                                    <td>{{$selected_horse->total_tonnage ? $selected_horse->total_tonnage."t" : ""}}</td>
                                    <td>
                                        {{$selected_horse->total_tonnage_loss ? $selected_horse->total_tonnage_loss."t" : ""}}
                                       <hr style="margin-top:5px; margin-bottom:5px">  
                                        {{$this->calculateTonnageLosses($selected_horse)}}
                                    </td>
                                   
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Horse Perfomance Details Found ....
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="mileageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Update Mileage <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Prev Service Mileage</label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="prev_service" class="form-control">
                                @error('prev_service') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Prev Service Date</label>
                                <input type="date"  wire:model.debounce.300ms="prev_service_date" class="form-control">
                                @error('prev_service_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Current Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="mileage" class="form-control" required>
                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Next Service Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="next_service" class="form-control" required>
                                @error('next_service') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
   



</div>

