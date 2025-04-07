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
                                                <h5>Profit & Loss</h5>
                                             
                                            </div>
                                        </div>
                                        <div class="panel-body">

                                            <div class="row">
                                              
                                                <form wire:submit.prevent="generateStatement()">
                                                <div class="col-lg-4">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                   Select Horse
                                                    </span>
                                                    <select wire:model.debounce.300ms="selectedHorse" class="form-control" aria-label="..." >
                                                        <option value="">Select Horse</option>
                                                        @foreach ($horses as $horse)
                                                        <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>

                                       
                                                <!-- /.col-lg-6 -->
                                                
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  From
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div>
                                                <div class="col-lg-2" style="margin-left: 10px">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                  To
                                                  </span>
                                                  <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                                    </div>
                                                    <!-- /input-group -->
                                                </div> 
                                                </form>
                                                @if (isset($selectedHorse) && isset($from) && isset($to))
                                                <div class="col-lg-2" >
                                                <a href="{{route('horses.statement.preview',['selectedHorse' => $selectedHorse, 'from' => $from, 'to' => $to])}}"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-eye"></i>Priview Report (PDF)</a>
                                                </div>
                                                @endif
                                                
                                            </div>
                                            <!-- /.row -->
                                            <br>
                                            <br>
                                         
                                            <div class="row">
                                                <div class="col-md-4">
                                                </div>
                                                <div class="col-md-6" >
                                                    <div class="col-lg-2"  >
                                                        <div class="btn-group" >
                                                            <button type="buttom" wire:click.prevent="set_report('summary')" class="btn btn-default {{$summary == "summary" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                Summary
                                                            </button>
                                                           
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                    <div class="col-lg-2" style="margin-left: 25px">
                                                        <div class="btn-group" >
                                                            <button type="buttom" wire:click.prevent="set_report('details')" class="btn btn-default {{$details == "details" ? 'border-primary' : ""}} btn-wide btn-rounded" type="button"  aria-haspopup="true" aria-expanded="true">
                                                                 Details
                                                             </button>
                                                        </div>
                                                        <!-- /input-group -->
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <center>
                                                    <div class="col-md-2">

                                                    </div>
                                                    <div class="col-md-2">
                                                        <h5>Income</h5>
                                                        <br>
                                                        <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}} - </h4>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <h5>Cost Of Goods Sold</h5>
                                                        <br>
                                                        <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_cost_of_goods_sold ? $total_cost_of_goods_sold : 0,2)}} -</h4>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <h5>Operating Expenses</h5>
                                                        <br>
                                                        <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_operating_expenses ? $total_operating_expenses : 0,2) }} =</h4>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <h5>Net Profit</h5>
                                                        <br>
                                                        <h4 style="margin-top:-15px">{{$default_currency->name}} {{$default_currency->symbol}}{{ number_format($net_profit ? $net_profit : 0,2)}}</h4>
                                                    </div>
                                                    <div class="col-md-2">
                                                        
                                                    </div>
                                                </center>
                                          
                                            </div>
                                      
                                            <!-- /.col-md-12 -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                           
                       
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">   
                             
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="panel">
                                        <div class="panel-heading">
                                            <div class="panel-title" >
                                                <div class="col-xs-5 p-n">
                                                    @if (isset($selected_horse))
                                                    <strong>{{$selected_horse->registration_number}} {{$selected_horse->fleet_number ? $selected_horse->fleet_number: ""}}</strong>
                                                    @endif
                                                  
                                                </div>
                                                <div class="col-xs-6 p-n">
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >From: {{$from}}</span></strong></p>
                                                    <p style="font-size: 15px;"><strong><span style="float: right" >To: {{$to}}</span></strong></p> 
                                                </div>
                                               
                                            </div>
                                            
                                        </div>
                                        <br>
                                        <br>
                                        <hr style="width:100%;", size="3", color=black>  
                                       

                                        <div class="panel-body ">

                                            @if (isset($summary) && $details == Null)

                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Income</span> 
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>  
                                            
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Cost of Goods Sold</span> 
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_cost_of_goods_sold ? $total_cost_of_goods_sold : 0,2) }}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>  
                                           
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="margin-left:5px">GROSS PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>
                                                <!-- /.col-md-6 -->
                                              
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($gross_profit ? $gross_profit : 0,2)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$gross_profit_percentage ? $gross_profit_percentage."%" : ""}}</span></p>
                                                    
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <br>
                                            <hr style="width:100%;", size="3", color=black>  
                                            <div class="col-xs-12 p-n" >
                                                <div class="col-xs-5 p-n">
                                                  <span style="margin-left:5px">Operating Expenses</span> 
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_operating_expenses ? $total_operating_expenses : 0 ,2)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>  
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3; ">
                                                <div class="col-xs-5 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                    <strong><span style="margin-left:5px">NET PROFIT</span></strong>
                                                    <p style="margin-left:5px">As a percentage of Total Income</p>
                                                </div>
                                               
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($net_profit ? $net_profit : 0,2)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$net_profit_percentage ? $net_profit_percentage."%" : ""}}</span></p>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                       
                                        @elseif (isset($details) && $summary == Null)

                                      
                                            <div class="col-xs-12 p-n" style="background-color: #D3D3D3">
                                                <div class="col-xs-5 p-n">
                                                  <strong><span style="margin-left:5px">Income</span> </strong>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <hr style="width:100%;", size="3", color=black>  
                                          
                                          
                                                <div class="col-xs-12 p-n">
                                                    <div class="col-xs-5 p-n">
                                                    <span style="margin-left:5px">Sales</span> 
                                                    </div>
                                        
                                                    <div class="col-xs-6 p-n">
                                                    <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}}</span>
                                                    </div>
                                                    <!-- /.col-md-6 -->
                                                </div>
                                                <hr style="width:100%;", size="3", color=black> 
                                        
                                            
                                            <div class="col-xs-12 p-n">
                                                <div class="col-xs-5 p-n">
                                                    <strong><span style="margin-left:5px">Total Income</span></strong> 
                                                </div>
                                                <!-- /.col-md-6 -->
                                                <div class="col-xs-6 p-n">
                                                <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_income ? $total_income : 0,2)}}</span>
                                                </div>
                                                <!-- /.col-md-6 -->
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
                                                            </div>
                                                            <!-- /.col-md-6 -->
                                                        
                                                            <div class="col-xs-6 p-n">
                                                            <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($total_account_cost_of_goods_sold ? $total_account_cost_of_goods_sold : 0,2)}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->
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
                                                
                                            <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;">
                                                <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($gross_profit ? $gross_profit : 0,2)}}</span></strong>
                                                <br>
                                                <p><span style="float: right">{{$gross_profit_percentage ? $gross_profit_percentage."%" : ""}}</span></p>
                                                
                                            </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                            <br>
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
                                                        })->get();
                                                    
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


                                                    @if (isset($account_bill_expenses) && $account_bill_expenses->count()> 0)
                                                        <div class="col-xs-12 p-n">
                                                            <div class="col-xs-5 p-n">
                                                            <span style="margin-left:5px">{{$account->name}}</span> 
                                                            </div>
                                                            <!-- /.col-md-6 -->
                                                        
                                                            <div class="col-xs-6 p-n">
                                                            <span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{ number_format($total_account_operating_expenses ? $total_account_operating_expenses : 0,2)}}</span>
                                                            </div>
                                                            <!-- /.col-md-6 -->
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
                                                
                                                <div class="col-xs-6 p-n" style="margin-top: 10px; margin-bottom: -10px;" >
                                                    <strong><span style="float: right">{{$default_currency->name}} {{$default_currency->symbol}}{{number_format($net_profit ? $net_profit : 0,2)}}</span></strong>
                                                    <br>
                                                    <p><span style="float: right">{{$net_profit_percentage ? $net_profit_percentage."%" : ""}}</span></p>
                                                </div>
                                                <!-- /.col-md-6 -->
                                            </div>
                                       
                                        @endif
                                    </div>

                                    </div>
                                </div>
                             
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
