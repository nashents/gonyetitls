
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Payslip | @if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
             <div id="print-area">
            <div id="invoice">
                <div class="toolbar hidden-print">
                    <div class="text-end">
                        <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left"></i> Back</button>
                         <a href="javascript:void(0)" onclick="printSection()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color: black"></i> Print</a>
                        {{-- <a href="{{route('payslip.pdf', $payroll_salary->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o"></i> Export as PDF</a> --}}
                    </div>
                    <hr>
                </div>
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
                                  
                                    <h3 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
									{{$company->name}}
									</a>
                                    </h3>
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
                                    <h6 class="to">Emp: {{$employee->employee_number }} {{$employee->name}} {{$employee->surname}} </h6>
                                    <div class="text-gray-light">Dpt: {{$employee->departments->first()?->name}}</div>
                                    <div class="text-gray-light">Title: {{$employee->post}}</div>
                                    <div class="address">Add.: {{$employee->street_address}} {{$employee->suburb}}, {{$employee->city}}, {{$employee->country}}</div>
                                    <div class="email">Email.: {{$employee->email}}</div>
                                    <div class="text-gray-light">ID.: {{$employee->idnumber}}</div>
                                    <div class="text-gray-light">Pay Date.: {{$company->pay_date}}</div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date">Payslip for.: {{$payroll_salary->payroll->month}} {{$payroll_salary->payroll->year}}</div>
                                    <div class="date"><span>Leave Due.: {{$employee->leave_days}}</span> <span class="ml-2">Leave Taken.: {{$employee->leaves->count()}}</span> <span class="ml-2">GRD.: {{$employee->grade}}</span></div>
                                    <div class="date"><span>Date Joined.: {{$employee->start_date}}</span> <span class="ml-2">D.O.B.: {{$employee->dob}}</span> </div>
                                    <div class="date"><span>Exchange Rate.: {{$payroll_salary->payroll ? $payroll_salary->payroll->exchange_rate : ""}}</span></div>
                                    <div class="date"><span>Bank.: {{$employee->bank ? $employee->bank->name : ""}}</span></div>
                                </div>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"><strong>Earnings</strong></th>
                                        <th class="text-center"> <strong>Deductions</strong></th>
                                        <th class="text-center"> <strong>Currency</strong></th>
                                        <th class="text-right"> <strong>Amount</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">
                                            Basic Pay
                                        </td>
                                        <td class="text-center"></td>
                                        <td class="text-center">{{$currency?->name}}</td>
                                        <td class="text-right">
                                            {{$currency?->symbol}}{{number_format($payroll_salary->basic,2)}}
                                        </td>
                                    </tr>
                                    @foreach ($payroll_salary->payroll_salary_items as $item)
                                    @if ($item)
                                        @php
                                            $item_currency  = $item?->currency;
                                        @endphp
                                    <tr>
                                        
                                        <td class="text-center">
                                            @if ($payroll_salary_item->salary_item->allowance)
                                                {{$payroll_salary_item->salary_item->allowance->name}}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($payroll_salary_item->salary_item->loan)
                                            Loan : {{ $payroll_salary_item->salary_item->loan->loan_number }} {{ $payroll_salary_item->salary_item->loan->loan_type ? $payroll_salary_item->salary_item->loan->loan_type->name : "" }}
                                            @elseif ($payroll_salary_item->salary_item->deduction)
                                            {{$payroll_salary_item->salary_item->deduction->name}}
                                            @endif 
                                        </td>
                                        <td class="text-center">{{isset($item_currency) ? $item_currency->name : $currency?->name}}</td>
                                        <td class="text-right">
                                            @if ($payroll_salary_item->amount)
                                             {{isset($item_currency) ? $item_currency->symbol : $currency?->symbol}}{{number_format($payroll_salary_item->amount,2)}}        
                                            @endif
                                        </td>
                                    </tr>
                                   
                                    @endif
                                   
                                    @endforeach
                                   

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td colspan="2">Basic Pay</td>
                                        <td class="text-right">  {{$currency?->symbol}}{{number_format($payroll_salary->basic,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td colspan="2">Total Earnings</td>
                                        <td class="text-right">  {{$currency?->symbol}}{{number_format($payroll_salary->total_allowances,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td colspan="2">Total Deductions</td>
                                        <td class="text-right">  {{$currency?->symbol}}{{number_format($payroll_salary->total_deductions,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td colspan="2">Gross Pay</td>
                                        <td class="text-right">  {{$currency?->symbol}}{{number_format($payroll_salary->gross,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="1"></td>
                                        <td colspan="2">Net Pay</td>
                                        <td class="text-right">  {{$currency?->symbol}}{{number_format($payroll_salary->net,2)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </main>
                  
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>


@endsection
