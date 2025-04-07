<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <table id="payroll_salariesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Employee
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Basic
                </th>
                <th class="th-sm">Gross
                </th>
                <th class="th-sm">Net
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            @if ($payroll_salaries->count()>0)
            <tbody>
                @foreach ($payroll_salaries as $payroll_salary)
              <tr>
                <td>{{$payroll_salary->employee ? $payroll_salary->employee->name : ""}} {{$payroll_salary->employee ? $payroll_salary->employee->surname : ""}}</td>
                <td>
                 
                    {{$payroll_salary->salary->currency ? $payroll_salary->salary->currency->name : ""}}
                 
                </td>
                <td>
                  @if ($payroll_salary->basic)
                  {{$payroll_salary->salary->currency ? $payroll_salary->salary->currency->symbol : ""}}{{number_format($payroll_salary->basic,2)}}
                  @endif
                 </td>
                <td>
                  @if ($payroll_salary->gross)
                  {{$payroll_salary->salary->currency ? $payroll_salary->salary->currency->symbol : ""}}{{number_format($payroll_salary->gross,2)}} 
                  @endif
                 </td>
                <td>
                  @if ($payroll_salary->net)
                  {{$payroll_salary->salary->currency ? $payroll_salary->salary->currency->symbol : ""}}{{number_format($payroll_salary->net,2)}}
                  @endif
                </td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('payslip.preview', $payroll_salary->id) }}"><i class="fa fa-file color-success"></i>Payslip</a></li>
                        </ul>
                    </div>
                 
            </td>
              </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>
</div>
