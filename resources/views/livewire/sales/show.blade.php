<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Sale Details</a></li>
                <li role="presentation"><a href="#sale_items" aria-controls="sale_items" role="tab" data-toggle="tab">Sale Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Sale#</th>
                                <td class="w-20 line-height-35">{{$sale->sale_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$sale->user ? $sale->user->name : ""}} {{$sale->user ? $sale->user->surname : ""}} </td>
                            </tr>
                           
                                <tr>
                                    <th class="w-10 text-center line-height-35">Customer</th>
                                    <td class="w-20 line-height-35">{{$sale->customer ? $sale->customer->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Date</th>
                                    <td class="w-20 line-height-35">{{$sale->date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$sale->currency ? $sale->currency->name : ""}}</td>
                                </tr>
                                @if ($sale->exchange_rate)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency conversion:</th>
                                    <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($sale->exchange_amount,2)}} @ {{ $sale->exchange_rate}} </td>
                                </tr> 
                                @endif
                                <tr>
                                    <th class="w-10 text-center line-height-35">Subtotal</th>
                                    <td class="w-20 line-height-35">
                                        @if ($sale->subtotal)
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->subtotal,2)}}
                                        @endif
                                    </td>
                                </tr>
                                @if ($sale->tax_amount != "" && $sale->tax_amount > 0)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tax Amount</th>
                                    <td class="w-20 line-height-35">
                                        @if ($sale->tax_amount)
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->tax_amount,2)}}
                                        @endif
                                    </td>
                                </tr>
                                @endif
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">
                                        @if ($sale->total)
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->total,2)}}
                                        @endif
                                    </td>
                                </tr>
                                @if ($sale->exchange_amount)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                                    <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$sale->exchange_amount}}</td>
                                </tr> 
                                @endif
                                <tr>
                                    <th class="w-10 text-center line-height-35">Comments</th>
                                    <td class="w-20 line-height-35">{{$sale->comments}}</td>
                                </tr>
                                
                               
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="sale_items">
                  @livewire('sales.sale-items', ['id' => $sale->id])
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
