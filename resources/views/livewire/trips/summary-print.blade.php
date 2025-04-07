<div>
    <div id="invoice">

        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;">
                                <img src="{{asset('images/uploads/'.$company->logo)}}" width="80" alt="">
                            </a>
                        </div>
                        <div class="col company-details">
                            <h4 class="name">
                                <a target="_blank" href="javascript:;">
                            {{$company->name}}
                            </a>
                            </h4>
                            <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                            <div>{{$company->phonenumber}}
                            </div>
                            <div>{{$company->email}}</div>
                        </div>
                    </div>
                </header>
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to">
                            <div class="text-gray-light">Data Summary Report</div>
                            <div class="text-gray-light">Company`s default trading currency <strong>{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</strong></div>
                        </div>
                        <div class="col invoice-details">
                            @if (isset($from) && isset($to) )
                            <div class="date">
                                <strong>From: </strong> {{$from ? $from : ""}}
                            </div>
                            <div class="date">
                                <strong>To: </strong> {{$to ? $to : ""}}    
                            </div>
                            @else
                            <div class="date">
                                <strong>Current Month: </strong>  {{ date('F') }}   
                            </div>
                           
                            @endif
                            
                        </div>
                    </div>
                    <table>
                        <tbody>
                            <tr>
                                <th class="text-center"><strong>Total Trips</strong></th>
                                <td class="unit text-center">
                                    {{$trips ? $trips->count() : ""}}</td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Total Revenue</strong></th>
                                <td class="unit text-center">
                                    @if (isset(Auth::user()->employee->company->currency_id)) 
                                        @php
                                            $total_revenue = 0;
                                                foreach ($trips as $trip) { 
                                                    if ($trip->currency_id == Auth::user()->employee->company->currency_id && ($trip->freight != Null && $trip->freight != "" )) {
                                                        $total_revenue =  $total_revenue + $trip->freight;
                                                        }elseif($trip->currency_id != Auth::user()->employee->company->currency_id && ($trip->exchange_customer_freight != Null && $trip->exchange_customer_freight != "" ) ) {
                                                            $total_revenue =  $total_revenue + $trip->exchange_customer_freight;
                                                        }
                                                }
                                        @endphp
                                        <center> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($total_revenue,2)}}</center>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                
                                <th class="text-center"><strong>Total Commision</strong></th>
                                <td class="unit text-center">
                                   @if (isset(Auth::user()->employee->company->currency_id)) 
                                    @php
                                        $total_commissions = 0;
                                        $transporter_trips = $trips->where('transporter_agreement' == 1);
                                            foreach ($transporter_trips as $trip) {
                                                if ($trip->currency_id == Auth::user()->employee->company->currency_id && ($trip->freight != Null && $trip->freight != "" ) && ($trip->transporter_freight != "" && $trip->transporter_freight != Null ) ) {
                                                    $total_commissions =  $total_commissions + ($trip->freight - $trip->transporter_freight);
                                                    }elseif($trip->currency_id != Auth::user()->employee->company->currency_id && ($trip->exchange_customer_freight != Null && $trip->exchange_customer_freight != "" ) && ($trip->exchange_transporter_freight != "" && $trip->exchange_transporter_freight != Null ) ) {
                                                        $total_commissions =  $total_commissions + ($trip->exchange_customer_freight - $trip->exchange_transporter_freight);
                                                    }
                                        }  
                                    @endphp
                                     <center>{{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($total_commissions,2)}}</center>
                                    @endif

                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Total Expenses</strong></th>
                                <td class="unit text-center">
                                    @if (isset(Auth::user()->employee->company->currency_id))
                                    @php
                                        $total_expenses = 0;
                                            foreach ($trips as $trip) {
                                            if ($trip->trip_expenses->count()>0) {
                                                foreach ($trip->trip_expenses as $expense) {
                                                    if ($expense->currency_id == Auth::user()->employee->company->currency_id && $expense->amount != Null && $expense->amount != "" ) {
                                                    $total_expenses =  $total_expenses + $expense->amount;
                                                    }elseif($expense->currency_id =! Auth::user()->employee->company->currency_id && $expense->exchange_amount != Null && $expense->exchange_amount != "" ) {
                                                        $total_expenses =  $total_expenses + $expense->exchange_amount;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                   <center>{{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($total_expenses,2)}}</center>
                                   @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Gross Profit</strong></th>
                                <td class="unit text-center">
                                    <center>{{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($total_revenue,2) }}</center>
                                  
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center"><strong>Net Profit</strong></th>
                                <td class="unit text-center">
                                   @if (isset($total_revenue) && isset($total_commissions) )
                                        @php
                                            $net_profit = $total_revenue - $total_commissions;
                                        @endphp
                                       <center>{{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($net_profit,2) }}</center> 
                                   @endif
                                </td>
                            </tr>
                           
                        </tbody>
                    </table>
                </main>
                <center> <footer style="   position: fixed; 
                    bottom: 0px; 
                    left: 0px; 
                    right: 0px;
                    height: 50px;">{{ucfirst($company->name)}} Data Summary</footer></center>  
               
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
</div>
