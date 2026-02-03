<div>
    <x-loading/>
    {{-- <a href="" data-toggle="modal" data-target="#salary_itemModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Salary Item</a> --}}
    <br>
    <br>
    <table id="salary_itemsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
            <th class="th-sm">Type
            </th>
            <th class="th-sm">Item
            </th>
            <th class="th-sm">Currency
            </th>
            <th class="th-sm">Amount
            </th>
         
          </tr>
        </thead>

        <tbody>
            @foreach ($salary_items as $salary_item)
          <tr>
         
            <td>
                @if ($salary_item->allowance)
                    Earning
                @elseif($salary_item->deduction)
                    Deduction
                @elseif($salary_item->loan)
                    Deduction
                @elseif($salary_item->recovery)
                    @if ($salary_item->recovery->type == "Gain")
                     Earning
                    @elseif($salary_item->recovery->type == "Loss")
                     Deduction
                    @endif
                   
                @endif
            </td>
            <td>
                @if ($salary_item->allowance)
                    {{$salary_item->allowance ? $salary_item->allowance->name : ""}}
                @elseif($salary_item->deduction)
                    {{$salary_item->deduction ? $salary_item->deduction->name : ""}}
                @elseif($salary_item->recovery)
                    {{$salary_item->recovery ? $salary_item->recovery->name : ""}}
                @elseif($salary_item->loan)
                {{ $salary_item->loan->loan_number }} {{ $salary_item->loan->loan_type ? $salary_item->loan->loan_type->name : "" }}, Total: {{$salary_item->loan->currency ? $salary_item->loan->currency->name : ""}} {{$salary_item->loan->currency ? $salary_item->loan->currency->symbol : ""}}{{number_format($salary_item->loan->total ? $salary_item->loan->total : 0,2)}} Monthly Installments: {{$salary_item->loan->currency ? $salary_item->loan->currency->name : ""}} {{$salary_item->loan->currency ? $salary_item->loan->currency->symbol : ""}}{{number_format($salary_item->loan->payment_per_month ? $salary_item->loan->payment_per_month : 0,2)}}
                @endif
            </td>
            <td>{{$salary_item->salary->currency->name}}</td>
            <td>
                @if ($salary_item->amount)
                  {{$salary_item->salary->currency->symbol}}{{number_format($salary_item->amount,2)}}        
                @endif
            </td>
           
        </tr>
          @endforeach
        </tbody>
      </table>

</div>
