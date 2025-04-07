<div>
    <div class="row mt-30">
    <div class="col-md-10 col-md-offset-1">
        <!-- /.row -->
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation"class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Recovery Details</a></li>
            <li role="presentation" ><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
            <li role="presentation" ><a href="#payments" aria-controls="payments" role="tab" data-toggle="tab">Payments</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Recovery#</th>
                            <td class="w-20 line-height-35"> {{$recovery->recovery_number}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Opened By</th>
                            <td class="w-20 line-height-35"> {{$recovery->user ? $recovery->user->name : ""}} {{$recovery->user ? $recovery->user->surname : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Opened On</th>
                            <td class="w-20 line-height-35"> {{$recovery->date}}</td>
                        </tr>
                        @if ($recovery->trip)
                        <tr>
                            <th class="w-10 text-center line-height-35">Trip</th>
                            <td class="w-20 line-height-35"> {{$recovery->trip ? $recovery->trip->trip_number : ""}} <strong>From:</strong> {{$recovery->trip->loading_point ? $recovery->trip->loading_point->name : "undefined"}} <strong>To:</strong> {{$recovery->trip->offloading_point ? $recovery->trip->offloading_point->name : ""}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="w-10 text-center line-height-35">Location</th>
                            <td class="w-20 line-height-35">  @if ($recovery->destination)
                                {{$recovery->destination->country ? $recovery->destination->country->name : ""}} {{$recovery->destination ? $recovery->destination->city : ""}}        
                            @endif</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Deduction Name</th>
                            <td class="w-20 line-height-35"> {{$recovery->deduction ? $recovery->deduction->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Driver</th>
                            <td class="w-20 line-height-35">
                                @if ($recovery->driver)
                                {{$recovery->driver ? $recovery->driver->driver_number : ""}} {{$recovery->driver ? $recovery->driver->employee->name : ""}} {{$recovery->driver ? $recovery->driver->employee->surname : ""}}
                                @endif
                            </td>
                        </tr>
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$recovery->currency ? $recovery->currency->name : ""}}</td>
                            </tr>
                            @if ($recovery->exchange_rate)
                            <tr>
                                <th class="w-10 text-center line-height-35">Exchange Rate</th>
                                <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$recovery->exchange_rate}}</td>
                            </tr> 
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Amount</th>
                                <td class="w-20 line-height-35">
                                    @if ($recovery->amount)
                                    {{ $recovery->currency ? $recovery->currency->symbol : "" }}{{ number_format($recovery->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            @if ($recovery->exchange_amount)
                            <tr>
                                <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                                <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$recovery->exchange_amount}}</td>
                            </tr> 
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Amount Due</th>
                                <td class="w-20 line-height-35">
                                    @if ($recovery->balance)
                                    {{ $recovery->currency ? $recovery->currency->symbol : "" }}{{ number_format($recovery->balance,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Payment Status</th>
                                <td class="w-20 line-height-35"><span class="label label-{{($recovery->status == 'Paid') ? 'success' : (($recovery->status == 'Partial') ? 'warning' : 'danger') }}">{{ $recovery->status }}</span></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$recovery->description}}</td>
                            </tr> 
                            <tr>
                                <th class="w-10 text-center line-height-35">Progress</th>
                                <td class="w-20 line-height-35"><span class="label label-{{$recovery->progress == "Open"  ? 'warning' : 'success' }}">{{$recovery->progress}}</span></td>
                            </tr> 
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($recovery->authorization == 'approved') ? 'success' : (($recovery->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($recovery->authorization == 'approved') ? 'approved' : (($recovery->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr> 
                          
                           
                    </tbody>
                </table>
              
            </div>
            <div role="tabpanel" class="tab-pane" id="documents">
                @livewire('documents.index', ['id' => $recovery->id,'category' =>'recovery'])
              </div>
            <div role="tabpanel" class="tab-pane" id="payments">
                @php
                    $payments = $recovery->payments
                @endphp
                <table id="paymentsTable" class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                    <thead >
                        <th class="th-sm">Payment#
                        </th>
                        <th class="th-sm">Recovery#
                        </th>
                        <th class="th-sm">Driver
                        </th>
                        <th class="th-sm">PaidBy
                        </th>
                        <th class="th-sm">MOP
                        </th>
                        <th class="th-sm">Currency
                        </th>
                        <th class="th-sm">Amt
                        </th>
                        <th class="th-sm">Bal
                        </th>
                        <th class="th-sm">Actions
                        </th>

                      </tr>
                    </thead>
               
                    @if ($payments->count()>0)
                    <tbody>
                        @foreach ($payments as $payment)
                      <tr>
                        
                        <td>{{ucfirst($payment->payment_number)}}</td>
                        <td>
                            @if ($payment->recovery)
                            <a href="{{ route('recoveries.show',$payment->recovery->id) }}" style="color:blue">{{$payment->recovery ? $payment->recovery->recovery_number : ""}}</a>
                            @endif
                        </td>
                        <td>
                            @if ($payment->driver)
                                 {{ucfirst($payment->driver->employee ? $payment->driver->employee->name : "")}} {{ucfirst($payment->driver->employee ? $payment->driver->employee->surname : "")}}
                            @endif   
                        </td>
                        <td>{{ucfirst($payment->name)}} {{ucfirst($payment->surname)}}</td>
                        <td>{{$payment->mode_of_payment}}</td>
                        <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                        <td>@if ($payment->amount)
                            {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                        @endif</td>
                        <td>
                           @if ($payment->bill)
                           {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->bill->balance,2)}} 
                           @elseif ($payment->invoice)
                           {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->invoice->balance,2)}} 
                           @elseif ($payment->recovery)
                           {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->recovery->balance,2)}} 
                            @endif
                          </td>
                         <td class="w-10 line-height-35 table-dropdown">
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-bars"></i>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{route('payments.show', $payment->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                </ul>
                            </div>
                     

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
