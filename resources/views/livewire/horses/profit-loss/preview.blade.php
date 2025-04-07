<div>
    <div id="invoice">
        <x-loading/>
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color:black"></i> Back</button>
                <a href="{{route('horses.statement.print',['selectedHorse' => $selectedHorse, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                {{-- <a href="{{route('horses.statement.pdf',['selectedHorse' => $selectedHorse, 'from' => $from, 'to' => $to])}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a> --}}
            </div>
            <hr>
        </div>
        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;"><img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt=""></a>               
                        </div>
                        <div class="col company-details">
                            <h4 class="name" >
                                <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                    {{$company->name}}
                                </a>
                            </h4>
                            <div>{{$company->street_address}} {{$company->suburb}} <br>
                                {{$company->city}}, {{$company->country}}</div>
                            <div>
                                {{$company->phonenumber}}
                                @if ($company->second_phonenumber)
                                | {{$company->second_phonenumber}}
                                @endif
                                @if ($company->third_phonenumber)
                                | {{$company->third_phonenumber}}
                                @endif
                            </div>
                          
                            
                            <div>{{$company->email}}</div>
                            @if ($company->second_email)
                            <div>{{$company->second_email}}</div>
                            @endif
                            @if ($company->third_email)
                            <div>{{$company->third_email}}</div>
                            <br>
                            @endif
                            
                            <h5 class="to" >Statement of Comprehensive Income</h5>
                            <div> <strong>{{$selected_horse->registraion_number}}</strong></div>
                            <br>
                        </div>
                    </div>
                </header>
             
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to" >
                            <div class="text-gray-light">Statement For:</div>
                            <h6 class="to">Horse: {{$selected_horse->registration_number}} {{$selected_horse->fleet_number ? $selected_horse->fleet_number: ""}} {{$selected_horse->horse_make ? $selected_horse->horse_make->name: ""}} {{$selected_horse->horse_model ? $selected_horse->horse_model->name: ""}}</h6>
                            @php
                                $assignment = App\Models\Assignment::where('horse_id',$selected_horse->id)->where('status',1)->first();
                                $trailer_assignment = App\Models\TrailerAssignment::where('horse_id',$selected_horse->id)->where('status',1)->first();
                            @endphp
                            <h6 class="to">Trailer(s): 
                                @if (isset($trailer_assignment))
                                {{$trailer_assignment->trailer ? $trailer_assignment->trailer->registration_number : ""}}  {{$trailer_assignment->trailer->fleet_number ? "(".$trailer_assignment->trailer->fleet_number.")" : ""}}
                                @endif
                            </h6>
                            <h6 class="to">Driver: 
                                @if (isset($assignment))
                                    {{$assignment->driver->employee ? $assignment->driver->employee->name : ""}} {{$assignment->driver->employee ? $assignment->driver->employee->surname : ""}}
                                @endif
                            </h6>
                        </div>
                        <div class="col invoice-details">
                            <h5 class="to" >{{$default_currency->fullname}} {{$default_currency->name}} {{$default_currency->symbol ? "(".$default_currency->symbol.")" : ""}}</h5>
                            <div class="date" style="padding-bottom: 3px"><strong>From: </strong> {{Carbon\Carbon::parse($from)->format('d F Y')}} </div> 
                            <div class="date" style="padding-bottom: 3px"><strong>To: </strong> {{Carbon\Carbon::parse($to)->format('d F Y')}} </div> 
                        </div>
                    </div> 
                    <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">   
                             
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title" >
                                        <div class="col-xs-5 p-n">
                                            <div class="text-gray-light">Total Trip(s): <strong>{{$total_trips}}</strong></div>
                                            <div class="text-gray-light">Total Fuel Order(s): <strong>{{$total_fuel_orders}}</strong></div>
                                            <div class="text-gray-light">Total Fuel Usage: <strong>{{$total_fuel ? $total_fuel." Litres" : ""}}</strong></div>
                                        </div>
                                    </div>
                                </div>
                              
                                <hr class="mb-3 mt-3" style="width:100%;", size="3", color=black>  

                                <div class="panel-body ">
                                    <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                        <div class="col-xs-5 p-n">
                                          <strong><span style="margin-left:5px">Income</span> </strong>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <hr class="mb-3 mt-3" style="width:100%;", size="3", color=black>         
                                    <div class="col-xs-12 p-n">
                                        <div class="col-xs-5 p-n">
                                            <span style="margin-left:5px">Sales</span> 
                                            <span style="float: right; padding-right:5px; ">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}}</span>
                                        </div>
                                    </div>
                                    <hr style="width:100%;", size="3", color=black> 
                                    <div class="col-xs-12 p-n">
                                        <div class="col-xs-5 p-n">
                                            <strong><span style="margin-left:5px">Total Income</span></strong> 
                                            <span style="float: right; padding-right:5px;">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}}</span>
                                        </div>
                                       
                                    </div>
                                    <hr style="width:100%", size="3", color=black>  
                                    <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                        <div class="col-xs-5 p-n">
                                         <strong><span style="margin-left:5px">Cost of Goods Sold</span></strong>  
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <hr style="width:100%", size="3", color=black>  
                                   
                                    @foreach ($cost_of_goods_sold_accounts as $account)
                                        @php
                                                $selected_horse = $this->selectedHorse;

                                                $account_bill_expenses = App\Models\BillExpense::where('account_id',$account->id)
                                                        ->whereHas('bill', function($q){
                                                            $q->whereDate('bill_date','>=',$this->from);
                                                        })
                                                        ->whereHas('bill', function($q){
                                                            $q->whereDate('bill_date','<=',$this->to);
                                                        })
                                                        ->whereHas('bill', function($q){
                                                            $q->where('horse_id', $this->selectedHorse);
                                                        })
                                                        ->get();

                                                $account_cost_of_goods_sold = App\Models\BillExpense::where('account_id',$account->id)
                                                ->whereNull('allowance_id')
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','>=',$this->from);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','<=',$this->to);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->where('authorization', 'approved');
                                                })
                                                ->whereHas('bill', function ($query) {
                                                    $query->whereNotNull('trip_id');
                                                })
                                                ->whereHas('bill', function($q)use($default_currency_id){
                                                    $q->where('currency_id', $default_currency_id);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->where('horse_id', $this->selectedHorse);
                                                })
                                                ->orWhereHas('bill', function ($billQuery) use ($selected_horse) {
                                                    $billQuery->whereHas('trip', function ($tripQuery) use ($selected_horse) {
                                                        $tripQuery->where('horse_id', $selected_horse);
                                                    });
                                                })
                                                ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');
                                                
                                                $account_exchange_cost_of_goods_sold = App\Models\BillExpense::where('account_id',$account->id)
                                                ->whereNull('allowance_id')
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','>=',$this->from);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','<=',$this->to);
                                                })
                                                ->whereHas('bill', function($q){
                                                $q->where('authorization', 'approved');
                                                })
                                                ->whereHas('bill', function ($query) {
                                                            $query->whereNotNull('trip_id');
                                                        })
                                                ->whereHas('bill', function($q)use($default_currency_id){
                                                $q->where('currency_id','!=', $default_currency_id);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->where('horse_id', $this->selectedHorse);
                                                })
                                                ->orWhereHas('bill', function ($billQuery) use ($selected_horse) {
                                                    $billQuery->whereHas('trip', function ($tripQuery) use ($selected_horse) {
                                                        $tripQuery->where('horse_id', $selected_horse);
                                                    });
                                                })
                                                ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

                                                if (is_numeric($account_cost_of_goods_sold) && is_numeric($account_exchange_cost_of_goods_sold)) {
                                                    $total_account_cost_of_goods_sold =  $account_cost_of_goods_sold + $account_exchange_cost_of_goods_sold;
                                                }
                                    
                                        @endphp


                                        @if (isset($account_bill_expenses) && $account_bill_expenses->count() > 0)
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                <span style="margin-left:5px">{{$account->name}} </span> 
                                                <span style="float: right; padding-right:5px;">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_account_cost_of_goods_sold ? $total_account_cost_of_goods_sold : 0,2)}}</span>
                                                </div>
                                            </div>
                                            <hr style="width:100%;", size="3", color=black> 
                                        @endif
                                    @endforeach
                                    <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                        <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                            <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                            <p style="margin-left:5px">As a percentage of Total Income</p>
                                        </div>
                                        <!-- /.col-md-6 -->
                                        
                                    <div class="col-xs-6 p-n" >
                                        <strong><span style="float: right; margin-top: -55px; padding-right:5px;">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($gross_profit ? $gross_profit : 0,2)}}</span></strong>
                                        <br>
                                        <p>
                                            <span style="float: right; margin-top: -55px; padding-right:5px;"> 
                                                @if ($gross_profit_percentage)
                                                    {{number_format($gross_profit_percentage ? $gross_profit_percentage: 0,2)}}%
                                                @endif
                                            </span>
                                        </p>
                                        
                                    </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                   
                                    <hr style="width:100%", size="3", color=black>  
                                    <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                        <div class="col-xs-5 p-n">
                                          <strong><span style="margin-left:5px">Operating Expenses</span> </strong>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                                    <hr style="width:100%", size="3", color=black>  
                                   
                                    @foreach ($operating_expenses_accounts as $account)
                                            @php       
                                             $account_bill_expenses = App\Models\BillExpense::where('account_id',$account->id)
                                                        ->whereHas('bill', function($q){
                                                            $q->whereDate('bill_date','>=',$this->from);
                                                        })
                                                        ->whereHas('bill', function($q){
                                                            $q->whereDate('bill_date','<=',$this->to);
                                                        })
                                                        ->whereHas('bill', function($q){
                                                            $q->where('horse_id', $this->selectedHorse);
                                                        })
                                                        ->get();

                                                $account_operating_expenses = App\Models\BillExpense::where('account_id',$account->id)
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','>=',$this->from);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','<=',$this->to);
                                                })
                                                ->whereHas('bill', function($q){
                                                $q->where('authorization', 'approved');
                                                })
                                                ->whereHas('bill', function($q) use($default_currency_id){
                                                $q->where('currency_id', $default_currency_id);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->where('horse_id', $this->selectedHorse);
                                                })
                                                ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');
                                            
                                                
                                                $account_exchange_operating_expenses = App\Models\BillExpense::where('account_id',$account->id)
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','>=',$this->from);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->whereDate('bill_date','<=',$this->to);
                                                })
                                                ->whereHas('bill', function($q){
                                                $q->where('authorization', 'approved');
                                                })
                                                ->whereHas('bill', function($q)use($default_currency_id){
                                                $q->where('currency_id','!=', $default_currency_id);
                                                })
                                                ->whereHas('bill', function($q){
                                                    $q->where('horse_id', $this->selectedHorse);
                                                })
                                                ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

                                                if (is_numeric($account_operating_expenses) && is_numeric($account_exchange_operating_expenses)) {
                                                    $total_account_operating_expenses =  $account_operating_expenses + $account_exchange_operating_expenses;
                                                }
                                            @endphp
                                        @if (isset($account_bill_expenses) && $account_bill_expenses->count() > 0)
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                        <span style="margin-left:5px">{{$account->name}}</span> 
                                                        <span style="float: right; padding-right:5px;">{{$default_currency->name}} {{$default_currency->symbol}}{{ number_format($total_account_operating_expenses ? $total_account_operating_expenses : 0,2)}}</span>
                                                    </div>

                                                </div>
                                                <hr style="width:100%;", size="3", color=black> 
                                            @endif
                                        @endforeach
                             
                                    <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                        <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                            <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                            <p style="margin-left:5px">As a percentage of Total Income</p>
                                        </div>
                                        <!-- /.col-md-6 -->
                                        
                                        <div class="col-xs-6 p-n"  >
                                            <strong><span style="float: right; margin-top: -55px; padding-right:5px;">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($net_profit ? $net_profit : 0,2)}}</span></strong>
                                            <br>
                                            <p>
                                                <span style="float: right; margin-top: -55px; padding-right:5px;">
                                                    @if ($net_profit_percentage)
                                                    {{number_format($net_profit_percentage ? $net_profit_percentage : 0,2)}}%    
                                                    @endif
                                               </span>
                                            </p>
                                        </div>
                                        <!-- /.col-md-6 -->
                                    </div>
                            </div>

                            </div>
                        </div>
                     
                    </div>
                   
                </main>

             {{-- <center> <footer>{{$company->invoice_footer}}</footer></center>   --}}
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
