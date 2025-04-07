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
                                <a href="#" data-toggle="modal" data-target="#rateModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Rate</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="ratesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Freight Cal
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">LP
                                    </th>
                                    <th class="th-sm">OP
                                    </th>
                                    <th class="th-sm">Distance
                                    </th>
                                    <th class="th-sm">Cargo
                                    </th>
                                    <th class="th-sm">Weight
                                    </th>
                                    <th class="th-sm">Litreage
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($rates->count()>0)
                                <tbody>
                                    @foreach ($rates as $rate)
                                  <tr>
                                    @php
                                        $from = App\Models\Destination::find($rate->from);
                                        $to = App\Models\Destination::find($rate->to);
                                    @endphp
                                    <td>{{$rate->category}}</td>
                                    <td>{{$rate->freight_calculation}}</td>
                                    <td>{{$from ? $from->country->name : ""}} {{$from->city}}</td>
                                    <td>{{$to ? $to->country->name : ""}} {{$to->city}}</td>
                                    <td>{{$rate->loading_point ? $rate->loading_point->name : ""}}</td>
                                    <td>{{$rate->offloading_point ? $rate->offloading_point->name : ""}}</td>
                                    <td>
                                        @if ($rate->distance)
                                        {{$rate->distance}}Kms    
                                        @endif
                                    </td>
                                    <td>{{$rate->cargo ? $rate->cargo->name : ""}}</td>
                                    <td>
                                        @if ($rate->weight)
                                            {{$rate->weight}}Tons
                                        @endif 
                                    </td>
                                    <td>
                                        @if ($rate->litreage)
                                            {{$rate->litreage}}Litres
                                        @endif
                                    </td>
                                    <td>{{$rate->currency ? $rate->currency->name : ""}}</td>
                                    <td>{{$rate->currency ? $rate->currency->symbol : ""}}{{$rate->rate}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" data-toggle="modal" data-target="#rateEditModal" wire:click="edit({{$rate->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#rateDeleteModal{{ $rate->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('rates.delete')
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
    {{-- <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ratesImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import rates <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="{{route('rates.import')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload rate(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload rate File" >
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
    </div> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rateModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Rate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="country">Category<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="category" required>
                            <option value="">Select Category</option>
                            <option value="Customer">Customer</option>
                            <option value="Transporter">Transporter</option>
                        </select>
                        @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <h5 class="underline mt-30">Freight Calculation Method<span class="required" style="color: red">*</span></h5>
                        <div class="mb-10">
                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style"  />
                            <label for="one" class="radio-label">Flat Rate</label>
                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style"  />
                            <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                            <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" />
                            <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                            @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">From<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="from" required>
                                    <option value="">Select From Location</option>
                                    @foreach ($destinations as $destination)
                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">To<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="to" required>
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="country">Loading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="loading_point_id" >
                                    <option value="">Select Loading Point</option>
                                    @foreach ($loading_points as $loading_point)
                                    <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small> 
                                @error('loading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="country">OffLoading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="offloading_point_id" >
                                    <option value="">Select Offloading Point</option>
                                    @foreach ($offloading_points as $offloading_point)
                                    <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> 
                                @error('offloading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="title">Distance</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Distance" />
                                @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Cargo</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCargo">
                                    <option value="">Select Cargo</option>
                                    @foreach ($cargos as $cargo)
                                    <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> 
                                @error('selectedCargo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                   
                        @if ($cargo_type == "Solid")
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Weight</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Weight" />
                                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           </div>
                        @elseif($cargo_type == "Liquid")
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Weight</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Weight" />
                                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Litreage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Litreage" />
                                @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                           </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Rate<span class="required" style="color: red">*</span></label>
                            <input type="numnber" step="any" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" required/>
                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                       </div>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rateEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Rate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
              <div class="modal-body">
                <div class="form-group">
                    <label for="country">Category<span class="required" style="color: red">*</span></label>
                    <select class="form-control" wire:model.debounce.300ms="category" required>
                        <option value="">Select Category</option>
                        <option value="Customer">Customer</option>
                        <option value="Transporter">Transporter</option>
                    </select>
                    @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <h5 class="underline mt-30">Freight Calculation Method<span class="required" style="color: red">*</span></h5>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style"  />
                        <label for="one" class="radio-label">Flat Rate</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style"  />
                        <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" />
                        <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                        @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">From<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="from" required>
                                <option value="">Select From Location</option>
                                @foreach ($destinations as $destination)
                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                            @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">To<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="to" required>
                                <option value="">Select To Location</option>
                                @foreach ($destinations as $destination)
                                <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small> 
                            @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="country">Loading Points</label>
                            <select class="form-control" wire:model.debounce.300ms="loading_point_id" >
                                <option value="">Select Loading Point</option>
                                @foreach ($loading_points as $loading_point)
                                <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small> 
                            @error('loading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="country">OffLoading Points</label>
                            <select class="form-control" wire:model.debounce.300ms="offloading_point_id" >
                                <option value="">Select Offloading Point</option>
                                @foreach ($offloading_points as $offloading_point)
                                <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small> 
                            @error('offloading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="title">Distance</label>
                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Distance" />
                            @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                       </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Cargo</label>
                            <select class="form-control" wire:model.debounce.300ms="selectedCargo">
                                <option value="">Select Cargo</option>
                                @foreach ($cargos as $cargo)
                                <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small> 
                            @error('selectedCargo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
               
                    @if ($cargo_type == "Solid")
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Weight</label>
                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Weight" />
                            @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                       </div>
                    @elseif($cargo_type == "Liquid")
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="title">Weight</label>
                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Weight" />
                            @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                       </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="title">Litreage</label>
                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Litreage" />
                            @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                       </div>
                    @endif
                   
                      

                
                   
                  
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}">{{$currency->name}}</option>
                                @endforeach
                            </select>
                            <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="title">Rate<span class="required" style="color: red">*</span></label>
                        <input type="numnber" step="any" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" required/>
                        @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

