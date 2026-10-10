<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                            <div class="panel-title">
                                <h5 style="display:inline-block; margin-right:15px;"><i class="fa fa-sitemap"></i> Payroll Cost by Department <small>{{ $periodLabel }}</small></h5>
                                <button wire:click="export" class="btn btn-default btn-sm"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            {{-- Filters --}}
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>From (period start)</label>
                                    <input wire:model="dateFrom" type="date" class="form-control" @if($payrollRunId) disabled @endif>
                                </div>
                                <div class="col-md-2">
                                    <label>To</label>
                                    <input wire:model="dateTo" type="date" class="form-control" @if($payrollRunId) disabled @endif>
                                </div>
                                <div class="col-md-2">
                                    <label>Payroll Run</label>
                                    <select wire:model="payrollRunId" class="form-control">
                                        <option value="">All runs in period</option>
                                        @foreach($runs as $run)
                                            <option value="{{ $run->id }}">{{ $run->name }} ({{ ucfirst($run->status) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Department</label>
                                    <select wire:model="departmentId" class="form-control">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                        <option value="none">Unassigned</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Currency</label>
                                    <select wire:model="currencyId" class="form-control">
                                        <option value="">All Currencies</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button wire:click="thisMonth" class="btn btn-default btn-sm">This Month</button>
                                        <button wire:click="lastMonth" class="btn btn-default btn-sm">Last Month</button>
                                    </div>
                                    <label style="font-weight:normal; margin-top:6px;">
                                        <input wire:model="includeDrafts" type="checkbox"> Include draft runs
                                    </label>
                                </div>
                            </div>

                            <p class="text-muted" style="margin-bottom:15px;">
                                Each employee is counted once, against their <strong>default department</strong> at the time the run was built.
                                Approved, locked and posted runs are included; reversed runs are excluded{{ $includeDrafts ? '' : ', as are drafts' }}.
                                Total Cost = Gross + employer NSSA/NEC/Pension. Click a department to see its employees.
                            </p>

                            @forelse($groups as $group)
                                @php $currencyKey = $group['currency']?->id ?? 0; $symbol = $group['currency']?->symbol; @endphp
                                <div wire:key="dept-cost-currency-{{ $currencyKey }}" style="margin-bottom:25px;">
                                    <h5><strong>{{ $group['currency']?->name ?? 'No currency' }}</strong></h5>
                                    <div style="overflow-x:auto;">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Department</th>
                                                    <th class="text-center">Employees</th>
                                                    <th class="text-right">Basic</th>
                                                    <th class="text-right">Allowances</th>
                                                    <th class="text-right">Gross</th>
                                                    <th class="text-right">PAYE + AIDS Levy</th>
                                                    <th class="text-right">Employee NSSA/NEC/Pension</th>
                                                    <th class="text-right">Other Deductions</th>
                                                    <th class="text-right">Net Pay</th>
                                                    <th class="text-right">Employer Contributions</th>
                                                    <th class="text-right">Total Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($group['departments'] as $department)
                                                    @php $rowKey = $currencyKey . ':' . ($department['department_id'] ?? 'none'); $open = in_array($rowKey, $expanded, true); @endphp
                                                    <tr wire:key="dept-cost-row-{{ $rowKey }}" wire:click="toggle('{{ $rowKey }}')" style="cursor:pointer;">
                                                        <td>
                                                            <i class="fa fa-{{ $open ? 'minus' : 'plus' }}-square-o"></i>
                                                            <strong>{{ $department['department_name'] }}</strong>
                                                        </td>
                                                        <td class="text-center">{{ $department['totals']['headcount'] }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['basic'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['allowances'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['gross'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['paye'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['statutory_employee'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['other_deductions'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['net'], 2) }}</td>
                                                        <td class="text-right">{{ number_format($department['totals']['employer'], 2) }}</td>
                                                        <td class="text-right"><strong>{{ $symbol }}{{ number_format($department['totals']['total_cost'], 2) }}</strong></td>
                                                    </tr>
                                                    @if($open)
                                                        @foreach($department['employees'] as $line)
                                                            <tr wire:key="dept-cost-line-{{ $rowKey }}-{{ $line['payroll_salary_id'] }}" style="background:#f9f9f9; font-size:0.92em;">
                                                                <td style="padding-left:30px;">
                                                                    {{ $line['employee_name'] }}
                                                                    @if($line['employee_number'])<small class="text-muted">({{ $line['employee_number'] }})</small>@endif
                                                                    <br><small class="text-muted">{{ $line['run_name'] }}</small>
                                                                </td>
                                                                <td></td>
                                                                <td class="text-right">{{ number_format($line['basic'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['allowances'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['gross'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['paye'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['statutory_employee'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['other_deductions'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['net'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['employer'], 2) }}</td>
                                                                <td class="text-right">{{ number_format($line['total_cost'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr style="font-weight:bold; background:#eef2f5;">
                                                    <td>Total</td>
                                                    <td class="text-center">{{ $group['totals']['headcount'] }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['basic'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['allowances'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['gross'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['paye'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['statutory_employee'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['other_deductions'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['net'], 2) }}</td>
                                                    <td class="text-right">{{ number_format($group['totals']['employer'], 2) }}</td>
                                                    <td class="text-right">{{ $symbol }}{{ number_format($group['totals']['total_cost'], 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted" style="padding:40px;">
                                    No payroll runs match these filters.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
