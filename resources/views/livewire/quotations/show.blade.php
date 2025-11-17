<div>
    <div class="row mt-30">
    <div class="col-md-10 col-md-offset-1">
        <!-- /.row -->
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation"class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Quotation Details</a></li>
            <li role="presentation"><a href="#quotation_items" aria-controls="quotation_items" role="tab" data-toggle="tab">Quotation Items</a></li>
             <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">

                        <tr>
                            <th class="w-10 text-center line-height-35">Quotation#</th>
                            <td class="w-20 line-height-35"> {{$quotation->quotation_number}}</td>
                        </tr>
                      
                        <tr>
                            <th class="w-10 text-center line-height-35">Created By</th>
                            <td class="w-20 line-height-35"> {{$quotation->user ? $quotation->user->name : ""}} {{$quotation->user ? $quotation->user->surname : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Bank Accounts</th>
                            <td class="w-20 line-height-35">
                                @foreach ($quotation->bank_accounts as $bank_account)
                                    {{ $bank_account->name }} {{ $bank_account->account_name }} {{ $bank_account->account_number }},
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Customer</th>
                            <td class="w-20 line-height-35">{{$quotation->customer ? $quotation->customer->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Date</th>
                            <td class="w-20 line-height-35">@if ($quotation->date)
                                {{\Carbon\Carbon::parse($quotation->date)->format('j F Y')}}
                                @endif</td>
                        </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Expiry</th>
                                <td class="w-20 line-height-35">@if ($quotation->expiry)
                                    {{\Carbon\Carbon::parse($quotation->expiry)->format('j F Y')}}
                                    @endif</td>
                            </tr> 
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$quotation->currency ? $quotation->currency->name : ""}}</td>
                            </tr>
                            @if ($quotation->exchange_rate)
                            <tr>
                                <th class="w-10 text-center line-height-35">Exchange Rate</th>
                                <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$quotation->exchange_rate}}</td>
                            </tr> 
                            @endif
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Subtotal</th>
                                <td class="w-20 line-height-35">
                                    @if ($quotation->subtotal)
                                    {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{ number_format($quotation->subtotal,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax Amount</th>
                                <td class="w-20 line-height-35">
                                    {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{ number_format($quotation->tax_amount ? $quotation->tax_amount : 0,2)}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Additional Cost(s)</th>
                                <td class="w-20 line-height-35">
                                    @foreach($quotation->additional_costs as $additional_cost)
                                        <strong>{{$additional_cost->cost_item ? $additional_cost->cost_item->name : ""}}</strong> {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{ number_format($additional_cost->total ? $additional_cost->total : 0,2)}}
                                    @endforeach
                                    
                                </td>
                            </tr>
                         
                            <tr>
                                <th class="w-10 text-center line-height-35">Total</th>
                                <td class="w-20 line-height-35">
                                    @if ($quotation->total)
                                    {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($quotation->total,2)}}
                                    @endif
                                </td>
                            </tr>
                            @if ($quotation->exchange_amount)
                            <tr>
                                <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                                <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$quotation->exchange_amount}}</td>
                            </tr> 
                            @endif
                    </tbody>
                </table>
              
            </div>
            <div role="tabpanel" class="tab-pane" id="quotation_items">
                @livewire('quotations.items', ['id' => $quotation->id])
            </div>
               <div role="tabpanel" class="tab-pane" id="trips">
                @livewire('quotations.trips', ['id' => $quotation->id])
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
