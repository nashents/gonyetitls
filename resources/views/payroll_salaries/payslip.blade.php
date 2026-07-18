<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Payslip</title>
@include('includes.css')

</head>
<body style="font-family: FontAwesome; font-size: 14px;">

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice">
                    <div class="invoice overflow-auto">
                        <div style="min-width: 600px">
                            <header>
                                <div class="row">
                                    <div class="col">
                                        <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                    </div>
                                    <div class="col company-details">
                                        <h3 class="name" style="color: {{ $company->color ? $company->color : '#000000' }}">
                                            {{$company->name}}
                                        </h3>
                                        <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                        <div>{{$company->phonenumber}}</div>
                                        <div>{{$company->email}}</div>
                                    </div>
                                </div>
                            </header>
                            <main>
                                <div class="row contacts">
                                    <div class="col invoice-to">
                                        <h6 class="to"><strong>Emp.:</strong> {{$employee->employee_number ? "(".$employee->employee_number.")" : "" }} {{$employee->name}} {{$employee->surname}} </h6>
                                        <div class="text-gray-light"><strong>Dpt.:</strong> {{$employee->departments->first()->name}}</div>
                                        <div class="text-gray-light">Title.:<strong></strong> {{$employee->post}}</div>
                                        <div class="address"><strong>Add.:</strong> {{$employee->street_address}} {{$employee->suburb}}, {{$employee->city}}, {{$employee->country}}</div>
                                        <div class="email"><strong>Email.:</strong> {{$employee->email}}</div>
                                        <div class="text-gray-light"><strong>ID.:</strong> {{$employee->idnumber}}</div>
                                        <div class="text-gray-light"><strong>Pay Date.:</strong> {{$company->pay_date}}</div>
                                    </div>
                                    <div class="col invoice-details">
                                        <div class="date"><strong></strong>Payslip for.: {{$payroll_salary->payroll->month}} {{$payroll_salary->payroll->year}}</div>
                                        <div class="date"><span><strong>Leave Due.:</strong> {{$employee->leave_days}}</span> <span class="ml-2"><strong>Leave Taken.:</strong> {{$employee->leaves->count()}}</span> <span class="ml-2"><strong>GRD.:</strong> {{$employee->grade}}</span></div>
                                        <div class="date"><span><strong>Date Joined.:</strong> {{$employee->start_date}}</span> <span class="ml-2"><strong>D.O.B.:</strong> {{$employee->dob}}</span> </div>
                                        <div class="date"><span><strong>Exchange Rate.:</strong> {{$payroll_salary->payroll ? $payroll_salary->payroll->exchange_rate : ""}}</span></div>
                                        <div class="date"><span><strong>Bank.:</strong> {{$employee->bank ? $employee->bank->name : ""}}</span></div>
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
                                            <td class="text-center">{{$payroll_salary->salary->currency->name}}</td>
                                            <td class="text-right">
                                                {{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->basic,2)}}
                                            </td>
                                        </tr>
                                        @foreach ($payroll_salary_items as $payroll_salary_item)
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
                                            <td class="text-center">{{$payroll_salary_item->payroll_salary->salary->currency->name}}</td>
                                            <td class="text-right">
                                                @if ($payroll_salary_item->amount)
                                                  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary_item->amount,2)}}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Basic Pay</td>
                                            <td class="text-right">{{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->basic,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Total Earnings</td>
                                            <td class="text-right">{{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->total_allowances,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Total Deductions</td>
                                            <td class="text-right">{{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->total_deductions,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Gross Pay</td>
                                            <td class="text-right">{{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->gross,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Net Pay</td>
                                            <td class="text-right">{{$payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->net,2)}}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </main>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
