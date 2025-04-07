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
                                <a href="#" data-toggle="modal" data-target="#workshop_serviceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Service</a>
                                <a href="#" wire:click="exportWorkshopServicesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportWorkshopServicesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportWorkshopServicesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                       
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">WS#
                                    </th>
                                    {{-- <th class="th-sm">CreatedBy
                                    </th> --}}
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Trailer
                                    </th>
                                    <th class="th-sm">Load Status
                                    </th>
                                    <th class="th-sm">In
                                    </th>
                                    <th class="th-sm">Out
                                    </th>
                                    <th class="th-sm">Day(s)
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amt
                                    </th>
                                    <th class="th-sm">Bal
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($workshop_services))
                                <tbody>
                                    @forelse ($workshop_services as $workshop_service)
                                  <tr>
                                    <td>{{$workshop_service->workshop_service_number}}</td>
                                    {{-- <td>{{$workshop_service->user ? $workshop_service->user->name : ""}} {{$workshop_service->user ? $workshop_service->user->surname : ""}}</td> --}}
                                    <td>{{$workshop_service->transporter ? $workshop_service->transporter->name : ""}}</td>
                                    <td>{{$workshop_service->horse ? $workshop_service->horse->registration_number : ""}} {{$workshop_service->horse->fleet_number ? "(".$workshop_service->horse->fleet_number.")" : ""}}</td>
                                    <td>{{$workshop_service->trailer ? $workshop_service->trailer->registration_number : ""}} {{$workshop_service->trailer->fleet_number ? "(".$workshop_service->trailer->fleet_number.")" : ""}}</td>
                                    <td>{{$workshop_service->load_status}}</td>
                                    <td>{{$workshop_service->start_date}}</td>
                                    <td>{{$workshop_service->end_date}}</td>
                                    <td>{{$workshop_service->days}}</td>
                                    <td>{{$workshop_service->currency ? $workshop_service->currency->name : ""}}</td>
                                    <td>{{$workshop_service->currency ? $workshop_service->currency->symbol : ""}}{{$workshop_service->amount ? number_format($workshop_service->amount,2) : ""}}</td>
                                    <td>{{$workshop_service->currency ? $workshop_service->currency->symbol : ""}}{{$workshop_service->balance ? number_format($workshop_service->balance,2) : ""}}</td>
                                    <td><span class="label label-{{($workshop_service->status == 'Paid') ? 'success' : (($workshop_service->status == 'Partial') ? 'warning' : 'danger') }}">{{ $workshop_service->status }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('workshop_services.show', $workshop_service->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$workshop_service->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#workshop_serviceDeleteModal{{ $workshop_service->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('workshop_services.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Services Found ....
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
                                    @if (isset($workshop_services))
                                        {{ $workshop_services->links() }} 
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="workshop_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Service <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="account_id" class="form-control" required >
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Vendors<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required >
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Transporters<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required >
                                    <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Horses</label>
                                <select wire:model.debounce.300ms="horse_id" class="form-control" >
                                    <option value="">Select Horse</option>
                                    @foreach ($horses as $horse)
                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->make ? $horse->make->name :""}} {{$horse->model ? $horse->model->name :""}}</option>
                                    @endforeach
                                    
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Trailers</label>
                                <select wire:model.debounce.300ms="trailer_id" class="form-control"  >
                                    <option value="">Select Trailer</option>
                                  
                                    @foreach ($trailers as $trailer)
                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                    @endforeach
                                   
                                </select>
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Load Status<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedLoadStatus" class="form-control" required>
                                   <option value="">Select Status</option>
                                   <option value="Loaded">Loaded</option>
                                   <option value="Offloaded">Offloaded</option>
                               </select>
                             
                                @error('selectedLoadStatus') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Days<span class="required" style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="days" required/>
                                @error('days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">In<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" required />
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Out</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" />
                                @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currency</label>
                               <select wire:model.debounce.300ms="selectedCurrency" class="form-control">
                                   <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                @endforeach
                               </select>
                             
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" >
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                      <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="workshop_serviceEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Service <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="account_id" class="form-control" required >
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Vendors<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required >
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                </select>
                                <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Transporters<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required >
                                    <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Horses</label>
                                <select wire:model.debounce.300ms="horse_id" class="form-control" >
                                    <option value="">Select Horse</option>
                                    @foreach ($horses as $horse)
                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->make ? $horse->make->name :""}} {{$horse->model ? $horse->model->name :""}}</option>
                                    @endforeach
                                    
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Trailers</label>
                                <select wire:model.debounce.300ms="trailer_id" class="form-control"  >
                                    <option value="">Select Trailer</option>
                                  
                                    @foreach ($trailers as $trailer)
                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                    @endforeach
                                   
                                </select>
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Load Status<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedLoadStatus" class="form-control" required>
                                   <option value="">Select Status</option>
                                   <option value="Loaded">Loaded</option>
                                   <option value="Offloaded">Offloaded</option>
                               </select>
                             
                                @error('selectedLoadStatus') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Days<span class="required" style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="days" required/>
                                @error('days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">In<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="start_date" required />
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Out</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="end_date" />
                                @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Currency<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                   <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                @endforeach
                               </select>
                             
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" >
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                      <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

