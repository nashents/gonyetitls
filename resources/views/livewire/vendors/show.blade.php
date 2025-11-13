<div>
    <div class="row mt-30">
        <div class="col-md-3">

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>{{ucfirst($vendor->name)}}</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            @if ($vendor->user)
                                <img src="{{asset('images/'.$vendor->user->profile)}}" alt="{{$vendor->name}}" class="img-responsive">
                            @else
                                <img src="{{asset('images/uploads/logo.png')}}" alt="{{$vendor->name}}" class="img-responsive">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Account Credentials</h5>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i>{{$vendor->email}}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Passcode</th>
                                    <td>
                                        <small class="color-success"><i class="fa fa-arrow-right"></i> {{$vendor->pin}}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.panel -->

            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>vendor Tags</h5>
                    </div>
                </div>
                <div class="panel-body p-20">
                    <span class="label label-success label-rounded label-bordered">vendor</span>
                    <span class="label label-danger label-rounded label-bordered">tags</span>
                </div>
            </div>
            <!-- /.panel -->
        </div>
    <div class="col-md-9">
        <!-- /.row -->
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Vendor Details</a></li>
            <li role="presentation"><a href="#contacts" aria-controls="contacts" role="tab" data-toggle="tab">Contacts</a></li>
            <li role="presentation"><a href="#contracts" aria-controls="contracts" role="tab" data-toggle="tab">Contracts</a></li>
            <li role="presentation"><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            <li role="presentation"><a href="#bills" aria-controls="bills" role="tab" data-toggle="tab">Bills</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Vendor Type</th>
                            <td class="w-20 line-height-35"> {{$vendor->vendor_type ? $vendor->vendor_type->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Name</th>
                            <td class="w-20 line-height-35"> {{$vendor->name}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Phonenumber</th>
                            <td class="w-20 line-height-35">{{$vendor->phonenumber}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Landline</th>
                            <td class="w-20 line-height-35">{{$vendor->worknumber}}</td>
                        </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Email</th>
                                <td class="w-20 line-height-35">{{$vendor->email}}</td>
                            </tr>
                            @if ($vendor->currency)
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$vendor->currency ? $vendor->currency->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Balance</th>
                                <td class="w-20 line-height-35">{{$vendor->currency ? $vendor->currency->symbol : ""}}{{number_format($vendor->balance,2)}}</td>
                            </tr> 
                        @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Country</th>
                                <td class="w-20 line-height-35">{{ucfirst($vendor->country)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">City</th>
                                <td class="w-20 line-height-35">{{$vendor->city}}</td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Suburb</th>
                                <td class="w-20 line-height-35">{{$vendor->suburb}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Street Address</th>
                                <td class="w-20 line-height-35">{{$vendor->street_address}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$vendor->status == 1 ? "success" : "danger"}}">{{$vendor->status == 1 ? "Active" : "Inactive"}}</span></td>
                            </tr>
                    </tbody>
                </table>
              
            </div>

            <div role="tabpanel" class="tab-pane" id="contacts">
                @livewire('contacts.index', ['id' => $vendor->id,'category' =>'vendor'])
              </div>
              <div role="tabpanel" class="tab-pane" id="contracts">
                @livewire('contracts.index', ['id' => $vendor->id,'category' =>'vendor'])
              </div>
            <div role="tabpanel" class="tab-pane" id="documents">
                @livewire('documents.index', ['id' => $vendor->id,'category' =>'vendor'])
              </div>
              <div role="tabpanel" class="tab-pane" id="bills">
                <table id="billsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                    <thead>
                      <tr>

                        <th class="th-sm">Bill#
                        </th>
                        <th class="th-sm">Category
                        </th>
                        <th class="th-sm">Date
                        </th>
                        <th class="th-sm">Currency
                        </th>
                        <th class="th-sm">Total
                        </th>
                        <th class="th-sm">Amount Due
                        </th>
                        <th class="th-sm">Payment Status
                        </th>
                        <th class="th-sm">Action
                        </th>
                      </tr>
                    </thead>
                    @if ($bills->count()>0)
                    <tbody>
                        @foreach ($bills as $bill)
                      <tr>
                        <td>{{$bill->bill_number}}</td>
                        <td>
                            @if ($bill->transporter)
                                Transporter | <a href="{{ route('transporters.show',$bill->transporter->id) }}" style="color: blue" target="_blank">{{ $bill->transporter->name }}</a> 
                            @elseif($bill->vendor && ($bill->container == NULL || $bill->top_up == NULL))
                                Vendor | <a href="{{ route('vendors.show',$bill->vendor->id) }}" style="color: blue" target="_blank">{{ $bill->vendor->name }}</a> 
                            @elseif ( $bill->container || $bill->top_up)
                                Fuel Topup | <a href="{{ route('containers.show', $bill->container->id) }}" style="color: blue" target="_blank">{{ $bill->container->name }}</a> 
                            @elseif ( $bill->invoice)
                                Invoice VAT | <a href="{{ route('invoices.show', $bill->invoice->id) }}" style="color: blue" target="_blank">{{ $bill->invoice->invoice_number }}</a> 
                            @elseif ($bill->trip && ($bill->horse || $bill->driver))
                                Trip Expense | <a href="{{ route('trips.show', $bill->trip->id) }}" style="color: blue" target="_blank">{{ $bill->trip->trip_number }}</a> 
                            @endif
                        </td>
                        <td>{{$bill->bill_date}}</td>
                        <td>{{$bill->currency ? $bill->currency->name : ""}}</td> 
                        <td>
                            @if ($bill->total)
                                 {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}
                            @endif
                        </td>
                        <td>
                            @if ($bill->balance)
                                 {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance,2)}}
                            @else   
                            {{$bill->currency ? $bill->currency->symbol : ""}}0.00
                            @endif
                        </td>
                        <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                        <td class="w-10 line-height-35 table-dropdown">
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bars"></i>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{route('bills.show',$bill->id)}}"  ><i class="fas fa-eye color-default"></i>View</a></li>
                                </ul>
                            </div>
                            @include('bills.delete')
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



            <!-- /.section-title -->
        </div>
    </div>
    </div>
</div>
