<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Dispatch Details</a></li>
                <li role="presentation"><a href="#items" aria-controls="items" role="tab" data-toggle="tab">Dispatch Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Dispatch#</th>
                                <td class="w-20 line-height-35">{{$dispatch->dispatch_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$dispatch->user ? $dispatch->user->name : ""}} {{$dispatch->user ? $dispatch->user->surname : ""}} </td>
                            </tr>
                            @if ($dispatch->vendor)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vendor</th>
                                    <td class="w-20 line-height-35">{{$dispatch->vendor ? $dispatch->vendor->name : ""}}</td>
                                </tr>
                            @endif
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$dispatch->date}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">RequestedBy</th>
                                <td class="w-20 line-height-35">
                                    @php
                                        $requested_by = App\Models\Employee::find($dispatch->requested_by_id);
                                    @endphp
                                    @if ($requested_by)
                                        {{$requested_by->name}} {{$requested_by->surname}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$dispatch->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Narration</th>
                                <td class="w-20 line-height-35">
                                        @if ($dispatch->ticket)
                                            Ticket#: <a href="">{{$dispatch->ticket ? $dispatch->ticket->ticket_number : ""}}</a>
                                        @endif
                                        @if ($dispatch->horse)
                                            Horse: <a href="">{{$dispatch->horse ? $dispatch->horse->registration_number : ""}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->vehicle)
                                            Vehicle: <a href="">{{$dispatch->vehicle ? $dispatch->vehicle->registration_number : ""}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->trailer)
                                            Trailer: <a href="">{{$dispatch->trailer ? $dispatch->trailer->registration_number : ""}} {{$dispatch->trailer->fleet_number ? "(".$dispatch->trailer->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->employee)
                                            Employee: {{$dispatch->employee ? $dispatch->employee->name : ""}} {{$dispatch->employee ? $dispatch->employee->surname : ""}}
                                                @if ($dispatch->department)
                                                    Department: {{$dispatch->department ? $dispatch->department->name : ""}}
                                                @endif
                                                @if ($dispatch->branch)
                                                    Branch: {{$dispatch->branch ? $dispatch->branch->name : ""}}
                                                @endif
                                        @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Qty</th>
                                <td class="w-20 line-height-35">{{$dispatch->dispatch_items->count()}}</td>
                            </tr>
                             <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$dispatch->currency ? $dispatch->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Total Value</th>
                                <td class="w-20 line-height-35"> {{$dispatch->currency ? $dispatch->currency->symbol : ""}}{{$dispatch->total}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($dispatch->authorization == 'approved') ? 'success' : (($dispatch->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($dispatch->authorization == 'approved') ? 'approved' : (($dispatch->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                            @php
                                $authorizer = App\Models\User::find($dispatch->authorized_by_id);
                            @endphp
                            @if ($authorizer)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorized By</th>
                                    <td class="w-20 line-height-35">
                                        {{$authorizer->name}} {{$authorizer->surname}}
                                    </td>
                                </tr>
                            @endif
                            @if ($dispatch->authorization_date)
                                    <tr>
                                    <th class="w-10 text-center line-height-35">Authorized On</th>
                                    <td class="w-20 line-height-35">{{Carbon\Carbon::parse($dispatch->authorization_date)->format('Y-m-d')}}</td>
                                </tr>
                            @endif
                            @if ($dispatch->authorization_comments)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization Comments</th>
                                    <td class="w-20 line-height-35">{{$dispatch->authorization_comments}}</td>
                                </tr>
                            @endif
                               
                                
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="items">
                  @livewire('dispatches.items', ['id' => $dispatch->id])
                </div> 
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
