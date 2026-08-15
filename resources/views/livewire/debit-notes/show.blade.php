<div>
    <div class="row mt-30">

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Debit Note Details</a></li>
                <li role="presentation"><a href="#debit_note_items" aria-controls="debit_note_items" role="tab" data-toggle="tab">Debit Note Items</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">DebitNote#</th>
                                <td class="w-20 line-height-35">{{$debit_note->debit_note_number}}</td>
                            </tr>
                            @if ($debit_note->bill)
                            <tr>
                                <th class="w-10 text-center line-height-35">Bill#</th>
                                <td class="w-20 line-height-35"><a href="{{ route('bills.show',$debit_note->bill->id) }}" style="color:blue">{{$debit_note->bill ? $debit_note->bill->bill_number : ""}}</a></td>
                            </tr>

                            @endif

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$debit_note->user ? $debit_note->user->name : ""}} {{$debit_note->user ? $debit_note->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Subheading</th>
                                <td class="w-20 line-height-35">{{$debit_note->subheading}}</td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vendor</th>
                                    <td class="w-20 line-height-35">{{$debit_note->vendor ? $debit_note->vendor->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Date</th>
                                    <td class="w-20 line-height-35">{{$debit_note->date}}</td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$debit_note->currency ? $debit_note->currency->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Bill Total</th>
                                    <td class="w-20 line-height-35">
                                          @if ($debit_note->bill_amount)
                                        {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->bill_amount,2)}}
                                        @endif</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Debit Note Subtotal</th>
                                    <td class="w-20 line-height-35">
                                        @if ($debit_note->subtotal)
                                        {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->subtotal,2)}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">VAT AMT</th>
                                    <td class="w-20 line-height-35">
                                        @if ($debit_note->tax_amount)
                                            {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->tax_amount,2)}}
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Debit Note Total</th>
                                    <td class="w-20 line-height-35">
                                        @if ($debit_note->total)
                                        {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->total,2)}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Bill Balance</th>
                                    <td class="w-20 line-height-35">  @if ($debit_note->bill_amount)
                                        {{$debit_note->currency ? $debit_note->currency->symbol : ""}}{{number_format($debit_note->bill_balance,2)}}
                                        @endif</td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{($debit_note->authorization == 'approved') ? 'success' : (($debit_note->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($debit_note->authorization == 'approved') ? 'approved' : (($debit_note->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                </tr>
                                @if ($debit_note->reason)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Comments</th>
                                    <td class="w-20 line-height-35">{{$debit_note->reason}}</td>
                                </tr>
                                @endif
                                 <tr>
                                    <th class="w-10 text-center line-height-35">Reason</th>
                                    <td class="w-20 line-height-35">{{$debit_note->debit_note_reason}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Terms & Conditions</th>
                                    <td class="w-20 line-height-35">{{$debit_note->memo}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Footer</th>
                                    <td class="w-20 line-height-35">{{$debit_note->footer}}</td>
                                </tr>


                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="debit_note_items">
                  @livewire('debit-notes.debit-note-items', ['id' => $debit_note->id])
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
