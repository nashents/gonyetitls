
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Debit Note | @if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <x-loading/>
            <div class="invoice overflow-auto">
                <div style="min-width: 600px">
                    <header>
                        <div class="row">
                            <div class="col">
                                <a href="javascript:;">
                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                </a>
                            </div>
                            <div class="col company-details">

                                <h4 class="name" >
                                    <a target="_blank" href="javascript:;" style="color:  {{$company->color ? $company->color : "#000000" }}">
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
                                <div>
                                    VAT No.: {{$company->vat_number}}
                                </div>
                                <div>
                                    TIN.: {{$company->tin_number}}
                                </div>
                            </div>
                        </div>

                        <div style="padding-top: 25px; padding-bottom:15px">
                            <center><h2>DEBIT NOTE</h2></center>
                        </div>

                    </header>
                    <main>
                        <div class="row contacts">
                            <div class="col invoice-to" >
                                <div class="text-gray-light">BILL TO:</div>
                                <h6 class="to"> <strong>Vendor Name: </strong> {{$debit_note->vendor ? $debit_note->vendor->name : ""}}</h6>

                                <div class="address" >
                                    <strong>Vendor Address: </strong>
                                    @if (isset($debit_note->vendor->street_address) || isset($debit_note->vendor->suburb))
                                    {{$debit_note->vendor ? $debit_note->vendor->street_address : ""}} {{$debit_note->vendor ? $debit_note->vendor->suburb : ""}}, <br>
                                    @endif
                                    {{$debit_note->vendor ? $debit_note->vendor->city : ""}} {{$debit_note->vendor ? $debit_note->vendor->country : ""}}
                                </div>

                                @if (isset($debit_note->vendor->email))
                                <div class="email">
                                    <strong>Vendor Email: </strong><a href="mailto:{{$debit_note->vendor->email}}">{{$debit_note->vendor->email}}</a>
                                </div>
                                @endif
                                @if (isset($debit_note->vendor->phonenumber))
                                <div class="email">
                                    <strong>Vendor Phonenumber: </strong>{{$debit_note->vendor->phonenumber}}
                                </div>
                                @endif
                            </div>
                            <div class="col invoice-details">
                            <div class="date" style="padding-bottom: 3px"> <strong>Debit Note No.:</strong> {{$debit_note->debit_note_number}}</div>
                            <div class="date" style="padding-bottom: 3px"> <strong>Reference No.:</strong> {{$debit_note->bill ? $debit_note->bill->bill_number : ""}}</div>

                            <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$debit_note->date}}</div>

                            <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$debit_note->currency ? $debit_note->currency->name : ""}}</div>
                        </div>
                        </div>

                          <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"> <strong>Description</strong></th>
                                        <th class="text-right"><strong>Qty</strong></th>
                                        <th class="text-right"><strong>Amount</strong></th>
                                        <th class="text-right"><strong>Subtotal</strong></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($debit_note_items as $debit_note_item)
                                         <tr>
                                            <td class="text-center">
                                                {{$debit_note_item->description}}
                                            </td>
                                            <td class="unit text-right"> {{$debit_note_item->qty}}</td>
                                            <td class="unit text-right">
                                                @if ($debit_note_item->amount)
                                                {{ $debit_note->currency ? $debit_note->currency->symbol : "" }}{{number_format($debit_note_item->amount,2)}}
                                                @endif
                                            </td>
                                            <td class="unit text-right">
                                                @if ($debit_note_item->subtotal)
                                                    {{ $debit_note->currency ? $debit_note->currency->symbol : "" }}{{number_format($debit_note_item->subtotal,2)}}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No items</td></tr>
                                    @endforelse

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">SUB-TOTAL {{ $debit_note->currency ? $debit_note->currency->name : "" }} <small>(Excl)</small></td>
                                        <td>
                                            {{ $debit_note->currency ? $debit_note->currency->symbol : "" }}{{number_format($debit_note->subtotal,2)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">VAT TOTAL</td>
                                        <td>
                                            {{ $debit_note->currency ? $debit_note->currency->symbol : "" }}{{number_format($debit_note->tax_amount ?: 0,2)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">DEBIT NOTE TOTAL {{ $debit_note->currency ? $debit_note->currency->name : "" }} </td>
                                        <td>
                                            @if ($debit_note->total)
                                                  {{ $debit_note->currency ? $debit_note->currency->symbol : "" }}{{number_format($debit_note->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <br>
                        @if ($debit_note->debit_note_reason)
                            <div class="notices">
                            <strong>REASON FOR DEBIT: </strong>{{$debit_note->debit_note_reason}}
                            </div>
                        @endif
                        @if ($debit_note->memo)
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$debit_note->memo}}</div>
                            </div>
                        @endif

                        <br>

                    </main>

                     <center>
                            <footer style=" position:fixed; bottom: 0px; left: 0px; right: 0px; ">
                                {{$debit_note->footer}}
                                @if ($company->show_branding ?? true)
                                    <br>
                                    <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/logo.png')}}" alt="" style="width: 20%; height:20%">
                                @endif
                            </footer>
                    </center>
                </div>
                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                <div></div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
  window.addEventListener("load", window.print());
</script>
@endsection
