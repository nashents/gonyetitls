<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 
  <title>Payslip Template</title>
 

@include('includes.css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice">
                    <div class="invoice">
                        <div >
                            <header>
                                <div class="row" style="margin-top:-20px;">
                                    <div class="col">
                                        <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="100" alt=""></a>
                                    </div>
                                    <div class="col company-details"  style="margin-top:-70px;">
                                      
                                        <h3 class="name" >
                                            <a target="_blank" href="#" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
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
                                        <div class="text-gray-light">Emp: {{$employee->employee_number }} {{$employee->name}} {{$employee->surname}} </div>
                                        <div class="text-gray-light">Dpt: {{$employee->departments->first()->name}}</div>
                                        <div class="text-gray-light">Title: {{$employee->post}}</div>
                                        <div class="address">Add.: {{$employee->street_address}} {{$employee->suburb}}, {{$employee->city}}, {{$employee->country}}</div>
                                        <div class="email">Email.: <a href="mailto:{{$employee->email}}">{{$employee->email}}</a></div>
                                        <div class="text-gray-light">ID.: {{$employee->idnumber}}</div>
                                        <div class="text-gray-light">Pay Date.: {{$company->pay_date}}</div>
                                    </div>
                                    <div class="col invoice-details" style="margin-top:-120px;">
                                        <div class="date" style="font-size: 16px;">Payslip for.: {{$payroll_salary->payroll->month}} {{$payroll_salary->payroll->year}}</div>
                                        <div class="date" style="font-size: 16px;"><span>Leave Due.: {{$employee->leave_days}}</span> <span class="ml-2">Leave Taken.: {{$employee->leaves->count()}}</span> <span class="ml-2">GRD.: {{$employee->grade}}</span></div>
                                        <div class="date" style="font-size: 16px;"><span>Date Joined.: {{$employee->start_date}}</span> <span class="ml-2">D.O.B.: {{$employee->dob}}</span> </div>
                                        <div class="date" style="font-size: 16px;"><span>Exchange Rate.: {{$payroll_salary->payroll ? $payroll_salary->payroll->exchange_rate : ""}}</span></div>
                                        <div class="date" style="font-size: 16px;"><span>Bank.: {{$employee->bank ? $employee->bank->name : ""}}</span></div>
                                        
                                    </div>
                                </div>
                                <br>
                                <br>
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
                                        @foreach ($payroll_salary->payroll_salary_items as $payroll_salary_item)
                                        
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
                                            <td class="text-right">  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->basic,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Total Earnings</td>
                                            <td class="text-right">  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->total_allowances,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Total Deductions</td>
                                            <td class="text-right">  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->total_deductions,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Gross Pay</td>
                                            <td class="text-right">  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->gross,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">Net Pay</td>
                                            <td class="text-right">  {{$payroll_salary_item->payroll_salary->salary->currency->symbol}}{{number_format($payroll_salary->net,2)}}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                              
                        </div>
                        <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>