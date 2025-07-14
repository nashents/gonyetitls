<div>
  
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            
                                <div>
                                    @include('includes.messages')
                                </div>
                             
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Vendor Statements</h5>
                                                </div>
                                            </div>
                                            <div class="panel-body">

                                                <div class="row">
                                                    <form wire:submit.prevent="generateStatement()">
                                                	<div class="col-lg-3">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                       Vendors
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedVendor" class="form-control" aria-label="..." >
                                                            <option value="">Select Vendor</option>
                                                            @foreach ($vendors as $vendor)
                                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                            @endforeach
                                                      </select>
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                	<!-- /.col-lg-6 -->
                                                	<div class="col-lg-3">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                      Type
                                                      </span>
                                                      <select wire:model.debounce.300ms="selectedType" class="form-control" aria-label="..." >
                                                        <option value="">Select Type</option>
                                                        <option value="Outstanding Bills">Outstanding Bills</option>
                                                        <option value="Account Activity">Account Activity</option>
                                                      </select>
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                    @if (!is_null($selectedType) && $selectedType == "Account Activity" )
                                                    <div class="col-lg-2">
                                                		<div class="input-group">
                                                			<span class="input-group-addon">
                                                      From
                                                      </span>
                                                      <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div>
                                                	<div class="col-lg-2">
                                                		<div class="input-group" style="margin-left:15px;">
                                                			<span class="input-group-addon">
                                                      To
                                                      </span>
                                                      <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                                		</div>
                                                		<!-- /input-group -->
                                                	</div> 
                                                    @endif
                                                    </form>
                                                </div>
                                                <!-- /.row -->
                                                <br>
                                                <br>
                                                @if (isset($bills))
                                                    @if ($bills->count()>0)
                                                <div class="row">
                                                    <div class="col-md-4">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="col-lg-2">
                                                            <div class="btn-group">
                                                                @if (isset($selectedVendor) && (isset($selectedType) && $selectedType == "Outstanding Bills"))
                                                                <a href="{{route('vendor_statements.preview.outstanding',['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType])}}" class="btn btn-default border-primary btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                    <i class="fas fa-file-invoice"></i> Preview
                                                                </a>
                                                                @elseif(isset($selectedVendor) && (isset($selectedType) && $selectedType == "Account Activity") && isset($from) && isset($to))  
                                                                <a href="{{route('vendor_statements.preview.account',['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                    <i class="fas fa-file-invoice"></i> Preview
                                                                </a>
                                                                @endif
                                                               
                                                            </div>
                                                            <!-- /input-group -->
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <div class="btn-group">
                                                                @if (isset($selectedVendor) && (isset($selectedType) && $selectedType == "Outstanding Bills"))
                                                                <button type="buttom" wire:click="exportVendorStatementExcel()" class="btn btn-default border-primary btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                   <i class="fas fa-download"></i> Excel
                                                                </button>
                                                                @endif
                                                            </div>
                                                            <!-- /input-group -->
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                    </div>
                                                </div>
                                                @endif
                                                @endif
                                                <!-- /.col-md-12 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                           
                                <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">   
                                    @if ($selectedType == "Outstanding Bills")
                                    <table id="billsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                        <caption>Outstanding Bills for {{ App\Models\Vendor::find($selectedVendor)->name }}</caption>
                                        <thead>
                                          <tr>
                                            <th class="th-sm">Bill#
                                            </th>
                                            <th class="th-sm">Vendor
                                            </th>
                                            <th class="th-sm">Bill Date
                                            </th>
                                            <th class="th-sm">Due Date
                                            </th>
                                            <th class="th-sm">Currency
                                            </th>
                                            <th class="th-sm">Bill Total
                                            </th>
                                            <th class="th-sm">Amount Paid
                                            </th>
                                            <th class="th-sm">Amount Due
                                            </th>
                                            <th class="th-sm">Status
                                            </th>
                                          </tr>
                                        </thead>
                                        @if ($bills)
                                            @if ($bills->count()>0)
                                        <tbody>
                                            @foreach ($bills as $bill)
                                                <tr>
                                                    
                                                    <td><a href="{{route('bills.show',$bill->id)}}" style="color: blue">{{$bill->bill_number}}</a></td>
                                                    <td>{{$bill->vendor ? $bill->vendor->name : ""}}</td>
                                                    <td>{{$bill->bill_date}}</td>
                                                    <td>{{$bill->due_date}}</td>
                                                    <td>{{$bill->currency ? $bill->currency->name : ""}}</td>
                                                    <td>{{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->total,2)}}</td>
                                                    <td>
                                                        @php
                                                            $sum = $bill->payments->sum('amount');
                                                        @endphp
                                                        @if (isset($sum))
                                                             {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($sum,2)}}</td>
                                                        @endif
                                                       
                                                    <td>
                                                        @if ($bill->balance)
                                                            {{$bill->currency ? $bill->currency->symbol : ""}}{{number_format($bill->balance,2)}}
                                                        @endif
                                                    </td>
                                                    <td><span class="label label-{{($bill->status == 'Paid') ? 'success' : (($bill->status == 'Partial') ? 'warning' : 'danger') }}">{{ $bill->status }}</span></td>
                                                  </tr>
                                          @endforeach
                                        </tbody>
                                        @else 
                                        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                        @endif
                                        @else
                                           
                                            <div class="row">
                                                <center>
                                                    <img style="width:25%; height:25%;" src="{{asset('images/invoice.png')}}" alt="">
                                                    <h3>Keep vendors informed</h3> 
                                                    <p>Remind your vendors about outstanding bills or send details of their account activity.</p>
                                                    <p>Create a statement by selecting a vendor and statement type from the form above.</p>
                                                </center>
                                            </div>
                                         @endif
                                      </table>
                                    @elseif ($selectedType == "Account Activity")
                                        @php
                                            $currencies = App\Models\Currency::all();
                                            $billed_currencies = App\Models\Bill::where('vendor_id',$selectedVendor)->where('authorization','approved')->get()->pluck('currency_id')->toArray();
                                        @endphp

                                        @foreach ($currencies as $currency)

                                        @if (isset($billed_currencies))
                                        @if (in_array($currency->id, $billed_currencies))

                                                <table id="billsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                                    <caption>Account Activity for {{ App\Models\Vendor::find($selectedVendor)->name }} in {{ $currency->fullname }} {{ $currency->name }}</caption>
                                                    <thead>
                                                    <tr>
                                                        <th class="th-sm">Date
                                                        </th>
                                                        <th class="th-sm">Item
                                                        </th>
                                                        <th class="th-sm">Currency
                                                        </th>
                                                        <th class="th-sm">Amount
                                                        </th>
                                                        <th class="th-sm">Balance
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    @if ($results)
                                                        @if ($results->count()>0)
                                                        <tbody> 
                                                                @php
                                                                $opening_balance = App\Models\Bill::where('date','<=',$from)
                                                                ->where('authorization','approved')
                                                                ->where('vendor_id',$selectedVendor)
                                                                ->where('currency_id', $currency->id)
                                                                ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')
                                                                ->where('accrual_balance', function ($query) {
                                                                    $query->selectRaw('MAX(CAST(accrual_balance AS DECIMAL(10,2)))')->from('bills');
                                                                })
                                                                ->first();
                                                                @endphp
                                                            
                                                               
                                                                @if (isset($opening_balance))
                                                                <tr>
                                                                    <tr>
                                                                        <td colspan="2"><strong>{{ date('F j, Y', strtotime($to)) }}</strong></td>
                                                                        <td colspan="2"><strong>Opening Balance {{ $currency->name }}</strong></td>
                                                                        <td><strong>{{ $currency->symbol }}{{  number_format($opening_balance->accrual_balance,2) }}</strong></td>        
                                                                    </tr>
                                                                </tr>
                                                                @endif
                                                        
                                                        
                                                            @if (isset($results))
                                                                @foreach ($results->sortBy('created_at') as $result)
                                                                    @php
                                                                        $currency = App\Models\Currency::find($result->currency_id);
                                                                    
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ date('F j, Y', strtotime($result->transaction_date)) }}</td>
                                                                        <td>
                                                                                @php
                                                                                    $bill = App\Models\Bill::where('bill_number',$result->number)->where('authorization','approved')->get()->first();
                                                                                    $payment = App\Models\Payment::where('payment_number',$result->number)->get()->first();
                                                                                @endphp
                                                                                @if ($bill)
                                                                                    <a href="{{ route('bills.show',$bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $result->number }} </a><br>
                                                                                    Due {{ $bill->expiry }}
                                                                                 @elseif (isset($payment))
                                                                                    <a href="{{ route('payments.show',$payment->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">{{ $result->number }}</a> Payment  made for 
                                                                                    @if (isset($payment->bill))
                                                                                    <a href="{{ route('bills.show',$payment->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $payment->bill ? $payment->bill->bill_number : "" }} </a> 
                                                                                    @elseif (isset($payment->bill_payment))
                                                                                    <a href="{{ route('bills.show',$payment->bill_payment->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $payment->bill_payment->bill ? $payment->bill_payment->bill->bill_number : "" }} </a> 
                                                                                    @endif
                                                                                  
                                                                                @endif
                                                                            </td>
                                                                        <td>{{ $currency->name}}</td>
                                                                        <td>{{ $currency->symbol}}{{ number_format($result->amount,2) }}</td>
                                                                        <td>{{ $currency->symbol}}{{ number_format($result->balance,2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        
                                                            @php
                                                              $closing_balance = App\Models\Bill::whereBetween('date', [$from, $to])
                                                                ->where('authorization','approved')
                                                                ->where('vendor_id',$selectedVendor)
                                                                ->where('currency_id', $currency->id)
                                                                ->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')
                                                                ->get()->sum('balance');

                                                             @endphp
                                                        
                                                                @if (isset($closing_balance))
                                                                <tr>
                                                                    <td colspan="2"><strong>{{ date('F j, Y', strtotime($to)) }}</strong></td>
                                                                    <td colspan="2"><strong>Closing Balance {{ $currency->name }}</strong></td>
                                                                    <td><strong>{{ $currency->symbol }}{{  number_format($closing_balance,2) }}</strong></td>        
                                                                </tr>
                                                                @endif
                                                      
                                                        </tbody>
                                                    @else 
                                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                                    @endif
                                                    @else
                                                    
                                                        <div class="row">
                                                            
                                                            <center>
                                                                <img style="width:25%; height:25%;" src="{{asset('images/invoice.png')}}" alt="">
                                                                <h3>Keep vendors informed</h3> 
                                                                <p>Remind your vendors about outstanding bills or send details of their account activity.</p>
                                                                <p>Create a statement by selecting a vendor and statement type from the form above.</p></center>
                                                        </div>
                                                    @endif
                                                </table>
                                      @endif
                                      @endif

                                      @endforeach


                                    @else
                                           
                                      <div class="row">
                                          
                                          <center>
                                              <img style="width:25%; height:25%;" src="{{asset('images/invoice.png')}}" alt="">
                                              <h3>Keep vendors informed</h3> 
                                              <p>Remind your vendors about outstanding bills or send details of their account activity.</p>
                                              <p>Create a statement by selecting a vendor and statement type from the form above.</p></center>
                                      </div>
                                
                                    @endif                                 
                                    
        
                                    <!-- /.col-md-12 -->
                                </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->
           
            </div>
            <!-- /.container-fluid -->
        </section>

          <!-- Modal -->


    </div>
