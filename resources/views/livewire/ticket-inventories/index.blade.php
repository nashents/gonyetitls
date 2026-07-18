<div>
                    
                    <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Quantities
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Amount
                            </th>
                           
                          </tr>
                        </thead>
                        @if (isset($ticket_inventories))
                        <tbody>
                           @forelse ($ticket_inventories as  $ticket_inventory)
                            <tr>
                                <td>
                                    @if ($ticket_inventory->inventory)
                                    <a href="{{route('inventories.show',$ticket_inventory->inventory->id)}}" target="_blank" style="color: blue">
                                        {{$ticket_inventory->inventory ? $ticket_inventory->inventory->inventory_number : ""}}
                                        {{$ticket_inventory->inventory->product ? $ticket_inventory->inventory->product->name : ""}}
                                        {{$ticket_inventory->inventory->serial_number ? "SN#: ".$ticket_inventory->inventory->serial_number : ""}} {{$ticket_inventory->inventory->part_number ? "PN#: ".$ticket_inventory->inventory->part_number : ""}}
                                    </a>
                                    @elseif ($ticket_inventory->tyre)
                                    @php
                                            $tyre = $ticket_inventory->tyre;
                                    @endphp
                                    <a href="{{route('tyres.show',$ticket_inventory->tyre->id)}}" target="_blank" style="color: blue">
                                        {{$tyre->tyre_number}} {{$tyre->product && $tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}
                                        @if ($tyre->subtotal_incl)
                                        {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}  
                                        @endif
                                    </a>
                                    @endif
                                 
                                </td>  
                                <td>
                                    @if ($ticket_inventory->inventory)
                                    {{$ticket_inventory->weight}} {{$ticket_inventory->measurement}}
                                    @elseif ($ticket_inventory->tyre)
                                    1
                                    @endif
                                </td>
                                <td>{{$ticket_inventory->currency ? $ticket_inventory->currency->name : ""}}</td>
                                <td>
                                    @if (isset($ticket_inventory->amount) && is_numeric($ticket_inventory->amount))
                                        {{ $ticket_inventory->currency ? $ticket_inventory->currency->symbol : "" }}{{number_format($ticket_inventory->amount,2)}}        
                                    @endif
                                </td>   
                                
                            </tr>
                           @empty
                            <tr>
                            <td colspan="5">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Dispatched Items Found ....
                                </div>
                            </td>
                            </tr> 
                            @endforelse
                        </tbody>
                        @else
                        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                     @endif
                    </table>

                    <nav class="text-center" style="float: right">
                        <ul class="pagination rounded-corners">
                            @if (isset($ticket_inventories))
                                @if ($ticket_inventories->count()>0)
                                    {{ $ticket_inventories->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav>

                
                    
</div>
