<div>
    <div class="row mt-30">
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ucfirst($customer->name)}}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            @if ($customer->user)
                                <img src="{{asset('images/'.$customer->user->profile)}}" alt="{{$customer->name}}" class="img-responsive">
                            @else
                                <img src="{{asset('images/uploads/logo.png')}}" alt="{{$customer->name}}" class="img-responsive">
                            @endif
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Portal Access</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>Email</th>
                                <td><small class="color-success"><i class="fa fa-arrow-right"></i> {{ $customer->email }}</small></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="label {{ $customer->portal_enabled ? 'label-success' : 'label-default' }} label-wide">
                                        {{ $customer->portal_enabled ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="#" wire:click.prevent="toggleActivation" class="btn btn-sm {{ $customer->portal_enabled ? 'btn-warning' : 'btn-success' }} btn-block">
                        <i class="fa fa-power-off"></i> {{ $customer->portal_enabled ? 'Disable Portal Access' : 'Activate Portal Access' }}
                    </a>

                    <a href="#" wire:click.prevent="generateAndSendCredentials" wire:confirm="Generate a new PIN and email it to {{ $customer->email }}?" class="btn btn-sm btn-primary btn-block mt-10">
                        <i class="fa fa-envelope"></i> Generate &amp; Send via Email
                    </a>

                    <button type="button" class="btn btn-sm btn-default btn-block mt-10" disabled title="Not configured yet">
                        <i class="fa fa-whatsapp"></i> Send via WhatsApp
                    </button>

                    @if ($customer->pin)
                        <small class="color-gray">Legacy trip-tracking passcode ({{ $customer->pin }}) still works separately and is unaffected by the above.</small>
                    @endif
                </div>
            </div>
            <!-- /.panel -->

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Customer Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">customer</span>
                    <span class="label label-danger label-rounded label-bordered">tags</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.col-md-3 -->

        <div class="col-md-9">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Customer Details</a></li>
                <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
                <li role="presentation"><a href="#contracts" aria-controls="contracts" role="tab" data-toggle="tab">Contracts</a></li>
                <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
                <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                <li role="presentation"><a href="#invoices" aria-controls="trips" role="tab" data-toggle="tab">Invoices</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">Customer#</th>
                                <td class="w-20 line-height-35">{{$customer->customer_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$customer->name}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Initials</th>
                                <td class="w-20 line-height-35">{{$customer->initials}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Email</th>
                                    <td class="w-20 line-height-35">{{$customer->email}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Phonenumber</th>
                                    <td class="w-20 line-height-35">{{$customer->phonenumber}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">VAT Number</th>
                                    <td class="w-20 line-height-35">{{$customer->vat_number}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">TIN Number</th>
                                    <td class="w-20 line-height-35">{{$customer->tin_number}}</td>
                                </tr>
                                @if ($customer->currency)
                                    <tr>
                                        <th class="w-10 text-center line-height-35">Currency</th>
                                        <td class="w-20 line-height-35">{{$customer->currency ? $customer->currency->name : ""}}</td>
                                    </tr>
                                    <tr>
                                        <th class="w-10 text-center line-height-35">Balance</th>
                                        <td class="w-20 line-height-35">{{$customer->currency ? $customer->currency->symbol : ""}}{{number_format($customer->balance,2)}}</td>
                                    </tr> 
                                @endif
                         
                                <tr>
                                    <th class="w-10 text-center line-height-35">Country</th>
                                    <td class="w-20 line-height-35">{{$customer->country}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">City</th>
                                    <td class="w-20 line-height-35">{{$customer->city}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Suburb</th>
                                    <td class="w-20 line-height-35">{{$customer->suburb}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Address</th>
                                    <td class="w-20 line-height-35">{{$customer->street_address}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Allowable Loss %</th>
                                    <td class="w-20 line-height-35">{{$customer->allowable_loss_percentage ? $customer->allowable_loss_percentage."%" : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$customer->status == 1 ? "success" : "danger"}}">{{$customer->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                        
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="contacts">
                  @livewire('contacts.index', ['id' => $customer->id,'category' =>'customer'])
                </div>
                <div role="tabpanel" class="tab-pane" id="contracts">
                  @livewire('contracts.index', ['id' => $customer->id,'category' =>'customer'])
                </div>
                <div role="tabpanel" class="tab-pane" id="documents">
                  @livewire('documents.index', ['id' => $customer->id,'category' =>'customer'])
                </div>
                <div role="tabpanel" class="tab-pane" id="trips">
                    <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                            <th class="th-sm">Trip#
                            </th>
                            <th class="th-sm">Horse
                            </th>
                            <th class="th-sm">Driver
                            </th>
                            <th class="th-sm">From
                            </th>
                            <th class="th-sm">To
                            </th>
                            <th class="th-sm">Trip Value
                            </th>
                            <th class="th-sm">Status
                            </th>
                          </tr>
                        </thead>

                        <tbody>
                          
                            @if (isset($trips) && $trips->count()>0)
                           
                            @foreach ($trips as $trip)
                            @php
                                $to = App\Models\Destination::find($trip->to);
                                $from = App\Models\Destination::find($trip->from);
                            @endphp
                          <tr>
                            <td>{{$trip->trip_number}}</td>
                            <td>
                                @if ($trip->horse)
                                    {{$trip->horse->horse_make ? $trip->horse->horse_make->name : ""}} {{$trip->horse->horse_model ? $trip->horse->horse_model->name : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}}
                                @endif
                            </td>
                            <td>
                                @if ($trip->driver)
                                    {{$trip->driver->employee ? $trip->driver->employee->name : ""}} {{$trip->driver->employee ? $trip->driver->employee->surname : ""}}
                                @endif
                            </td>
                            <td>{{$from?->country ? $from?->country->name : ""}} {{$from?->city}}</td>
                            <td>{{$to?->country ? $to?->country->name : ""}} {{$to?->city}}</td>
                            <td>{{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight}}</td>
                            @if ($trip->trip_status == "Offloaded")
                            <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Scheduled")
                            <td><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Loaded")
                            <td><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "InTransit")
                            <td><span class="label label-secondary label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "OnHold")
                            <td><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                            @elseif($trip->trip_status == "Offloading Point")
                            <td><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                            @endif
                          </tr>
                          @endforeach
                          @else
                          <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                          @endif
                        </tbody>


                      </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="invoices">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <div class="alert alert-info" role="alert">
                                <h5>Total Unpaid</h5>
                                <hr>
                                @foreach ($currencies as $currency)
                                @php
                                    $balance = App\Models\Invoice::where('customer_id',$customer->id)
                                                                 ->where('currency_id',$currency->id)->sum('balance');
                                @endphp
                                @if (isset($balance) && $balance > 0)
                                {{ $currency->symbol }}{{number_format($balance,2)}} |
                                @endif
                                @endforeach  
                            </div>
                            <!-- /.alert alert-info -->
                        </div>
                        <!-- /.col-md-12 -->
                    </div>
                    <table id="invoicesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead>
                          
                          <tr>

                            <th class="th-sm">Invoice#
                            </th>
                            <th class="th-sm">Customer
                            </th>
                            <th class="th-sm">Date
                            </th>
                            <th class="th-sm">Payment Due
                            </th>
                            <th class="th-sm">Status
                            </th>  
                            <th class="th-sm">Total
                            </th>
                            <th class="th-sm">Amount Due
                            </th>
                            <th class="th-sm">Authorization
                            </th>
                            <th class="th-sm">Action
                            </th>
                          </tr>
                        </thead>
                        @if ($invoices->count()>0)
                        <tbody>
                            @foreach ($invoices as $invoice)
                            @php
                            $expiry = $invoice->expiry;
                            $now = new DateTime();
                            $expiry_date = new DateTime($expiry);
                        @endphp
                          <tr>
                            <td>{{$invoice->invoice_number}}</td>
                            <td>
                                {{$invoice->customer ? $invoice->customer->name : "undefined"}}
                            </td>
                            <td>{{$invoice->date}}</td>
                            <td><span class="label label-{{$now <= $expiry_date ? 'success' : 'danger' }}">{{$invoice->expiry}}</span></td>
                            <td><span class="label label-{{($invoice->status == 'Paid') ? 'success' : (($invoice->status == 'Partial') ? 'warning' : 'danger') }}">{{ $invoice->status }}</span></td>
                            <td>
                                @if ($invoice->total)
                                {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->total,2)}}
                                @endif
                            </td>
                            <td>
                               
                                {{-- @if ($invoice->balance) --}}
                                {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->balance,2)}}
                                {{-- @endif --}}
                            </td>
                            <td><span class="badge bg-{{($invoice->authorization == 'approved') ? 'success' : (($invoice->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($invoice->authorization == 'approved') ? 'approved' : (($invoice->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                           
                            <td class="w-10 line-height-35 table-dropdown">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-bars"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{route('invoices.show',$invoice->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li>
                                    </ul>
                                </div>
                                @include('invoices.delete')
                        </td>
                          </tr>
                          @endforeach
                        </tbody>
                        @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                         @endif
                      </table>
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
