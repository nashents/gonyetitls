<div>
    <div class="row mt-30">

        <div>
            @include('includes.messages')
        </div>
        
        <!-- /.col-md-3 -->
        <div class="col-md-10 col-md-offset-1">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#jd" aria-controls="jd" role="tab" data-toggle="tab">Ticket Description</a></li>
                <li role="presentation"><a href="#inspection" aria-controls="inspection" role="tab" data-toggle="tab">Inspection Results</a></li>
                <li role="presentation"><a href="#parts" aria-controls="parts" role="tab" data-toggle="tab">Inventory</a></li>
                <li role="presentation"><a href="#expenses" aria-controls="expenses" role="tab" data-toggle="tab">Expenses</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="jd">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Booking#</th>
                                <td class="w-20 line-height-35">{{$ticket->booking ? $ticket->booking->booking_number : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">BookedBy</th>
                                <td class="w-20 line-height-35">
                                    @if ($ticket->booking)
                                    {{$ticket->booking->user ? $ticket->booking->user->name : ""}} {{$ticket->booking->user ? $ticket->booking->user->surname : ""}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Booked On</th>
                                <td class="w-20 line-height-35">{{$ticket->booking ? $ticket->booking->created_at : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Booking For</th>
                                <td class="w-20 line-height-35">
                                    @if (isset($ticket->booking->horse))
                                        Horse | {{ucfirst($ticket->booking->horse->horse_make ? $ticket->booking->horse->horse_make->name : "")}} {{ucfirst($ticket->booking->horse->horse_model ? $ticket->booking->horse->horse_model->name : "" )}} {{ucfirst($ticket->booking->horse->registration_number)}}
                                        @elseif(isset($ticket->booking->vehicle))
                                        Vehicle | {{ucfirst($ticket->booking->vehicle->vehicle_make->name)}} {{ucfirst($ticket->booking->vehicle->vehicle_model->name)}} {{ucfirst($ticket->booking->vehicle->registration_number)}}
                                        @elseif(isset($ticket->booking->trailer))
                                        Trailer | {{ucfirst($ticket->booking->trailer->make)}} {{ucfirst($ticket->booking->trailer->model)}} {{ucfirst($ticket->booking->trailer->registration_number)}}
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
                                <th class="w-10 text-center line-height-35">Ticket Opened</th>
                                <td class="w-20 line-height-35">{{$ticket->in_date}} @ {{$ticket->in_time}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Ticket Closed</th>
                                <td class="w-20 line-height-35">{{$ticket->out_date}} @ {{$ticket->out_time}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Service Type</th>
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
                                <th class="w-10 text-center line-height-35">Mechanic Comments</th>
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
                    <hr>
                    <br>
         

            <div class="card">
                <h5 class="card-header">
                    @php
                    $employees = $ticket->employees;
                    foreach ($employees as $employee) {
                        $employee_ids[] = $employee->id;
                    }
                @endphp
                @if (isset($employee_ids))
                    @if (in_array(Auth::user()->employee->id, $employee_ids))
                        <a href="" data-toggle="modal" data-target="#attachmentModal" class="btn btn-default"><i class="fa fa-paperclip"></i>Attach Images</a>    
                    @endif
                @elseif ($ticket->booking->vendor)
                    @if (Auth::user()->id == $ticket->booking->user_id)
                        <a href="" data-toggle="modal" data-target="#attachmentModal" class="btn btn-default"><i class="fa fa-paperclip"></i>Attach Images</a>    
                    @endif
                @endif
              
                </h5>
                @if ($before_attachments->count()>0)
                <div class="card-body">
                  
                    <small>Before Images</small>
                    <br>
                        @foreach ($before_attachments as $attachment)
                          <a href="{{asset('images/uploads/'.$attachment->filename)}}"><img src="{{asset('images/uploads/'.$attachment->filename)}}" alt="" style="width: 25%; height:25%"></a>
                        @endforeach
                   
                </div>
                @endif
                @if ($after_attachments->count()>0)
                <div class="card-body">
                   
                    <small>After Images</small>
                    <br>
                        @foreach ($after_attachments as $attachment)
                          <a href="{{asset('images/uploads/'.$attachment->filename)}}"><img src="{{asset('images/uploads/'.$attachment->filename)}}" alt="" style="width: 25%; height:25%"></a>
                        @endforeach
                   
                </div>
                @endif
            </div>
            <hr>
            <br>
            @php
            $employees = $ticket->employees;
            foreach ($employees as $employee) {
                $employee_ids[] = $employee->id;
            }
        @endphp
        @if (isset($employee_ids))
        @if (in_array(Auth::user()->employee->id, $employee_ids))
        <form wire:submit.prevent="updateTicketCard()">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Date Completed<span class="required" style="color: red">*</span></label>
                        <input type="date" wire:model.debounce.300ms="out_date" class="form-control" required>
                        @error('out_date') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="">Time Completed<span class="required" style="color: red">*</span></label>
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
                        <label for="">Job Card Comments<span class="required" style="color: red">*</span></label>
                        <textarea wire:model.debounce.300ms="notes" cols="30" rows="7" class="form-control" placeholder="Write job observations" required></textarea>
                    </div>
                </div>
            </div>
         
        @if ($ticket->status == 1)
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right mt-10" >
                    <button type="submit"  class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Update Ticket</button>
                </div>
            </div>
        </div>
        @endif
          
         
        </form>
        @endif
        @endif
        @if ($ticket->booking->vendor)
        <form wire:submit.prevent="updateTicketCard()">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Date Completed<span class="required" style="color: red">*</span></label>
                        <input type="date" wire:model.debounce.300ms="out_date" class="form-control" required>
                        @error('out_date') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="">Time Completed<span class="required" style="color: red">*</span></label>
                    <input type="time" wire:model.debounce.300ms="out_time" class="form-control" required>
                    @error('out_time') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="">Next Service Due</label>
                    <input type="number" step="any" min="{{ $ticket->odometer }}" wire:model.debounce.300ms="next_service" class="form-control" placeholder="When is the next service due (mileage)">
                    @error('next_service') <span class="text-danger error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Job Card Comments<span class="required" style="color: red">*</span></label>
                        <textarea wire:model.debounce.300ms="notes" cols="30" rows="7" class="form-control" placeholder="Write job observations" required></textarea>
                    </div>
                </div>
            </div>
         
        @if ($ticket->status == 1)
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right mt-10" >
                    <button type="submit"  class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Update Ticket</button>
                </div>
            </div>
        </div>
        @endif
          
         
        </form>
        @endif
     

                </div>
                <div role="tabpanel" class="tab-pane " id="inspection">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspection#</th>
                                <td class="w-20 line-height-35">
                                    @if ($ticket->inspection->inspection_results->count()>0)
                                    <a href="{{route('tickets.show', $ticket->id)}}" style="color: blue" target="_blank">{{$ticket->inspection ? $ticket->inspection->inspection_number : ""}}</a>
                                    @endif
                                    @if ($ticket->inspection->status == 1)
                                     <a href="{{route('inspections.show', $ticket->inspection->id)}}" style="color: blue" target="_blank">{{$ticket->inspection ? $ticket->inspection->inspection_number : ""}}</a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35">
                                    @if ($ticket->inspection)
                                    <span class="badge bg-{{$ticket->inspection->status == 1 ? "warning" : "success"}}">{{$ticket->inspection->status == 1 ? "Pending" : "Completed"}}</span>
                                    @endif
                                  </td>
                            </tr>
                          
                            <hr>
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
                           @foreach ($inspection_results as  $inspection_result)
                            <tr>
                                <td>{{$inspection_result->inspection_type ? $inspection_result->inspection_type->name : ""}}</td>
                                <td><span class="badge bg-{{($inspection_result->status == 'green') ? 'success' : (($inspection_result->status == 'red') ? 'danger' : 'warning') }}">{{($inspection_result->status == 'green') ? 'No Attention' : (($inspection_result->status == 'red') ? 'Immediate Attention' : 'Intermediate Attetion') }}</span></td>
                                <td>{{$inspection_result->comments}}</td>
                                <td>{{$inspection_result->cost}}</td>
                                <td>{{$inspection_result->hours}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                      </table>


                    </div>

                <div role="tabpanel" class="tab-pane " id="parts">
                    <x-loading/>
                        @livewire('ticket-inventories.index', ['ticket' => $ticket])
                    </div>
                <div role="tabpanel" class="tab-pane " id="expenses">
                    <x-loading/>
                        @livewire('ticket-expenses.index', ['ticket' => $ticket])
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
                            <label for="qty">Timeframe</label>
                            <select class="form-control" wire:model.debounce.300ms="timeframe">
                                <option value="">Select Time Frame</option>
                                <option value="Before">Before</option>
                                <option value="After">After</option>
                            </select>
                            @error('timeframe') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="qty">Attach Image(s)</label>
                            <input type="file" class="form-control"  wire:model.debounce.300ms="image" placeholder="Upload Images" multiple >
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

</div>
