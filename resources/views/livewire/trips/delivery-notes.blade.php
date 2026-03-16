<div>
    <style>
        th {
            width: 30%; /* Adjust width as needed */
            /* text-align: left; */
            padding: 10px;
            /* border: 1px solid #ddd; */
        }
    </style>
    <div class="col-md-12 p-n">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>Offloading Details</h5>
                    </div>
                </div>
               
                <table class="table table-condensed mb-0 border-top table-striped">
                    <caption>Trip Loading / Offloading Details</caption>
                    <tbody>
                        <tr>
                            <th style="width: 30%; padding-left:20px;" scope="row"> Trip Status</th>
                            @if ($trip->trip_status == "Offloaded")
                                <td class="table-success">
                                    <span class="label label-success label-wide " style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "Scheduled")
                                <td class="table-warning" >
                                    <span class="label label-warning label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "Loading Point")
                                <td class="table-default" >
                                    <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                    @endif
                                </td>
                                @elseif($trip->trip_status == "Started")
                                <td class="table-default" >
                                    <span class="label label-primary label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                    @endif
                                </td>
                                @elseif($trip->trip_status == "Loaded")
                                <td class="table-info">
                                    <span class="label label-info label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "InTransit")
                                <td class="table-primary">
                                    <span class="label label-primary label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "OnHold")
                                <td class="table-danger">
                                    <span class="label label-danger label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "Offloading Point")
                                <td class="table-default">
                                    <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @elseif($trip->trip_status == "Cancelled")
                                <td class="table-default">
                                    <span class="label label-default label-wide" style="margin-right:5px;">{{$trip->trip_status}}@if($trip->authorization == "approved")
                                        <a href="#" wire:click="status({{$trip->id}})" style="margin-left:2px" ><i class="fa fa-edit" style="color:black"></i></a>
                                    @endif</span>
                                    @if ($trip->trip_status_date)
                                   
                                    @if ((preg_match($pattern, $trip->trip_status_date)) )
                                        On {{ \Carbon\Carbon::parse($trip->trip_status_date)->format('d M Y g:i A')}}
                                    @else
                                        On {{$trip->trip_status_date}}
                                    @endif  
                                @endif
                                </td>
                                @endif

                               
                              
                        </tr>
                        @if ($trip->trip_status_description)
                        <tr >
                            <th style="width: 30%; padding-left:20px;" scope="row"> Trip Status Description</th>
                            <td>
                                {{$trip->trip_status_description}}
                            </td>
                        </tr>
                        @endif

                        @if ($delivery_note)
                            
                      
                            <tr>
                                <th style="width: 30%; padding-left:20px;" >Loading Date</th>
                                <td>
                                    @if (isset($delivery_note->loaded_date))
                                    {{$delivery_note->loaded_date}}
                                    @else
                                    No Loading Date Recorded
                                    @endif
                                </td>
                            </tr>
                            @if (isset($cargo_type) && $cargo_type === "Solid")
                                <tr>
                                    <th style="width: 30%; padding-left:20px;" style="width: 30%; padding:10px;">Loaded Quantity</th>
                                    <td>
                                        @if (isset($delivery_note->loaded_quantity))
                                        {{$delivery_note->loaded_quantity}}  {{$delivery_note->measurement}}
                                        @else
                                        No Loaded Quantity Recorded
                                        @endif
                                    </td>
                                </tr>
                            @elseif (isset($cargo_type) && $cargo_type === "Liquid")
                                <tr>
                                    <th style="width: 30%; padding-left:20px;" style="width: 30%; padding:10px;"> Loaded Litreage @ 20 Degrees</th>
                                    <td>
                                        @if (is_numeric($delivery_note->loaded_litreage_at_20))
                                            {{number_format($delivery_note->loaded_litreage_at_20,2)}}  {{$delivery_note->measurement}}
                                        @else
                                            No Loaded Litreage @ 20 Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 30%; padding-left:20px;" style="width: 30%; padding:10px;">Loaded Litreage @ Ambient</th>
                                    <td>
                                        @if (is_numeric($delivery_note->loaded_litreage))
                                        {{number_format($delivery_note->loaded_litreage,2)}} {{$delivery_note->measurement}}
                                        @else
                                        No Loaded Litreage @ Ambient Recorded
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Loaded Weight </th>
                                <td>
                                    @if (isset($delivery_note->loaded_weight))
                                    {{$delivery_note->loaded_weight ? $delivery_note->loaded_weight." tons" : ""}}
                                    @else
                                    No Loaded Weight Recorded
                                    @endif
                                </td>
                            </tr>
                            @if ($this->company->rates_managed_by_finance == 1)
                                @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">Customer Rate @ Loading </th>
                                        <td>
                                            @if (isset($delivery_note->loaded_rate))
                                            {{ $trip->currency ? $trip->currency->name : "" }}  {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_rate}}
                                            @else
                                                No Customer Rate @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">Customer Freight @ Loading</th>
                                        <td>
                                            @if (isset($delivery_note->loaded_freight))
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_freight}}
                                                @else
                                                No Customer Freight @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($trip->transporter_agreement == TRUE)
                                        <tr>
                                            <th style="width: 30%; padding-left:20px;">Transporter Rate @ Loading</th>
                                            <td>
                                                @if (isset($delivery_note->loaded_rate))
                                                {{ $trip->currency ? $trip->currency->name : "" }}  {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_rate}}
                                                    @else
                                                    No Transporter Rate @ Loading
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; padding-left:20px;">Transporter Freight @ Loading</th>
                                            <td>
                                                @if (isset($delivery_note->loaded_freight))
                                                {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_freight}}
                                                    @else
                                                    No Transporter Freight @ Loading
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @else 
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">Customer Rate @ Loading </th>
                                    <td>
                                        @if (isset($delivery_note->loaded_rate))
                                        {{ $trip->currency ? $trip->currency->name : "" }}  {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_rate}}
                                        @else
                                            No Customer Rate @ Loading
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">Customer Freight @ Loading</th>
                                    <td>
                                        @if (isset($delivery_note->loaded_freight))
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_freight}}
                                            @else
                                            No Customer Freight @ Loading
                                        @endif
                                    </td>
                                </tr>
                                @if ($trip->transporter_agreement == TRUE)
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">Transporter Rate @ Loading</th>
                                        <td>
                                            @if (isset($delivery_note->loaded_rate))
                                            {{ $trip->currency ? $trip->currency->name : "" }}  {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_rate}}
                                                @else
                                                No Transporter Rate @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">Transporter Freight @ Loading</th>
                                        <td>
                                            @if (isset($delivery_note->loaded_freight))
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->loaded_freight}}
                                                @else
                                                No Transporter Freight @ Loading
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Offloading Date</th>
                                <td>
                                    @if (isset($delivery_note->offloaded_date))
                                    {{$delivery_note->offloaded_date}}
                                    @else
                                    No Offloading Date Recorded
                                    @endif
                                </td>
                            </tr>
                            @if (isset($cargo_type) && $cargo_type === "Solid")
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">Offloaded Quantity </th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_quantity))
                                        {{$delivery_note->offloaded_quantity}}  {{$delivery_note->measurement}}
                                        @else
                                        No Offloaded Quantity Recorded
                                    @endif
                                    </td>
                                </tr>
                            @elseif(isset($cargo_type) && $cargo_type === "Liquid")
                                <tr>
                                    <th style="width: 30%; padding-left:20px;"> Offloaded Litreage @ Ambient</th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_litreage))
                                        {{$delivery_note->offloaded_litreage}}  {{$delivery_note->measurement}}
                                        @else
                                        No Offloaded Litreage @ Ambient Recorded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 30%; padding-left:20px;"> Offloaded Litreage @ 20 Degrees</th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_litreage_at_20))
                                        {{$delivery_note->offloaded_litreage_at_20}}  {{$delivery_note->measurement}}
                                        @else
                                        No Offloaded Litreage @ 20 Recorded
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Offloaded Weight</th>
                                <td>
                                    @if (isset($delivery_note->offloaded_weight))
                                    {{$delivery_note->offloaded_weight ? $delivery_note->offloaded_weight." tons" : "" }}
                                    @else
                                    No Offloaded Weight Recorded
                                    @endif
                                </td>
                            </tr>
                            @if ($this->company->rates_managed_by_finance == 1)
                                @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">   Customer Rate @ Offloading</th>
                                        <td>
                                            @if (isset($delivery_note->offloaded_rate))
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->offloaded_rate}}
                                                @else
                                                No Customer Rate @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">  Customer Freight @ Offloading</th>
                                        <td>
                                            @if (isset($delivery_note->offloaded_freight))
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->offloaded_freight}}
                                                @else
                                                No Customer Freight @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($trip->transporter_agreement == TRUE)
                                        <tr>
                                            <th style="width: 30%; padding-left:20px;"> Transporter Rate @ Offloading</th>
                                            <td>
                                                @if (isset($delivery_note->transporter_offloaded_rate))
                                                {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->transporter_offloaded_rate}}
                                                @else
                                                    No Transporter Rate @ Offloading
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%; padding-left:20px;">Transporter Freight @ Offloading</th>
                                            <td>
                                                @if (isset($delivery_note->transporter_offloaded_freight))
                                                    {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->transporter_offloaded_freight}}
                                                @else
                                                    No Transporter Freight @ Offloading
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @else
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">   Customer Rate @ Offloading</th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_rate))
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->offloaded_rate}}
                                            @else
                                            No Customer Rate @ Offloading
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">   Customer Rate @ Offloading</th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_rate))
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->offloaded_rate}}
                                            @else
                                            No Customer Rate @ Offloading
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 30%; padding-left:20px;">  Customer Freight @ Offloading</th>
                                    <td>
                                        @if (isset($delivery_note->offloaded_freight))
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->offloaded_freight}}
                                            @else
                                            No Customer Freight @ Offloading
                                        @endif
                                    </td>
                                </tr>
                                @if ($trip->transporter_agreement == TRUE)
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;"> Transporter Rate @ Offloading</th>
                                        <td>
                                            @if (isset($delivery_note->transporter_offloaded_rate))
                                            {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->transporter_offloaded_rate}}
                                            @else
                                                No Transporter Rate @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">Transporter Freight @ Offloading</th>
                                        <td>
                                            @if (isset($delivery_note->transporter_offloaded_freight))
                                                {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{$delivery_note->transporter_offloaded_freight}}
                                            @else
                                                No Transporter Freight @ Offloading
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Comments</th>
                                <td>
                                    @if (isset($delivery_note->comments))
                                    {{ $delivery_note->comments }}
                                    @else 
                                    No Comment Recorded
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <table class="table table-condensed mb-0 border-top table-striped">
                    <caption>Trip Loss Details</caption>
                    <tbody>
                        <tr>
                            <th style="width: 30%; padding-left:20px;">Weight Loss</th>
                            <td>
                                @if ($weight_loss)
                                    @if ($weight_loss > 0)
                                        <span class="label label-danger">
                                        {{ abs($weight_loss) }} Ton(s)
                                        </span>
                                    @elseif ($weight_loss === 0)
                                        <span class="label label-success">
                                            {{ abs($weight_loss) }} Ton(s)
                                        </span>
                                    @elseif ($weight_loss < 0)
                                        <span class="label label-success">
                                            {{ abs($weight_loss) }} Tons
                                        </span>
                                    @endif
                                @else
                                    <span >
                                        No Weight Loss Recorded
                                    </span>
                                @endif
                           
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; padding-left:20px;"> Allowable Weight Loss</th>
                            <td>
                                @if ($trip->allowable_loss_weight)
                                <span class="label label-success">
                                    {{ $trip->allowable_loss_weight ? $trip->allowable_loss_weight."Tons" : "" }}
                                </span>
                                @endif
                               
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 30%; padding-left:20px;">  Chargeable Weight Loss</th>
                            <td>
                                @if ($chargeable_weight_loss)
                                <span class="label label-danger">
                                    {{  $chargeable_weight_loss ?  $chargeable_weight_loss." Tons" : "" }}
                                </span>
                                @endif
                               
                            </td>
                        </tr>
                        @if (isset($cargo_type) && $cargo_type === "Solid")
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Quantity Loss</th>
                                <td>
                                    @if ($quantity_loss)
                                        @if ($quantity_loss > 0)
                                            <div class="label label-danger" >
                                            {{ abs($quantity_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($quantity_loss == 0)
                                            <div class="label label-default">
                                                {{ abs($quantity_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($quantity_loss < 0)
                                            <div class="label label-success">
                                                {{ abs($quantity_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @endif
                                    @else
                                        No Quantity Loss Recorded
                                    @endif
                                
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%; padding-left:20px;">  Allowable Quantity Loss</th>
                                <td>
                                    @if ($trip->allowable_loss_quantity)
                                        <span class="label label-success">
                                        {{ $trip->allowable_loss_quantity ? $trip->allowable_loss_quantity : "" }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%; padding-left:20px;">  Chargeable Quantity Loss</th>
                                <td>
                                    @if ($chargeable_quantity_loss)
                                    <span class="label label-success">
                                        {{  $chargeable_quantity_loss ?  $chargeable_quantity_loss : "" }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                        @elseif(isset($cargo_type) && $cargo_type === "Liquid")
                            <tr>
                                <th style="width: 30%; padding-left:20px;"> Litreage Loss @ Ambient Temperature</th>
                                <td>
                                    @if ($litreage_loss)
                                        @if ($litreage_loss > 0)
                                            <div class="label label-danger" >
                                            {{ abs($litreage_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($litreage_loss == 0)
                                            <div class="label label-default">
                                                {{ abs($litreage_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($litreage_loss < 0)
                                            <div class="label label-success">
                                                {{ abs($litreage_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @endif
                                    @else
                                        No Litreage @ Ambient Temperature Loss Recorded
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%; padding-left:20px;">Litreage Loss @ 20 Degrees</th>
                                <td>
                                    @if ($litreage_at_20_loss)
                                        @if ($litreage_at_20_loss > 0)
                                            <div class="label label-danger" >
                                            {{ abs($litreage_at_20_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($litreage_at_20_loss == 0)
                                            <div class="label label-default">
                                                {{ abs($litreage_at_20_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @elseif ($litreage_at_20_loss < 0)
                                            <div class="label label-success">
                                                {{ abs($litreage_at_20_loss) }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                            </div>
                                        @endif
                                    @else
                                        No Litreage @ 20 Degrees Loss Recorded
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%; padding-left:20px;">  Allowable Litreage Loss</th>
                                <td>
                                    @if ($trip->allowable_loss_litreage)
                                    <span class="label label-success">
                                        {{ $trip->allowable_loss_litreage ? $trip->allowable_loss_litreage : "" }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%; padding-left:20px;">  Chargeable Litreage Loss</th>
                                <td>
                                    @if ($chargeable_litreage_loss)
                                    <span class="label label-success">
                                        {{  $chargeable_litreage_loss ?  $chargeable_litreage_loss : "" }} {{$delivery_note ? $delivery_note->measurement : ""}}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if ($this->company->rates_managed_by_finance == 1)
                                @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                                    <tr>
                                        <th style="width: 30%; padding-left:20px;">   Freight Loss</th>
                                        <td>
                                            @if ($freight_loss)
                                                @if ($freight_loss > 0)
                                                    <div class="label label-danger" >
                                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                                    </div>
                                                @elseif ($freight_loss == 0)
                                                    <div class="label label-default">
                                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                                    </div>
                                                @elseif ($freight_loss < 0)
                                                    <div class="label label-success">
                                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                                    </div>
                                                @endif
                                            @else
                                            <div class="label label-danger">
                                                No Freight Loss Recorded
                                            </div>
                                            @endif
                                          
                                        </td>
                                    </tr>
                                @endif
                            @else
                            <tr>
                                <th style="width: 30%; padding-left:20px;">Freight Loss</th>
                                <td>
                                    @if (is_numeric($freight_loss) && $freight_loss > 0)
                                    <div class="label label-danger" >
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                    </div>
                                    @elseif (is_numeric($freight_loss) && $freight_loss == 0)
                                    <div class="label label-danger">
                                        {{ $trip->currency ? $trip->currency->name : "" }} {{ $trip->currency ? $trip->currency->symbol : "" }}{{ number_format($freight_loss,2) }}
                                    </div>
                                    @else
                                    <div class="label label-danger">
                                        No Freight Loss Recorded
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                    
                <!-- /.panel-body -->
            </div>
            <!-- /.panel -->

        </div>

    </div>

    @include('includes.trip_status')

</div>
