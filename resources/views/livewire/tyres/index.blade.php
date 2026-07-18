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
                                <a href="{{route('tyres.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tyre</a>
                                <a href="" data-toggle="modal" data-target="#tyresImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportTyresExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportTyresCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportTyresPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search tyres...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Tyre
                                    </th>
                                    <th class="th-sm">Dimensions
                                    </th>
                                    <th class="th-sm">Location
                                    </th>
                                     <th class="th-sm">Usage
                                    </th>
                                    <th class="th-sm">Health Status
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Cost
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                   
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($tyres))
                                <tbody>
                                    @forelse ($tyres as $tyre)
                                  <tr>
                                     @php
                                           $assignment = App\Models\TyreAssignment::with(['horse','tyre'])->where('tyre_id',$tyre->id)->where('status',1)->latest()->first();
                                        @endphp
                                    <td>
                                         {{$tyre->product ? $tyre->product->name : ""}} <strong>{{$tyre->product && $tyre->product->brand ? "(".$tyre->product->brand->name.")" : ""}}</strong>
                                         <br>
                                        <small><strong>Type: </strong>  
                                            @if ($tyre->type == "Diff")
                                                 <span class="badge bg-primary">{{$tyre->type}}</span>
                                            @elseif($tyre->type == "Supersingle")
                                                <span class="badge bg-info">{{$tyre->type}}</span>
                                            @elseif($tyre->type == "Multipurpose")
                                                <span class="badge bg-success">{{$tyre->type}}</span>
                                            @elseif($tyre->type == "Steer")
                                                <span class="badge bg-active">{{$tyre->type}}</span>
                                            @endif
                                           </small> <br>
                                           @if ($tyre->serial_number)
                                                <small><strong>S#:</strong> {{$tyre->serial_number}}</small>
                                           @endif
                                    </td>
                                    <td>
                                        {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}
                                        <br>
                                        <small><strong>Tread Depth(<i>mm</i>): </strong> {{number_format($tyre->thread_depth ?? Null,2)}}</small>
                                        <br>
                                        <small><strong>Pressure(<i>psi</i>): </strong> {{number_format($tyre->pressure_psi ?? Null,2)}}</small>
                                        <br>
                                        <small><strong>Life Span(<i>kms</i>): </strong> {{number_format($tyre->life_span ?? Null,2)}}</small>

                                    
                                    </td>
                                    <td>
                                       
                                        @if ($assignment)
                                            <a href="{{route('tyre_assignments.show',$assignment->id)}}" style="color: blue">
                                            @if ($assignment->horse)
                                                Horse: {{$assignment->horse->registration_number}} {{$assignment->horse->fleet_number ? "(".$assignment->horse->fleet_number.")" : ""}}
                                            @elseif($assignment->trailer)
                                                Trailer: {{$assignment->trailer->registration_number}} {{$assignment->trailer->fleet_number ? "(".$assignment->trailer->fleet_number.")" : ""}}
                                            @elseif($assignment->vehicle)
                                                Vehicle: {{$assignment->horse->registration_number}} {{$assignment->vehicle->fleet_number ? "(".$assignment->vehicle->fleet_number.")" : ""}}
                                            @endif
                                            </a>
                                            <br>
                                            <small><strong>{{$assignment->axle}} {{$assignment->position}}</strong></small>
                                        @else
                                            @if ($tyre->retread == 0)
                                                <span class="badge bg-success">Instore</span>
                                                <br>
                                                {{$tyre->store ? $tyre->store->name : ""}}
                                            @else
                                                <span class="badge bg-warning">Retread</span>
                                            @endif  
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Acquisition: </strong>
                                            {{
                                                !empty($tyre->purchase_date)
                                                    ? \Carbon\Carbon::parse($tyre->purchase_date)->format('d M Y')
                                                    : '-'
                                            }}
                                        </small>
                                        <br>

                                        <small>
                                            <strong>Age: </strong>
                                            {{ $tyre->age ?? '-' }}
                                        </small>

                                        @if($assignment)
                                            <br>

                                            <small>
                                                <strong>Fitted: </strong>
                                                {{ is_numeric($assignment->starting_odometer) ? number_format((float)$assignment->starting_odometer) : '-' }}
                                            </small>
                                            <br>

                                            <small>
                                                <strong>Current: </strong>
                                                {{
                                                    is_numeric($assignment->ending_odometer)
                                                        ? number_format((float)$assignment->ending_odometer)
                                                        : (
                                                            is_numeric($assignment->horse?->mileage)
                                                                ? number_format((float)$assignment->horse->mileage)
                                                                : '-'
                                                        )
                                                }}
                                            </small>
                                            <br>

                                            <small>
                                                <strong>Travelled: </strong>
                                                {{ is_numeric($assignment->travelled_km) ? number_format((float)$assignment->travelled_km) : '0' }} km
                                            </small>
                                            <br>

                                            <small>
                                                <strong>Life(Standard): </strong>
                                                {{ is_numeric($tyre->life_span) ? number_format((float)$tyre->life_span) : '0' }} km
                                            </small>
                                            <br>

                                            <small>
                                                <strong>Remaining: </strong>
                                                @php
                                                    $rem = $assignment->remaining_km;
                                                    $pct = $assignment->remaining_pct;
                                                @endphp

                                                {{ is_numeric($rem) ? number_format((float)$rem).' km' : '-' }}

                                                @if(is_numeric($pct))
                                                    ({{ number_format((float)$pct, 2) }}%)
                                                @endif
                                            </small>
                                            <br>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                                $checklist_result = App\Models\ChecklistResult::where('tyre_id',$tyre->id)->latest()->first();
                                                if ($checklist_result) {
                                                    $tread_depth_mm = $checklist_result->tread_depth_mm;
                                                    $pressure_psi = $checklist_result->pressure_psi;
                                                    $valve_ok = $checklist_result->valve_ok;
                                                    $sidewall_damage = $checklist_result->sidewall_damage;
                                                    $rim_condition = $checklist_result->rim_condition;
                                                    $wheel_nuts_torqued = $checklist_result->wheel_nuts_torqued;
                                                    $axle_match = $checklist_result->axle_match;
                                                    $notes = $checklist_result->notes;
                                                    $action_required = $checklist_result->action_required;
                                                    $rating = $checklist_result->rating;
                                                }
                                        @endphp 
                                        
                                        @if ($checklist_result)
                                           <small><strong>Tread Depth(<i>mm</i>):</strong>  <span class="badge bg-{{$this->badge($tyre->id,'depth')}}">{{$tread_depth_mm}}</span> </small> <br>
                                           <small><strong>Tyre Pressure(<i>psi</i>):</strong> <span class="badge bg-{{$this->badge($tyre->id,'pressure')}}">{{$pressure_psi}}</span> </small> <br>
                                           <small><strong>Valve: </strong> {{$valve_ok == 1 ? "Air Tight" : "Leaking"}}</small> <br>
                                           <small><strong>Sidewall Damage: </strong> {{$sidewall_damage}}</small> <br>
                                           <small><strong>Rim Condition: </strong> {{$rim_condition}}</small> <br>
                                           <small><strong>Wheelnuts Torqued:</strong> {{$wheel_nuts_torqued == 1 ? "Yes" : "No"}}</small> <br>
                                           <small><strong>Axle Match:</strong> {{$axle_match == 1 ? "Match" : "Not Matching"}}</small> <br>
                                           <small><strong>Overal Rating:</strong>  @for ($i = 1; $i <= 5; $i++)
                                        <span style="color: {{ $i <= $rating ? '#FFD700' : '#ccc' }};">★</span>
                                    @endfor</small> <br>
                                           <small><strong>Notes:</strong> {{Str::limit($notes,30,'...')}}</small> <br>
                                           <small><strong>Action:</strong> {{$action_required}}</small> <br>
                                        @endif
                                        {{-- <span class="badge bg-{{$tyre->retread == 0 ? "success" : "warning"}}">{{$tyre->retread == 0 ? "Fit for use" : "Retread"}}</span> --}}
                                    </td>
                                    <td>{{$tyre->qty}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->name : ""}}</td>
                                    <td>
                                        {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->amount ? $tyre->amount : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->tax_amount ? $tyre->tax_amount : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->cost ? $tyre->cost : 0,2)}}  
                                    </td>
                                    <td>
                                         {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->total ? $tyre->total : 0,2)}}  
                                        @if (Auth::user()->employee->company->currency_id != $tyre->currency_id)
                                            <br>
                                            <small>
                                                <strong>Exc Rate:</strong> {{number_format($tyre->exchange_rate,2)}} <br>
                                                <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($tyre->exchange_amount,2)}}
                                            </small>
                                        @endif
                                    </td>
                                 
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('tyres.show',$tyre->id) }}" ><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="{{ route('tyres.edit',$tyre->id) }}" ><i class="fa fa-edit color-success"></i>Edit</a></li>
                                                <li><a href="#" wire:click="showDispose({{$tyre->id}})"  ><i class="fa fa-times color-warning"></i> Dispose</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tyreDeleteModal{{ $tyre->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('tyres.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Tyres Found ....
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
                                    @if (isset($tyres))
                                        {{ $tyres->links() }} 
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="disposeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Dispose Tyre <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="dispose()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Dispose Item<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="dispose" class="form-control" required >
                                   <option value="">Select Option</option>
                                   <option value="1">Yes</option>
                                   <option value="0">No</option>
                               </select>
                                @error('dispose') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Reason<span class="required" style="color: red">*</span></label>
                      <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3" required></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyresImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Tyres <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('tyres.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Tyres Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload Loading Points File" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyreEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Tyre <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="width">Tyre Number</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="tyre_number"  placeholder="Tyre Number " required />
                        @error('tyre_number') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="country">Product(s)<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="product_id" class="form-control" required>
                           <option value="">Select Product</option>
                         @foreach ($products as $product)
                            <option value="{{$product->id}}"> {{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                         @endforeach
                       </select>
                        @error('product_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Serial Number</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="serial_number"  placeholder="Serial Number " required />
                        @error('serial_number') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Width<i>(mL)</i> /</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="width"  placeholder="Width " required>
                        @error('width') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="aspect_ratio">A/Ratio (R)</label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="aspect_ratio" placeholder="Aspect Ratio" required />
                        @error('aspect_ratio') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="diameter">Diameter<i>(in)</i></label>
                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="diameter"  placeholder="Diameter " required />
                        @error('diameter') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="width">Rate</label>
                        <input type="number" class="form-control" step="any" wire:model.debounce.300ms="amount"   required />
                        @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
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

