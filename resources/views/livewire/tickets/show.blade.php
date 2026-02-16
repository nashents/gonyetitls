<div>
    <div class="row mt-30">
    <x-loading/>
        <div>
            @include('includes.messages')
        </div>
        <!-- /.col-md-3 -->
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#jd" aria-controls="jd" role="tab" data-toggle="tab">Ticket Description</a></li>
                <li role="presentation"><a href="#waste_collection" aria-controls="waste_collection" role="tab" data-toggle="tab">Waste Collection</a></li>
                <li role="presentation"><a href="#inspection" aria-controls="inspection" role="tab" data-toggle="tab">Inspection Results</a></li>
                <li role="presentation"><a href="#requests" aria-controls="requests" role="tab" data-toggle="tab">Requested Items</a></li>
                <li role="presentation"><a href="#parts" aria-controls="parts" role="tab" data-toggle="tab">Dispatched Items</a></li>
                <li role="presentation"><a href="#expenses" aria-controls="expenses" role="tab" data-toggle="tab">Other Expenses</a></li>
                 <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="jd">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Ticket#</th>
                                <td class="w-20 line-height-35">{{$ticket->ticket_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">
                                    @if ($ticket->booking)
                                        {{$ticket->booking->user ? $ticket->booking->user->name : ""}} {{$ticket->booking->user ? $ticket->booking->user->surname : ""}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedOn</th>
                                <td class="w-20 line-height-35">{{$ticket->created_at}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Ticket For</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($ticket->booking->horse))
                                        Horse |  {{$ticket->booking->horse->registration_number}} {{$ticket->booking->horse->fleet_number ? "(".$ticket->booking->horse->fleet_number.")" : ""}}
                                        @elseif(isset($ticket->booking->vehicle))
                                        Vehicle |  {{$ticket->booking->vehicle->registration_number}} {{$ticket->booking->vehicle->fleet_number ? "(".$ticket->booking->vehicle->fleet_number.")" : ""}}
                                        @elseif(isset($ticket->booking->asset))
                                        Asset | {{$ticket->booking->asset->product->brand ? $ticket->booking->asset->product->brand->name : ""}} {{ucfirst($ticket->booking->asset->product ? $ticket->booking->asset->product->name : "")}}  {{$ticket->booking->asset->serial_number}}
                                        @elseif(isset($ticket->booking->trailer))
                                        Trailer | {{$ticket->booking->trailer->registration_number}} {{$ticket->booking->trailer->registration_number ? "(".$ticket->booking->trailer->registration_number.")" : ""}} 
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Assigned To</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($ticket->booking->employees) && $ticket->booking->employees->count()>0)
                                                @foreach ($ticket->booking->employees as $mechanic)
                                                    {{ $mechanic->name }} {{ $mechanic->surname }}
                                                    <br>
                                                @endforeach
                                            @elseif(isset($ticket->booking->vendor))
                                                {{ucfirst($ticket->booking->vendor->name)}}  
                                            @endif
                                </td>
                            </tr>
                       
                            <tr>
                                <th class="w-10 text-center line-height-35">In Date & Time</th>
                                <td class="w-20 line-height-35">{{$ticket->in_date}} @ {{$ticket->in_time}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Out Date & Time</th>
                                <td class="w-20 line-height-35">{{$ticket->out_date}} @ {{$ticket->out_time}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Ticket Closed On</th>
                                <td class="w-20 line-height-35">{{$ticket->out_date}} @ {{$ticket->out_time}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Job Type</th>
                                <td class="w-20 line-height-35">{{$ticket->service_type ? $ticket->service_type->name : ""}}</td>
                            </tr>
                            @if ($ticket->odometer)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Mileage</th>
                                    <td class="w-20 line-height-35">{{$ticket->odometer}}Kms</td>
                                </tr>
                            @endif
                            @if ($ticket->next_service)
                                    <tr>
                                        <th class="w-10 text-center line-height-35">Next Service</th>
                                        <td class="w-20 line-height-35">{{$ticket->next_service}}Kms</td>
                                    </tr>
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Station</th>
                                <td class="w-20 line-height-35">{{$ticket->station}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Problem Description</th>
                                <td class="w-20 line-height-35">{{$ticket->booking ? $ticket->booking->description : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Initial Diagnosis</th>
                                <td class="w-20 line-height-35">{{$ticket->initial_diagnosis}} 
                                    @if (isset($employee_ids) || $ticket->booking->vendor)
                                        @if ((in_array($employee->id, $employee_ids)) || ($user->id == $ticket->booking->user_id && $ticket->booking->vendor) )
                                            <a href="#" wire:click.prevent="showInitialDiagnosis({{$ticket->id}})"><i class="fas fa-edit"></i></a>
                                        @endif
                                    @endif
                                 </td>
                            </tr>
                            @if ($work_dones)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Work Done</th>
                                    <td class="w-20 line-height-35">
                                      
                                            @php
                                                $count = 1
                                            @endphp
                                            @foreach ($work_dones as $item)
                                            <div class="card-body mt-10 mb-10" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px; " >
                                                <small> <strong>{{$count++}})</strong> <strong>DoneBy: </strong>{{ $item->artisan }} {{ $item->employee ? $item->employee->name : "" }} {{ $item->employee ? $item->employee->surname : "" }} <strong>Work Done: </strong> {{ $item->job_description }} <strong>Spares Used: </strong> {{ $item->spares }}  <strong>Start Time: </strong> {{ $item->start_time }} <strong>End Time: </strong> {{ $item->end_time }} 
                                                </small>
                                            </div>
                                            @endforeach
                                    
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Final Mechanic Comments</th>
                                <td class="w-20 line-height-35">{{$ticket->report}}</td>
                            </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$ticket->status == 1 ? "warning" : "success"}}">{{$ticket->status == 1 ? "Open" : "Closed"}}</span></td>
                            </tr>
                            @if ($ticket->closed_by_id)
                                @php
                                    $user = App\Models\User::find($ticket->closed_by_id)
                                @endphp
                                 <tr>
                                    <th class="w-10 text-center line-height-35">Closed By</th>
                                    <td class="w-20 line-height-35">{{ $user->name }} {{ $user->surname }}</td>
                                </tr>
                            @endif
                            @if ($ticket->closed_comments)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Closing Comments</th>
                                    <td class="w-20 line-height-35">{{$ticket->closed_comments }} </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    @if ($before_attachments->count()>0 || $after_attachments->count()>0)
                        <div class="card mt-30 mb-30">
                            <div class="card-body">
                                <small>Before Images</small>
                                <br>
                                @foreach ($before_attachments as $attachment)
                                    <a href="{{asset('images/uploads/'.$attachment->filename)}}"><img src="{{asset('images/uploads/'.$attachment->filename)}}" alt="" style="width: 25%; height:25%"></a>
                                @endforeach
                            </div>
                            <div class="card-body">
                                <small>After Images</small>
                                <br>
                                @foreach ($after_attachments as $attachment)
                                    <a href="{{asset('images/uploads/'.$attachment->filename)}}"><img src="{{asset('images/uploads/'.$attachment->filename)}}" alt="" style="width: 25%; height:25%"></a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                   
                    <hr>
                    <br>
                  

                    <form wire:submit.prevent="updateTicketCard()">
                        @if (isset($employee_ids) || $ticket->booking->vendor)
                            @if ((in_array($employee->id, $employee_ids)) || ($user->id == $ticket->booking->user_id && $ticket->booking->vendor) )
                                <div class="card mt-30 mb-30">
                                    <h5 class="underline mt-30">{{$this->equipment}} Service History</h5>
                                    <div class="card-body mt-30 mb-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px; height: 150px; overflow: auto" >
                                        @if ($service_history)
                                            @foreach ($service_history as $equipment_ticket)
                                                @php
                                                    $mechanic = App\Models\Employee::find($equipment_ticket->reported_by_id);
                                                @endphp
                                                <a href="{{route('tickets.show', $equipment_ticket->id)}}" target="_blank" style="color: blue"> <strong>Ticket#</strong> {{$equipment_ticket->ticket_number}} <strong>In:</strong> {{$equipment_ticket->in_date}} <strong>@</strong> {{$equipment_ticket->in_time}} {{$mechanic?->name}} {{$mechanic?->surname}} <strong>Comments:</strong> {{$equipment_ticket->report}}</a>
                                                <br>
                                                <br>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="acknowledgement"   class="line-style blue-style" required/>
                                        <label for="one" class="radio-label">
                                            <strong><span class="required" style="color: red">*</span> I acknowledge that i have reviewed the service history of {{$this->equipment}}
                                            </strong>
                                        </label>
                                        @error('acknowledgement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="card mt-30 mb-30">
                                    <h5 class="underline mt-30">Upload Evidence </h5>
                                    <div class="card-header">
                                        <a href="" data-toggle="modal" data-target="#attachmentModal" class="btn btn-default"><i class="fa fa-paperclip"></i>Attach Images</a>    
                                    </div>
                                </div>
          
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Date All Work Completed<span class="required" style="color: red">*</span></label>
                                            <input type="date" wire:model.debounce.300ms="out_date" class="form-control" required>
                                            @error('out_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="">Time All Work Completed<span class="required" style="color: red">*</span></label>
                                        <input type="time" wire:model.debounce.300ms="out_time" class="form-control" required>
                                        @error('out_time') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="">Next Service Due</label>
                                        <input type="number" step="any" min="{{ $ticket->odometer }}" wire:model.debounce.300ms="next_service" class="form-control" placeholder="When is the next service due (mileage)" >
                                        @error('next_service') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Final Mechanic Comments<span class="required" style="color: red">*</span></label>
                                            <textarea wire:model.debounce.300ms="notes" cols="30" rows="7" class="form-control" placeholder="Write job observations" required></textarea>
                                        </div>
                                    </div>
                                </div>
                              
                               
                               @if ((int) $ticket->status === 1)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="btn-group pull-right mt-10" >
                                                <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Update Ticket</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    </form>
                </div>
                <div role="tabpanel" class="tab-pane " id="waste_collection">
                    <div class="mb-10 mt-5">
                         <a href="#" data-toggle="modal" data-target="#waste_collectionModal"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Waste Collection</a>
                    </div>
                   
                    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <tr>
                            <th class="th-sm">Collection#
                            </th>
                            <th class="th-sm">CreatedBy
                            </th>
                            <th class="th-sm">CreatedOn
                            </th>
                            <th class="th-sm">Items
                            </th>
                            <th class="th-sm">Auth
                            </th>
                            <th class="th-sm">Actions
                            </th>
                            </tr>
                        </thead>
                        @if (isset($waste_collections))
                        <tbody>
                            @forelse  ($waste_collections as $waste_collection)
                            
                            <tr>
                            <td>
                                {{$waste_collection->waste_collection_number}}
                            </td>
                            <td>{{ucfirst($waste_collection->user ? $waste_collection->user->name : "")}} {{ucfirst($waste_collection->user ? $waste_collection->user->surname : "")}}</td>
                            <td>{{$waste_collection->created_at}}</td>
                            <td>
                                @if ($waste_collection->waste_collection_items)
                                @php
                                    $i = 1
                                @endphp
                                    @foreach ($waste_collection->waste_collection_items as $item)
                                        {{$i++}}) {{$item->waste_type ? $item->waste_type->name : ""}} {{$item->description}} {{$item->qty}} {{$item->unit_of_measure}}@if (!$loop->last), <br> @endif
                                    @endforeach
                                @endif
                            </td>
                            <td><span class="badge bg-{{($waste_collection->authorization == 'approved') ? 'success' : (($waste_collection->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($waste_collection->authorization == 'approved') ? 'approved' : (($waste_collection->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{route('waste_collections.show', $waste_collection->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                        @if ($waste_collection->user_id == Auth::user()->id && $waste_collection->authorization == 'pending')
                                            <li><a href="#" wire:click.prevent="waste_collection_edit({{$waste_collection->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                            {{-- <li><a href="#" wire:click.prevent="delete({{$waste_collection->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                                        @endif
                                            
                                    </ul>
                                </div>
                            </td>
                            </tr>
                            @empty
                            <tr>
                            <td colspan="11">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Waste Collections Found ....
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
                            @if (isset($waste_collections))
                                @if ($waste_collections->count()>0)
                                    {{ $waste_collections->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav>
                </div>
                <div role="tabpanel" class="tab-pane " id="inspection">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspection#</th>
                                <td class="w-20 line-height-35">
                                    @if ($ticket->inspection->inspection_results->count()>0)
                                        {{$ticket->inspection ? $ticket->inspection->inspection_number : ""}}
                                    @else
                                        {{$ticket->inspection ? $ticket->inspection->inspection_number : ""}} <a href="{{route('inspections.show', $ticket->inspection->id)}}" style="color: blue" target="_blank"><i class="fas fa-edit color-primary"></i>Click me to inspect</a>
                                    @endif
                                    
                                </td>
                            </tr>
                          
                        </tbody>
                    </table>
                    <table id="inspection_resultsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Inspection Item
                            </th>
                            <th class="th-sm">Status
                            </th>
                            <th class="th-sm">Comments
                            </th>
                            <th class="th-sm">Cost
                            </th>
                            <th class="th-sm">Hours
                            </th>

                          </tr>
                        </thead>

                        <tbody>
                           @forelse ($inspection_results as  $inspection_result)
                            <tr>
                                <td>{{$inspection_result->inspection_type ? $inspection_result->inspection_type->name : ""}}</td>
                                <td><span class="badge bg-{{($inspection_result->status == 'green') ? 'success' : (($inspection_result->status == 'red') ? 'danger' : 'warning') }}">{{($inspection_result->status == 'green') ? 'No Attention' : (($inspection_result->status == 'red') ? 'Immediate Attention' : 'Intermediate Attetion') }}</span></td>
                                <td>{{$inspection_result->comments}}</td>
                                <td>{{$inspection_result->cost}}</td>
                                <td>{{$inspection_result->hours}}</td>
                            </tr>
                            @empty
                                <tr>
                                <td colspan="5">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Inspection Results Found ....
                                    </div>
                                    
                                </td>
                                </tr> 
                            @endforelse
                        </tbody>
                      </table>
                </div>

                <div role="tabpanel" class="tab-pane " id="requests">
                    @livewire('ticket-requests.index', ['ticket' => $ticket])
                </div>
                <div role="tabpanel" class="tab-pane " id="parts">
                    @livewire('ticket-inventories.index', ['ticket' => $ticket])
                </div>
                <div role="tabpanel" class="tab-pane " id="expenses">
                    @livewire('ticket-expenses.index', ['ticket' => $ticket])
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                    @livewire('documents.index', ['id' => $ticket->id,'category'=>'ticket'])
                </div>
                <br>
                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                            <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                        </div>
                    </div>
                </div>
                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>

     <div data-backdrop="static" data-keyboard="false" class="modal fade" id="waste_collectionDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Waste Collection Record?</strong> </center> 
                </div>
                <form wire:submit.prevent="destroy()" >
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Attachment(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="addAttachments()" >
                <div class="modal-body">
                    <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="qty">Timeframe<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="timeframe" required>
                                <option value="">Select Time Frame</option>
                                <option value="Before">Before</option>
                                <option value="After">After</option>
                            </select>
                            @error('timeframe') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="qty">Attach Image(s)<span class="required" style="color: red">*</span></label>
                            <input type="file" class="form-control"  wire:model.debounce.300ms="image" placeholder="Upload Images" multiple required>
                            @error('image') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="workDoneModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Work Done(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                  <form wire:submit.prevent="storeWorkDone()">
                        <div class="card-body mt-30 mb-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px; " >
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Artisan Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="artisan.0" class="form-control">
                                        <option value="">Select Artisan Type</option>
                                        <option value="Mechanic">Mechanic</option>
                                        <option value="Auto Mechanic">Auto Mechanic</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @error('artisan.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Work Done<span class="required" style="color: red">*</span></label>
                                    <textarea wire:model.debounce.300ms="job_description.0" cols="30" rows="2" class="form-control" placeholder="Write work done" required></textarea>
                                    @error('job_description.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Spares Used</label>
                                    <textarea wire:model.debounce.300ms="spares.0" cols="30" rows="2" class="form-control" placeholder="Write Spares used"></textarea>
                                    @error('spares.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Start Time</label>
                                    <input type="datetime-local" wire:model.debounce.300ms="work_start_time.0" class="form-control">
                                    @error('work_start_time.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">End Time</label>
                                    <input type="datetime-local" wire:model.debounce.300ms="work_end_time.0" class="form-control" >
                                    @error('work_end_time.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        </div>
                        @foreach ($work_inputs as $key => $value)
                          <div class="card-body mt-30 mb-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;" >
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="">Artisan Type<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="artisan.{{$value}}" class="form-control">
                                            <option value="">Select Artisan Type</option>
                                            <option value="Mechanic">Mechanic</option>
                                            <option value="Auto Mechanic">Auto Mechanic</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        @error('artisan.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="">Work Done<span class="required" style="color: red">*</span></label>
                                        <textarea wire:model.debounce.300ms="job_description.{{$value}}" cols="30" rows="2" class="form-control" placeholder="Write work done" required></textarea>
                                        @error('job_description.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="">Spares Used</label>
                                        <textarea wire:model.debounce.300ms="spares.{{$value}}" cols="30" rows="2" class="form-control" placeholder="Write spares used" ></textarea>
                                        @error('spares.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Start Time</label>
                                        <input type="datetime-local" wire:model.debounce.300ms="work_start_time.{{$value}}" class="form-control" >
                                        @error('work_start_time.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror  
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="">End Time</label>
                                        <input type="datetime-local" wire:model.debounce.300ms="work_end_time.{{$value}}" class="form-control" >
                                        @error('work_end_time.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-1" style="margin-top:27px;">
                                    <div class="form-group">
                                        <label for=""></label>
                                        <button class="btn btn-danger btn-rounded btn-xs"   wire:click.prevent="workRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                          
                            </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="workAdd({{$w}})"> <i class="fa fa-plus"></i> Work</button>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group mt-10" >
                                    <button type="submit" class="btn bg-success btn-wide btn-rounded btn-sm" > <i class="fa fa-save"></i>Save Work Done</button>
                                </div>
                            </div>
                        </div>

                    </form>
            </div>
        </div>
    </div>
 
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="initialDiagnosisModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Initial Diagnosis <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateDiagnosis()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="qty">Initial Diagnosis<span class="required" style="color: red">*</span></label>
                        <textarea class="form-control"  wire:model.debounce.300ms="initial_diagnosis" cols="30" rows="3" required></textarea>
                        @error('initial_diagnosis') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="waste_collectionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Waste Collection <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="waste_collection_store()" >
                    <div class="modal-body">
                         <div class="form-group">
                            <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                            <select  class="form-control" wire:model.debounce.300ms="waste_type_id.0">
                                <option value="">Select Option</option>
                                @foreach ($waste_types as $waste_type)
                                    <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('waste_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Type</a></small> <a href="#" wire:click.prevent="refresh('waste_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @error('waste_type_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>  
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description.0" cols="30" rows="2"></textarea>
                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                               <div class="form-group">
                                    <label for="name">Collection Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date.0" placeholder="Enter Date" required>
                                    @error('date.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                                   <select class="form-control" wire:model.debounce.300ms="unit_of_measure.0" required>
                                        <option value="">Select Option</option>
                                        @foreach ($unit_of_measures as $unit_of_measure)
                                             <option value="{{$unit_of_measure->name}}">{{$unit_of_measure->name}} ({{$unit_of_measure->abbreviation}})</option>
                                         @endforeach
                                    </select>
                                    @error('unit_of_measure.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Waste Recepticles</label>
                                    <select class="form-control" wire:model.debounce.300ms="waste_receptacle_id.0" >
                                        <option value="">Select Option</option>
                                        @foreach ($waste_receptacles as $waste_receptacle)
                                             <option value="{{$waste_receptacle->id}}">{{$waste_receptacle->name}}</option>
                                        @endforeach
                                    </select>
                                     <small><a href="{{ route('waste_receptacles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Receptacle</a></small> <a href="#" wire:click.prevent="refresh('waste_receptacles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('waste_receptacle_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Collected From<span class="required" style="color: red">*</span></label>
                                   <input type="text" class="form-control" wire:model.debounce.300ms="collected_from.0" placeholder="Enter Collection Point" required>
                                    @error('collected_from.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @foreach ($inputs as $key => $value)
                        <div class="form-group">
                            <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                            <select  class="form-control" wire:model.debounce.300ms="waste_type_id.{{$value}}" required>
                                <option value="">Select Option</option>
                                @foreach ($waste_types as $waste_type)
                                    <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('waste_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Type</a></small> <a href="#" wire:click.prevent="refresh('waste_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @error('waste_type_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description.{{$value}}" cols="30" rows="2"></textarea>
                                    @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Collection Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date.{{$value}}" placeholder="Enter Date" required>
                                    @error('date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Enter Qty" required>
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Unit of measure<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="unit_of_measure.{{$value}}" required>
                                        <option value="">Select Option</option>
                                        @foreach ($unit_of_measures as $unit_of_measure)
                                            <option value="{{$unit_of_measure->name}}">{{$unit_of_measure->name}} ({{$unit_of_measure->abbreviation}})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_of_measure.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Waste Recepticle</label>
                                    <select class="form-control" wire:model.debounce.300ms="waste_receptacle_id.{{$value}}" >
                                        <option value="">Select Option</option>
                                        @foreach ($waste_receptacles as $waste_receptacle)
                                             <option value="{{$waste_receptacle->id}}">{{$waste_receptacle->name}}</option>
                                        @endforeach
                                    </select>
                                     <small><a href="{{ route('waste_receptacles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Receptacle</a></small> <a href="#" wire:click.prevent="refresh('waste_receptacles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('waste_receptacle_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Collected From<span class="required" style="color: red">*</span></label>
                                   <input type="text" class="form-control" wire:model.debounce.300ms="collected_from.{{$value}}" placeholder="Enter Collection Point" required>
                                    @error('collected_from.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Collection Item</button>
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

     <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="waste_collectionEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Waste Collection <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="waste_collection_update()" >
                    <div class="modal-body">
                    @if ($waste_collection_items)
                         @foreach ($waste_collection_items as $key => $value)
                              <div class="form-group">
                            <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                            <select  class="form-control" wire:model.debounce.300ms="current_waste_type_id.{{$key}}">
                                <option value="">Select Option</option>
                                @foreach ($waste_types as $waste_type)
                                    <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('waste_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Type</a></small> <a href="#" wire:click.prevent="refresh('waste_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @error('current_waste_type_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>  
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="current_description.{{$key}}" cols="30" rows="2"></textarea>
                                    @error('current_description.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                               <div class="form-group">
                                    <label for="name">Collection Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="current_date.{{$key}}" placeholder="Enter Date" required>
                                    @error('current_date.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}" placeholder="Enter Qty" required>
                                    @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Balance<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_balance.{{$key}}" placeholder="Enter Balance" required>
                                    @error('current_balance.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Unit of measure<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="current_unit_of_measure.{{$key}}" >
                                        <option value="">Select Option</option>
                                        @foreach ($unit_of_measures as $unit_of_measure)
                                            <option value="{{$unit_of_measure->name}}">{{$unit_of_measure->name}} ({{$unit_of_measure->abbreviation}})</option>
                                        @endforeach
                                    </select>
                                    @error('current_unit_of_measure.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Waste Recepticle</label>
                                    <select class="form-control" wire:model.debounce.300ms="current_waste_receptacle_id.{{$key}}" >
                                        <option value="">Select Option</option>
                                        @foreach ($waste_receptacles as $waste_receptacle)
                                             <option value="{{$waste_receptacle->id}}">{{$waste_receptacle->name}}</option>
                                        @endforeach
                                    </select>
                                     <small><a href="{{ route('waste_receptacles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Receptacle</a></small> <a href="#" wire:click.prevent="refresh('waste_receptacles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('current_waste_receptacle_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                               <div class="form-group">
                                    <label for="name">Collected From<span class="required" style="color: red">*</span></label>
                                   <input type="text" class="form-control" wire:model.debounce.300ms="current_collected_from.{{$key}}" placeholder="Enter Collection Point" required>
                                    @error('current_collected_from.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @endif
                       
                       
                    @foreach ($inputs as $key => $value)
                        <div class="form-group">
                            <label for="bin_number">Waste Type<span class="required" style="color: red">*</span></label>
                            <select  class="form-control" wire:model.debounce.300ms="waste_type_id.{{$value}}" required>
                                <option value="">Select Option</option>
                                @foreach ($waste_types as $waste_type)
                                    <option value="{{$waste_type->id}}">{{$waste_type->name ? "Type: ".$waste_type->name : ""}} {{$waste_type->category ? "Category: ".$waste_type->category : ""}} {{$waste_type->general_composition ? "Composition: ".$waste_type->general_composition : ""}} {{$waste_type->generation_area ? "Area: ".$waste_type->generation_area: ""}}</option>
                                @endforeach
                            </select>
                            <small><a href="{{ route('waste_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Type</a></small> <a href="#" wire:click.prevent="refresh('waste_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            @error('waste_type_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Additional Information</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description.{{$value}}" cols="30" rows="2"></textarea>
                                    @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Collection Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date.{{$value}}" placeholder="Enter Date" required>
                                    @error('date.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Enter Qty" required>
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Unit of measure<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="unit_of_measure.{{$value}}" required>
                                        <option value="">Select Option</option>
                                        @foreach ($unit_of_measures as $unit_of_measure)
                                            <option value="{{$unit_of_measure->name}}">{{$unit_of_measure->name}} ({{$unit_of_measure->abbreviation}})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_of_measure.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                    <label for="name">Waste Recepticle</label>
                                    <select class="form-control" wire:model.debounce.300ms="waste_receptacle_id.{{$value}}" >
                                        <option value="">Select Option</option>
                                        @foreach ($waste_receptacles as $waste_receptacle)
                                             <option value="{{$waste_receptacle->id}}">{{$waste_receptacle->name}}</option>
                                        @endforeach
                                    </select>
                                     <small><a href="{{ route('waste_receptacles.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Waste Receptacle</a></small> <a href="#" wire:click.prevent="refresh('waste_receptacles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('waste_receptacle_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                             <div class="col-md-4">
                               <div class="form-group">
                                    <label for="name">Collected From<span class="required" style="color: red">*</span></label>
                                   <input type="text" class="form-control" wire:model.debounce.300ms="collected_from.{{$key}}" placeholder="Enter Collection Point" required>
                                    @error('collected_from.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Collection Item</button>
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
