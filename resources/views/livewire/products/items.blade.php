<div>
    <table id="itemsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
        <th class="th-sm">Product
        </th>
        <th class="th-sm">Part/Serial#
        </th>
        <th class="th-sm">Location
        </th>
        <th class="th-sm">Item Contents
        </th>
        <th class="th-sm">Purchase Date
        </th>
        <th class="th-sm">Currency
        </th>
        <th class="th-sm">Amt
        </th>
        <th class="th-sm">Tax Amt
        </th>
        <th class="th-sm">Total
        </th>
        <th class="th-sm">Status
        </th>
          </tr>
        </thead>

        <tbody>
            @foreach ($items as $item)
        
          <tr>
            <td>{{$item->product->brand ? $item->product->brand->name : ""}} {{$item->product ? $item->product->name : ""}}</td>
            <td>{{$item->serial_number ? "SN#: ".$item->serial_number : ""}} {{$item->product->identification_number ? "PN#: ".$item->product->identification_number : ""}}</td>
            <td>
                @if ($item->store)
                        store: {{$item->store ? $item->store->name : ""}}
                        <br>
                @endif
                @if ($item->product->category)
                Category: {{$item->product->category ? $item->product->category->name : ""}}  {{$item->product->category_value ? $item->product->category_value->name : ""}} 
                @endif
                @if ($item->rack)
                        Rack: {{$item->rack ? $item->rack->name : ""}} {{$item->rack ? $item->rack->rack_number : ""}} 
                        <br>
                @endif
                @if ($item->bin)
                        Bin: {{$item->bin ? $item->bin->name : ""}} {{$item->bin ? $item->bin->bin_number : ""}} 
                        <br>
                @endif
                
            </td>
            <td>{{$item->weight}} {{$item->measurement}} {{$item->balance ? "Bal: ".$item->balance." ".$item->measurement : ""}}</td>
            <td>{{$item->purchase_date}}</td>
            <td>{{$item->currency ? $item->currency->name : ""}}</td>
            <td>
                @if ($item->amount)
                    {{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->amount,2)}}  
                @endif
            </td>
            <td>
                
                    {{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->tax_amount ? $item->tax_amount : 0,2)}}  
                
            </td>
            <td>
                @if ($item->subtotal_incl)
                    {{$item->currency ? $item->currency->symbol : ""}}{{number_format($item->subtotal_incl,2)}}  
                @endif
            </td>
            <td><span class="badge bg-{{$item->status == 1 ? "success" : "danger"}}">{{$item->status == 1 ? "Instore" : "Out Of stock"}}</span></td>
        
          </tr>
         
          @endforeach

        </tbody>


      </table>

    
</div>
