<div>

    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>Transport Order Details</strong> </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">TransportOrder#</th>
                                <td class="w-20 line-height-35">{{$transport_order->transport_order_number}} </td>
                            </tr>
                           <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$transport_order->user ? $transport_order->user->name : ""}} {{$transport_order->user ? $transport_order->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Customer</th>
                                <td class="w-20 line-height-35"> {{$transport_order->customer ? $transport_order->customer->name : ""}}</td>
                            </tr>
                            @if ($transport_order->quotation)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Quotation</th>
                                    <td class="w-20 line-height-35"> <a href="{{route('quotations.show',$transport_order->quotation?->id)}}" style="color:blue" target="_blank">{{$transport_order->quotation ? $transport_order->quotation->quotation_number : ""}}</a></td>
                                </tr>
                            @endif
                            
                            <tr>
                                <th class="w-10 text-center line-height-35">Consignee</th>
                                <td class="w-20 line-height-35"> {{$transport_order->consignee ? $transport_order->consignee->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Cargo</th>
                                <td class="w-20 line-height-35">
                                    {{$transport_order->cargo ? $transport_order->cargo->name : ""}} <br>

                                    <small class="text-muted">
                                        <strong>Weight: </strong>{{$transport_order->weight ? $transport_order->weight."t" : ""}} 

                                        @if ($transport_order->cargo?->cargo_type == "Solid")
                                            <br>
                                            <strong>Qty: </strong>{{$transport_order->quantity}} {{$transport_order->units_of_measure?->name}} {{$transport_order->units_of_measure?->abbreviation ? "(".$transport_order->units_of_measure?->abbreviation.")" : ""}} <br>
                                        @else
                                            <br>
                                            <strong>Litreage: </strong>{{$transport_order->litreage}} {{$transport_order->units_of_measure?->name}} {{$transport_order->units_of_measure?->abbreviation ? "(".$transport_order->units_of_measure?->abbreviation.")" : ""}} <br>
                                        @endif
                                        
                                        <strong>AddInfo:</strong> {{$transport_order->cargo_details}} <br>
                                    </small></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">From</th>
                                <td class="w-20 line-height-35">
                                   @php
                                                        $fromRoutes = collect();

                                                        if ($transport_order->trip_origins && $transport_order->trip_origins->count()) {
                                                            $fromRoutes = $transport_order->trip_origins
                                                                ->map(function ($trip_origin) {
                                                                    $from = $trip_origin->destination
                                                                        ? trim(($trip_origin->destination->country?->name ?? '') . ' ' . ($trip_origin->destination->city ?? ''))
                                                                        : null;

                                                                    $loadingPoint = $trip_origin->loading_point?->name;

                                                                    return [
                                                                        'label' => trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -'),
                                                                        'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $from = $transport_order->fromDestination
                                                                ? trim(($transport_order->fromDestination->country?->name ?? '') . ' ' . ($transport_order->fromDestination->city ?? ''))
                                                                : null;

                                                            $loadingPoint = $transport_order->loading_point?->name;

                                                            $label = trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -');

                                                            if (!empty($label)) {
                                                                $fromRoutes = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($fromRoutes as $route)
                                                        {{ $route['label'] }}

                                                        @if(!$loop->last && $fromRoutes->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">To</th>
                                <td class="w-20 line-height-35">
                                    @php
                                                        $toRoutes = collect();

                                                        if ($transport_order->trip_destinations && $transport_order->trip_destinations->count()) {
                                                            $toRoutes = $transport_order->trip_destinations
                                                                ->map(function ($trip_destination) {
                                                                    $to = $trip_destination->destination
                                                                        ? trim(($trip_destination->destination->country?->name ?? '') . ' ' . ($trip_destination->destination->city ?? ''))
                                                                        : null;

                                                                    $offloadingPoint = $trip_destination->offloading_point?->name;

                                                                    return [
                                                                        'label' => trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -'),
                                                                        'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();
                                                        } else {
                                                            $to = $transport_order->toDestination
                                                                ? trim(($transport_order->toDestination->country?->name ?? '') . ' ' . ($transport_order->toDestination->city ?? ''))
                                                                : null;

                                                            $offloadingPoint = $transport_order->offloading_point?->name;

                                                            $label = trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -');

                                                            if (!empty($label)) {
                                                                $toRoutes = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($toRoutes as $route)
                                                        {{ $route['label'] }}

                                                        @if(!$loop->last && $toRoutes->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
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
                                    
                                            {{ $transport_order->currency?->name }} {{ $transport_order->currency?->symbol }}
                                            {{ number_format(
                                                    (float) (is_numeric($transport_order->freight)
                                                        ? $transport_order->freight
                                                        : preg_replace('/[^\d\.\-]/', '', (string) ($transport_order->freight ?? 0))
                                                    ),
                                                    2
                                                ) }}
                                    </td>
                                </tr>
                            @endif
                           
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"> <span class="badge bg-{{$transport_order->status == 1 ? "warning" : "success"}}">{{$transport_order->status}}</span>        </td>
                            </tr>
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
