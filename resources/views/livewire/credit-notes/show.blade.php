<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Credit Note Details</a></li>
                <li role="presentation"><a href="#credit_note_items" aria-controls="credit_note_items" role="tab" data-toggle="tab">Credit Note Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">CreditNote#</th>
                                <td class="w-20 line-height-35">{{$credit_note->credit_note_number}}</td>
                            </tr>
                            @if ($credit_note->invoice)
                            <tr>
                                <th class="w-10 text-center line-height-35">Invoice#</th>
                                <td class="w-20 line-height-35"><a href="{{ route('invoices.show',$credit_note->invoice->id) }}" style="color:blue">{{$credit_note->invoice ? $credit_note->invoice->invoice_number : ""}}</a></td>
                            </tr>
                            
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$credit_note->user ? $credit_note->user->name : ""}} {{$credit_note->user ? $credit_note->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Subheading</th>
                                <td class="w-20 line-height-35">{{$credit_note->subheading}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Customer</th>
                                    <td class="w-20 line-height-35">{{$credit_note->customer ? $credit_note->customer->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Date</th>
                                    <td class="w-20 line-height-35">{{$credit_note->date}}</td>
                                </tr>
                               
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$credit_note->currency ? $credit_note->currency->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Invoice Total</th>
                                    <td class="w-20 line-height-35">
                                          @if ($credit_note->invoice_amount)
                                        {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->invoice_amount,2)}}
                                        @endif</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Credit Note Subtotal</th>
                                    <td class="w-20 line-height-35">
                                        @if ($credit_note->subtotal)
                                        {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->subtotal,2)}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">VAT</th>
                                    <td class="w-20 line-height-35">@ {{$credit_note->vat}}%</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">VAT AMT</th>
                                    <td class="w-20 line-height-35">
                                        @if ($credit_note->vat_amount)
                                            {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->vat_amount,2)}}
                                        @endif
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Credit Note Total</th>
                                    <td class="w-20 line-height-35">
                                        @if ($credit_note->total)
                                        {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->total,2)}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Invoice Balance</th>
                                    <td class="w-20 line-height-35">  @if ($credit_note->invoice_amount)
                                        {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->invoice_balance,2)}}
                                        @endif</td>
                                </tr>
                               
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{($credit_note->authorization == 'approved') ? 'success' : (($credit_note->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($credit_note->authorization == 'approved') ? 'approved' : (($credit_note->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                </tr>
                                @if ($credit_note->comments)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Comments</th>
                                    <td class="w-20 line-height-35">{{$credit_note->comments}}</td>
                                </tr>
                                @endif
                                 <tr>
                                    <th class="w-10 text-center line-height-35">Reason</th>
                                    <td class="w-20 line-height-35">{{$credit_note->credit_note_reason}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Terms & Conditions</th>
                                    <td class="w-20 line-height-35">{{$credit_note->memo}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Footer</th>
                                    <td class="w-20 line-height-35">{{$credit_note->footer}}</td>
                                </tr>
                               
                             
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="credit_note_items">
                  @livewire('credit-notes.credit-note-items', ['id' => $credit_note->id])
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
