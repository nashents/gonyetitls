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
                              
                                <div class="panel-title">
                                    <a href="#" data-toggle="modal" data-target="#waste_disposalModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Waste Disposal</a>
                                </div>
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search waste  disposals register...">
                                    </div>
                                </div>
                                <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <tr>
                                        <th class="th-sm">Disposal#
                                        </th>
                                        <th class="th-sm">DisposedBy
                                        </th>
                                        <th class="th-sm">DisposedOn
                                        </th>
                                        <th class="th-sm">Movement
                                        </th>
                                        <th class="th-sm">Currency
                                        </th>
                                        <th class="th-sm">Items
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>
                                      </tr>
                                    </thead>
                                    @if (isset($waste_disposals))
                                    <tbody>
                                        @forelse  ($waste_disposals as $waste_disposal)
                                       
                                      <tr>
                                        <td>
                                            {{$waste_disposal->waste_disposal_number}}
                                            <br>
                                            <small>
                                                <strong>CreatedBy:</strong> {{ucfirst($waste_disposal->user ? $waste_disposal->user->name : "")}} {{ucfirst($waste_disposal->user ? $waste_disposal->user->surname : "")}} <br>
                                                <strong>CreatedOn:</strong> {{$waste_disposal->created_at}}
                                            </small>
                                        </td>  
                                        <td>{{ucfirst($waste_disposal->employee ? $waste_disposal->employee->name : "")}} {{ucfirst($waste_disposal->employee ? $waste_disposal->employee->surname : "")}}</td>
                                        <td>{{$waste_disposal->date}}</td>
                                        <td>
                                            {{$waste_disposal->movement}}
                                            @if ($waste_disposal->customer)
                                                {{$waste_disposal->customer ? $waste_disposal->customer->name : ""}}
                                            @endif
                                        </td>
                                        <td>{{$waste_disposal->currency ? $waste_disposal->currency->name : ""}}</td>
                                        <td>
                                            @if ($waste_disposal->waste_disposal_items)
                                                @foreach ($waste_disposal->waste_disposal_items as $item)
                                                    {{$item->waste_type->name}} {{$item->qty}} {{$item->unit_of_measure}}@if (!$loop->last), @endif
                                                @endforeach
                                            @endif
                                        </td>
                                        <td><span class="badge bg-{{($waste_disposal->authorization == 'approved') ? 'success' : (($waste_disposal->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($waste_disposal->authorization == 'approved') ? 'approved' : (($waste_disposal->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('waste_disposals.show', $waste_disposal->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    @if ($waste_disposal->user_id == Auth::user()->id)
                                                        <li><a href="#" wire:click.prevent="edit({{$waste_disposal->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                      </tr>
                                      @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No Waste Disposals Found ....
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
                                        @if (isset($waste_disposals))
                                            @if ($waste_disposals->count()>0)
                                                {{ $waste_disposals->links() }} 
                                            @endif
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
 <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="waste_disposalModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Waste Disposal <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">DisposedBy<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedEmployee" >
                                        <option value="">Select Option</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bin_number">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>  
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bin_number">Type<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control" wire:model.debounce.300ms="movement">
                                        <option value="">Select Option</option>
                                        <option value="Disposal">Disposal</option>
                                        <option value="Transfer">Transfer</option>
                                    </select>
                                    @error('movement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bin_number">Transfered To<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control" wire:model.debounce.300ms="customer_id" {{$movement == "Transfer" ? "" : "disabled"}}>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('trip_groups.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Trips Tracking Group</a></small><a href="#" wire:click.prevent="refresh('tracking_groups')" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bin_number">Currency<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control" wire:model.debounce.300ms="currency_id">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                           <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                        @endforeach
                                    </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>  
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control" wire:model.debounce.300ms="waste_type_id.0">
                                        <option value="">Select Option</option>
                                        @foreach ($waste_types as $waste_type)
                                            <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('waste_type_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description.0" cols="30" rows="2"></textarea>
                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                          
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Use</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="use.0" cols="30" rows="2"></textarea>
                                    @error('use.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                              <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Enter Qty" required>
                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Unit of measure<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="unit_of_measure.0" >
                                        <option value="">Select Option</option>
                                        <option value="Cubic">Cubic</option>
                                        <option value="Litres">Litres</option>
                                        <option value="Kgs">Kgs</option>
                                        <option value="Meters">Meters</option>
                                        <option value="Tons">Tons</option>
                                    </select>
                                    @error('unit_of_measure.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Total Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" placeholder="Enter Amount" required>
                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                                    <select  class="form-control" wire:model.debounce.300ms="waste_type_id.{{$value}}" required>
                                        <option value="">Select Option</option>
                                        @foreach ($waste_types as $waste_type)
                                            <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('waste_type_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description.{{$value}}" cols="30" rows="2"></textarea>
                                    @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Use</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="use.{{$value}}" cols="30" rows="2"></textarea>
                                    @error('use.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Enter Qty" required>
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Unit of measure<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="unit_of_measure.{{$value}}" required>
                                        <option value="">Select Option</option>
                                        <option value="Cubic">Cubic</option>
                                        <option value="Litres">Litres</option>
                                        <option value="Kgs">Kgs</option>
                                        <option value="Meters">Meters</option>
                                        <option value="Tons">Tons</option>
                                    </select>
                                    @error('unit_of_measure.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Total Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{$value}}" placeholder="Enter Amount" required>
                                    @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>     
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> disposal Item</button>
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


    </div>
