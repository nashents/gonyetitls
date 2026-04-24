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
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search rates...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">Origin
                                    </th>
                                    <th class="th-sm">Destination
                                 
                                    <th class="th-sm">Distance
                                    </th>
                                    <th class="th-sm">Cargo
                                    </th>
                                      <th class="th-sm">FCM
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($rates))
                                <tbody>
                                    @forelse ($rates as $rate)
                                  <tr>
                                    @php
                                        $from = App\Models\Destination::find($rate->from);
                                        $to = App\Models\Destination::find($rate->to);
                                    @endphp
                                    <td>
                                        {{$rate->user?->name}} {{$rate->user?->surname}} <br>
                                        <small class="text-muted"><strong>CreatedOn: </strong>{{$rate->created_at}}</small>
                                    </td>
                                    <td>
                                        @if ($rate->category == "Customer")
                                            <strong>Customer:</strong>{{$rate->customer?->name}}
                                        @elseif ($rate->category == "Transporter")
                                            <strong>Transporter:</strong>{{$rate->transporter?->name}}
                                        @endif
                                    </td>
                                  
                                    <td>
                                        {{$from ? $from->country->name : ""}} {{$from->city}} {{$rate->loading_point ? $rate->loading_point->name : ""}}
                                    </td>
                                    <td>
                                        {{$to ? $to->country->name : ""}} {{$to->city}} {{$rate->offloading_point ? $rate->offloading_point->name : ""}}
                                    </td>
                                    <td>
                                       {{$rate->distance ? $rate->distance."Kms" : ""}}
                                    </td>
                                    <td>
                                        {{ ucfirst($rate->cargo?->name ?? '') }} <br>
                                        <small class="text-muted">
                                            @if ($rate->cargo?->type == "Solid")
                                                <strong>Weight: </strong>{{$rate->weight}} <br>
                                            @elseif($rate->cargo?->type == "Liquid")
                                                <strong>Weight: </strong>{{$rate->weight}} <br>
                                                <strong>Litreage: </strong>{{$rate->litreage}}
                                            @endif
                                            
                                        </small>
                                    </td>
                                      <td>{{$rate->freight_calculation}}</td>
                                    <td>{{$rate->currency ? $rate->currency->name : ""}}</td>
                                    <td>{{$rate->currency ? $rate->currency->symbol : ""}}{{number_format($rate->rate ? $rate->rate : 0,2)}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click.prevent="edit({{$rate->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#"  wire:click="delete({{$rate->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                     
                                </td>
                                  </tr>
                                @empty
                                    <tr>
                                        <td colspan="12">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Rates Found ....
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
                                    @if (isset($rates))
                                        {{ $rates->links() }} 
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Rate?</strong> </center> 
            </div>
            <form wire:submit.prevent="destroy" >
               
            <div class="modal-footer no-border">
                <div class="btn-group" role="group">
                    <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                    <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                </div>
                <!-- /.btn-group -->
            </div>
        </form>
        </div>
    </div>
</div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rateModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-60" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Trip Rate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Category</label>
                                <select class="form-control" wire:model.debounce.300ms="category">
                                    <option value="">Select Category</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if (isset($category) && $category == "Customer")
                                <div class="form-group">
                                    <label for="country">Customers</label>
                                    <select class="form-control" wire:model.debounce.300ms="customer_id" >
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @elseif (isset($category) && $category == "Transporter")
                                <div class="form-group">
                                    <label for="country">Transporters</label>
                                    <select class="form-control" wire:model.debounce.300ms="transporter_id" >
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('transporter_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @endif
                        </div>
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
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Loading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="loading_point_id" >
                                    <option value="">Select Loading Point</option>
                                    @foreach ($loading_points as $loading_point)
                                    <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small><a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('loading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">To<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="to" required>
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">OffLoading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="offloading_point_id" >
                                    <option value="">Select Offloading Point</option>
                                    @foreach ($offloading_points as $offloading_point)
                                    <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small><a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('offloading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="title">Distance</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Distance" />
                                @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Cargo</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCargo">
                                    <option value="">Select Cargo</option>
                                    @foreach ($cargos as $cargo)
                                    <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small><a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                @error('selectedCargo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                   
                        @if ($cargo_type == "Solid")
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Weight(t)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter cargo weight" />
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($cargo_type == "Liquid")
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Weight(t)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter cargo weight" />
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">Litreage(l)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter cargo litreage" />
                                    @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
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
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                     <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small><a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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
        <div class="modal-dialog mw-100 w-60" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Rate <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
              <div class="modal-body">
                 <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Category</label>
                                <select class="form-control" wire:model.debounce.300ms="category">
                                    <option value="">Select Category</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if (isset($category) && $category == "Customer")
                                <div class="form-group">
                                    <label for="country">Customers</label>
                                    <select class="form-control" wire:model.debounce.300ms="customer_id" >
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> <a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @elseif (isset($category) && $category == "Transporter")
                                <div class="form-group">
                                    <label for="country">Transporters</label>
                                    <select class="form-control" wire:model.debounce.300ms="transporter_id" >
                                        <option value="">Select Transporter</option>
                                        @foreach ($transporters as $transporter)
                                            <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('transporter_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small><a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            @endif
                        </div>
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
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Loading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="loading_point_id" >
                                    <option value="">Select Loading Point</option>
                                    @foreach ($loading_points as $loading_point)
                                    <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small><a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('loading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">To<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="to" required>
                                    <option value="">Select To Location</option>
                                    @foreach ($destinations as $destination)
                                    <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">OffLoading Points</label>
                                <select class="form-control" wire:model.debounce.300ms="offloading_point_id" >
                                    <option value="">Select Offloading Point</option>
                                    @foreach ($offloading_points as $offloading_point)
                                    <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Points</a></small><a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('offloading_point_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="title">Distance</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Distance" />
                                @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Cargo</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCargo">
                                    <option value="">Select Cargo</option>
                                    @foreach ($cargos as $cargo)
                                    <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small><a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                @error('selectedCargo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                   
                        @if ($cargo_type == "Solid")
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Weight(t)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="In tons" />
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($cargo_type == "Liquid")
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="title">Weight(t)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="weight" placeholder="In tons" />
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="title">Litreage(l)</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="litreage" placeholder="In litres" />
                                    @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
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
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                     <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small><a href="#" wire:click.prevent="refresh('currencies')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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

