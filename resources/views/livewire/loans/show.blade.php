<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Loan Details</a></li>
                <li role="presentation"><a href="#payments" aria-controls="payments" role="tab" data-toggle="tab">Payments</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Loan#</th>
                                <td class="w-20 line-height-35">{{$loan->loan_number}}</td>
                            </tr>
                            @if ($loan->loan_type)
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$loan->loan_type ? $loan->loan_type->name : ""}}</td>
                            </tr> 
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$loan->user ? $loan->user->name : ""}} {{$loan->user ? $loan->user->surname : ""}} </td>
                            </tr>

                            <tr>
                                <th class="w-10 text-center line-height-35">Date</th>
                                <td class="w-20 line-height-35">{{$loan->start_date}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$loan->currency ? $loan->currency->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Amount</th>
                                    <td class="w-20 line-height-35">
                                         @if ($loan->amount)
                                        {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->amount,2)}}        
                                   @endif
                                </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Interest</th>
                                    <td class="w-20 line-height-35">{{$loan->interest}}%</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Payback Period</th>
                                    <td class="w-20 line-height-35">{{$loan->period}} Months</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">  @if ($loan->total)
                                        {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->total,2)}}        
                                    @endif</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Installments</th>
                                    <td class="w-20 line-height-35">
                                        @if ($loan->payment_per_month)
                                        {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month,2)}}        
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Balance</th>
                                    <td class="w-20 line-height-35">
                                        @if ($loan->balance)
                                        {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->balance,2)}}        
                                    @endif
                                    </td>
                                </tr>
                                @if ($loan->purpose)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Purpose of Loan</th>
                                    <td class="w-20 line-height-35">{{$loan->purpose}}</td>
                                </tr>
                             @endif
                                <tr>
                                    <th class="w-10 text-center line-height-35">Payment Status</th>
                                    <td class="w-20 line-height-35"><span class="label label-{{($loan->status == 'Paid') ? 'success' : (($loan->status == 'Partial') ? 'warning' : 'danger') }}">{{ $loan->status }}</span></td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{($loan->authorization == 'approved') ? 'success' : (($loan->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($loan->authorization == 'approved') ? 'approved' : (($loan->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                </tr>
                             
                                @if ($loan->reason)
                                    <tr>
                                        <th class="w-10 text-center line-height-35">Authorization Comments</th>
                                        <td class="w-20 line-height-35">{{$loan->reason}}</td>
                                    </tr>
                                @endif       
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="payments">
                   
                          <table  class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                            <thead >
                                <th class="th-sm">Payment#
                                </th>
                                <th class="th-sm">Loan#
                                </th>
                                <th class="th-sm">MOP
                                </th>
                                <th class="th-sm">Currency
                                </th>
                                <th class="th-sm">Paid
                                </th>
                                <th class="th-sm">Bal
                                </th>
                                <th class="th-sm">Actions
                                </th>

                              </tr>
                            </thead>
                       
                            @if (isset($payments))
                            <tbody>
                                @forelse ($payments as $payment)
                              <tr>
                                
                                <td>{{ucfirst($payment->payment_number)}}</td>
                                <td>
                                    @if ($payment->loan)
                                    <a href="{{ route('loans.show',$payment->loan->id) }}" style="color:blue">{{$payment->loan ? $payment->loan->loan_number : ""}}</a>
                                    @endif
                                </td>
                                <td>{{$payment->mode_of_payment}}</td>
                                <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                                <td>
                                    @if ($payment->amount)
                                        {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                                    @endif
                                </td>
                                <td>
                                    @if ($payment->loan)
                                        {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->loan->balance,2)}} 
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
                              @empty
                              <tr>
                                <td colspan="7">
                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                        No Loan Payments Found ....
                                    </div>  
                                </td>
                              </tr>  
                                @endforelse
                            </tbody>
                            @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                            @endif
                          

                          </table>
                          <nav class="text-center" style="float: right">
                            <ul class="pagination rounded-corners">
                                @if (isset($payments))
                                    {{ $payments->links() }} 
                                @endif 
                            </ul>
                        </nav>   
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
