<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>TTO Details</strong> </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">TTO#</th>
                                <td class="w-20 line-height-35">{{$tto->tto_number}} </td>
                            </tr>
                           <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$tto->user ? $tto->user->name : ""}} {{$tto->user ? $tto->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Customer</th>
                                <td class="w-20 line-height-35"> {{$transport_order->customer ? $transport_order->customer->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Consignee</th>
                                <td class="w-20 line-height-35"> {{$transport_order->consignee ? $transport_order->consignee->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo</th>
                                <td class="w-20 line-height-35">
                                    {{$transport_order->cargo ? $transport_order->cargo->name : ""}} <br>

                                    <small class="text-muted">
                                        <strong>Weight: </strong>{{$tto->allocated_weight ? $tto->allocated_weight."t" : ""}} 

                                        @if ($transport_order->cargo?->cargo_type == "Solid")
                                            <br>
                                            <strong>Qty: </strong>{{$tto->quantity}} {{$tto->units_of_measure?->name}} {{$tto->units_of_measure?->abbreviation ? "(".$tto->units_of_measure?->abbreviation.")" : ""}} <br>
                                        @else
                                            <br>
                                            <strong>Litreage: </strong>{{$tto->litreage}} {{$tto->units_of_measure?->name}} {{$tto->units_of_measure?->abbreviation ? "(".$tto->units_of_measure?->abbreviation.")" : ""}} <br>
                                        @endif
                                        
                                        <strong>AddInfo:</strong> {{$transport_order->cargo_details}} <br>
                                    </small></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">From</th>
                                <td class="w-20 line-height-35">
                                    @if($transport_order->fromDestination)
                                        {{ $transport_order->fromDestination->country?->name }} {{ $transport_order->fromDestination->city }}
                                    @endif
                                    {{ $transport_order->loading_point?->name }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">To</th>
                                <td class="w-20 line-height-35">
                                    @if($transport_order->toDestination)
                                        {{ $transport_order->toDestination->country?->name }} {{ $transport_order->toDestination->city }}
                                    @endif
                                    {{ $transport_order->offloading_point?->name }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35"> {{$transport_order->currency ? $transport_order->currency->name : ""}}</td>
                            </tr>
                            @php
                                $showFreight = !$company->rates_managed_by_finance
                                    || in_array('Finance', $department_names)
                                    || in_array('Super Admin', $role_names);
                            @endphp
                            @if($showFreight)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Freight</th>
                                    <td class="w-20 line-height-35">
                                    
                                            {{ $tto->currency?->name }} {{ $tto->currency?->symbol }}
                                            {{ number_format(
                                                    (float) (is_numeric($tto->allocated_freight)
                                                        ? $tto->freight
                                                        : preg_replace('/[^\d\.\-]/', '', (string) ($tto->allocated_freight ?? 0))
                                                    ),
                                                    2
                                                ) }}
                                    </td>
                                </tr>
                            @endif
                           
    
                        </tbody>
                    </table>
                   
                </div>
               
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                        </div>
                    </div>
                    </div>

                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
</div>
