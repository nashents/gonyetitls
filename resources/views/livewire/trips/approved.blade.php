<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
        <section class="section">
            <x-loading/>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <div>
                                    @include('includes.messages')
                                </div>
                               
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                       
                                <div class="panel-title">
                                  <div class="row">
                                        <div class="col-lg-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    Filter By
                                                </span>
                                                <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                                    <option value="created_at">Trip Created At</option>
                                                    <option value="offloaded_date">Trip Offloading Date</option>
                                                    <option value="start_date">Trip Start Date</option>
                                                </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    From
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    To
                                                </span>
                                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-lg-3" >
                                            <div class="input-group">
                                               <a href="#" wire:click.prevent="clearFilters()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-refresh"></i>CLEAR FILTERS</a>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                              
                                </div>
                                <br>

                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search Using: Trip#,Transporter,Customer,Reg#,CreatedBy,POD#,StartDate...">
                                    </div>
                                </div>
                                   
                                @php
                                    $showFreight = !$company->rates_managed_by_finance
                                        || in_array('Finance', $department_names)
                                        || in_array('Super Admin', $role_names);

                                    $dtPattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                                    $formatDate = function ($value) use ($dtPattern) {
                                        if (!$value) return null;

                                        // datetime-local like 2026-01-09T06:30
                                        if (is_string($value) && preg_match($dtPattern, $value)) {
                                            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $value)->format('d M Y g:i A');
                                        }

                                        // already a Carbon/date string
                                        try {
                                            return \Carbon\Carbon::parse($value)->format('d M Y g:i A');
                                        } catch (\Throwable $e) {
                                            return $value; // fallback
                                        }
                                    };

                                    $statusMap = [
                                        'Offloaded'        => ['row' => '#E8F5E9', 'border' => '#2E7D32', 'cell' => 'table-success', 'badge' => 'success'],
                                        'Scheduled'        => ['row' => '#FFF3E0', 'border' => '#E65100', 'cell' => 'table-warning', 'badge' => 'warning'],
                                        'Loading Point'    => ['row' => '#F5F5F5', 'border' => '#616161', 'cell' => 'table-secondary', 'badge' => 'secondary'],
                                        'Loaded'           => ['row' => '#E1F5FE', 'border' => '#0277BD', 'cell' => 'table-info', 'badge' => 'info'],
                                        'Started'          => ['row' => '#E3F2FD', 'border' => '#1565C0', 'cell' => 'table-primary', 'badge' => 'primary'],
                                        'InTransit'        => ['row' => '#E3F2FD', 'border' => '#1565C0', 'cell' => 'table-primary', 'badge' => 'primary'],
                                        'Offloading Point' => ['row' => '#E8F0FE', 'border' => '#1E88E5', 'cell' => 'table-info', 'badge' => 'info'],
                                        'OnHold'           => ['row' => '#FFEBEE', 'border' => '#C62828', 'cell' => 'table-danger', 'badge' => 'danger'],
                                        'Cancelled'        => ['row' => '#EFEBE9', 'border' => '#8D6E63', 'cell' => 'table-light', 'badge' => 'light'],
                                    ];
                                @endphp
                              
                                {{-- <div class="table-responsive"> --}}
                                    <table class="table  table-striped table-bordered table-sm table-responsive sortable" cellspacing="0" width="100%" style=" width:100%; height:100%;  font-size: 13px;">
                                        <thead>
                                            <tr>
                                                <th>Trip#<hr style="margin-top:2px; margin-bottom:2px">Type</th>
                                                <th>Departure <hr style="margin-top:2px; margin-bottom:2px">Offloaded</th>
                                                <th>Customer (Cargo)</th>
                                                <th>Transporter<hr style="margin-top:2px; margin-bottom:2px">Driver</th>
                                                <th>Horse/Vehicle<hr style="margin-top:2px; margin-bottom:2px">Trailer</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Status</th>
                                                @if($showFreight)
                                                    <th>Freight</th>
                                                @endif
                                                <th>Invoice<hr style="margin-top:2px; margin-bottom:2px">POD</th>
                                                <th>Auth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($trips as $trip)
                                            @php
                                                $s = $statusMap[$trip->trip_status] ?? ['row' => null, 'border' => null, 'cell' => '', 'badge' => 'secondary'];
                                                $offloadedDate = $trip->delivery_note?->offloaded_date;
                                                $pod = $trip->pod;
                                                $proofOfDelivery = App\Models\TripDocument::where('trip_id', $trip->id)->where('title', 'POD')->first();
                                            @endphp

                                            <tr @if($s['row']) style="background-color: {{ $s['row'] }}; border-left: 6px solid {{ $s['border'] }};" @endif>
                                                <td>
                                                    <strong>{{ $trip->trip_number }}@if($trip->trip_ref)/{{ $trip->trip_ref }}@endif</strong>
                                                    <br>
                                                    <small>
                                                        <strong>CreatedBy:</strong>  {{ $trip->user?->name }} {{ $trip->user?->surname }} <br>
                                                        <strong>CreatedOn:</strong> {{ $trip->created_at }}
                                                    </small>
                                                    <hr class="my-1">
                                                    {{ $trip->trip_type?->name }}
                                                    @if($trip->haulage_type)
                                                        <small><strong>{{ $trip->haulage_type == "short_haul" ? "(Short Haul)" : "(Long Haul)"}}</strong></small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $formatDate($trip->start_date) }}
                                                    <hr class="my-1">
                                                    {{ $formatDate($offloadedDate) }}
                                                </td>
                                                <td>
                                                    @php
                                                        $items = collect();

                                                        if ($trip->trip_transport_orders && $trip->trip_transport_orders->count()) {

                                                            $items = $trip->trip_transport_orders
                                                                ->map(function ($trip_transport_order) {
                                                                    $to = $trip_transport_order->transport_order;

                                                                    $customer = ucfirst($to->customer?->name ?? '');
                                                                    $cargo    = ucfirst($to->cargo?->name ?? '');

                                                                    return [
                                                                        'label' => trim($customer . ' ' . $cargo),
                                                                        'key'   => md5(($to->customer_id ?? '') . '|' . ($to->cargo_id ?? '')),
                                                                    ];
                                                                })
                                                                ->filter(fn ($item) => !empty($item['label']))
                                                                ->unique('key')
                                                                ->values();

                                                        } else {

                                                            // fallback (old structure)
                                                            $customer = ucfirst($trip->customer?->name ?? '');
                                                            $cargo    = ucfirst($trip->cargo?->name ?? '');

                                                            $label = trim($customer . ' ' . $cargo);

                                                            if (!empty($label)) {
                                                                $items = collect([
                                                                    [
                                                                        'label' => $label,
                                                                        'key'   => md5(($trip->customer_id ?? '') . '|' . ($trip->cargo_id ?? '')),
                                                                    ]
                                                                ]);
                                                            }
                                                        }
                                                    @endphp

                                                    @forelse($items as $item)
                                                        {{ $item['label'] }}

                                                        @if(!$loop->last && $items->count() > 1)
                                                            <hr class="my-1">
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>

                                                <td>
                                                    {{ ucfirst($trip->transporter?->name ?? '') }}
                                                    @if($trip->driver)
                                                        <hr class="my-1">
                                                        {{ $trip->driver?->employee?->name }} {{ $trip->driver?->employee?->surname }}
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($trip->horse)
                                                        Horse | {{ $trip->horse->registration_number }} {{ $trip->horse->fleet_number ? "({$trip->horse->fleet_number})" : "" }}
                                                    @elseif($trip->vehicle)
                                                        Vehicle | {{ $trip->vehicle->registration_number }} {{ $trip->vehicle->fleet_number ? "({$trip->vehicle->fleet_number})" : "" }}
                                                    @endif

                                                    @if($trip->trailers?->count())
                                                        <hr class="my-1">
                                                        @foreach($trip->trailers as $trailer)
                                                            {{ $trailer->registration_number }} {{ $trailer->fleet_number ? "({$trailer->fleet_number})" : "" }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @endif
                                                </td>

                                                  <td>
                                                    @php
                                                        $fromRoutes = collect();

                                                        if ($trip->trip_origins && $trip->trip_origins->count()) {
                                                            $fromRoutes = $trip->trip_origins
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
                                                            $from = $trip->fromDestination
                                                                ? trim(($trip->fromDestination->country?->name ?? '') . ' ' . ($trip->fromDestination->city ?? ''))
                                                                : null;

                                                            $loadingPoint = $trip->loading_point?->name;

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

                                                <td>
                                                    @php
                                                        $toRoutes = collect();
                                                        if ($trip->trip_destinations && $trip->trip_destinations->count()) {
                                                            $toRoutes = $trip->trip_destinations
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
                                                            $to = $trip->toDestination
                                                                ? trim(($trip->toDestination->country?->name ?? '') . ' ' . ($trip->toDestination->city ?? ''))
                                                                : null;

                                                            $offloadingPoint = $trip->offloading_point?->name;

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
                                                <td class="{{ $s['cell'] }}" style="padding: 5px;">
                                                    <span class="label label-{{ $s['badge'] }} label-wide">
                                                        {{ $trip->trip_status }}
                                                        @if($trip->authorization === "approved")
                                                            <a href="#" wire:click.prevent="$emit('openTripStatusModal', {{ $trip->id }})" class="ms-1">
                                                                <i class="fa fa-edit" style="color:black"></i>
                                                            </a>
                                                        @endif
                                                    </span>
                                                </td>

                                                @if($showFreight)
                                                    <td>
                                                        {{ $trip->currency?->name }} {{ $trip->currency?->symbol }}
                                                        {{ number_format(
                                                                (float) (is_numeric($trip->freight)
                                                                    ? $trip->freight
                                                                    : preg_replace('/[^\d\.\-]/', '', (string) ($trip->freight ?? 0))
                                                                ),
                                                                2
                                                            ) }}

                                                        @if($trip->currency_id && (int) $trip->currency_id !== (int) $company->currency_id)
                                                            <hr class="my-1">
                                                            <small>
                                                                {{ $company->currency?->name }} {{ $company->currency?->symbol }}
                                                                {{ number_format(
                                                                        (float) (is_numeric($trip->exchange_customer_freight)
                                                                            ? $trip->exchange_customer_freight
                                                                            : preg_replace('/[^\d\.\-]/', '', (string) ($trip->exchange_customer_freight ?? 0))
                                                                        ),
                                                                        2
                                                                    ) }}
                                                                <br>
                                                                <strong>Rate:</strong>
                                                                {{ is_numeric($trip->exchange_rate)
                                                                    ? number_format((float) $trip->exchange_rate, 4)
                                                                    : ($trip->exchange_rate ?: '-') }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                @endif

                                                <td>
                                                    @php $tripInvoices = $trip->invoice_documents; @endphp
                                                    @if($tripInvoices?->count())
                                                        <span class="label label-success">issued</span>
                                                        <small>
                                                            <strong>Invoice#(s):</strong>
                                                            @foreach($tripInvoices as $invoice)
                                                                {{ $invoice->invoice_number }}@if(!$loop->last),@endif
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        <span class="label label-warning">pending</span>
                                                    @endif

                                                    <hr class="my-1">
                                                    @if (isset($pod))
                                                        <span class="label label-{{ $pod ? 'success' : 'warning' }}">
                                                            {{ $pod ? "Submitted On: {$pod->date}" : "pending" }}
                                                        </span>
                                                       <div class="text-center"> {{ $pod->document_number ? "POD#: ".$pod->document_number : "" }}</div>
                                                    @elseif (isset($proofOfDelivery))
                                                        <span class="label label-{{ $proofOfDelivery ? 'success' : 'warning' }}">
                                                            {{ $proofOfDelivery ? "Submitted On: {$proofOfDelivery->date}" : "pending" }}
                                                        </span>
                                                        <div class="text-center"> {{ $proofOfDelivery->document_number ? "POD#: ".$proofOfDelivery->document_number : "" }}</div>
                                                    @else
                                                        <span class="label label-warning">
                                                            pending
                                                        </span>
                                                    @endif
                                                   
                                                </td>

                                                <td>
                                                    @php
                                                        $authClass = $trip->authorization === 'approved' ? 'success' : ($trip->authorization === 'rejected' ? 'danger' : 'warning');
                                                        $authText  = $trip->authorization === 'approved' ? 'approved' : ($trip->authorization === 'rejected' ? 'rejected' : 'pending');
                                                    @endphp
                                                    <span class="label label-{{ $authClass }}">{{ $authText }}</span>

                                                    @if($trip->authorization_date)
                                                        <br><small><strong style="background-color: orange">AuthorizedOn: {{ $trip->authorization_date }}</strong></small>
                                                    @endif
                                                    @if($trip->authorized_by_id)
                                                        <br><small><strong style="background-color: orange">AuthorizedBy: {{ $this->getAuthorizer($trip->authorized_by_id) }}</strong></small>
                                                    @endif
                                                    @if($trip->reason)
                                                        <br><small><strong style="background-color: orange">Comments: {{ $trip->reason }}</strong></small>
                                                    @endif
                                                </td>

                                               <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted" style="padding: 12px; font-size: 17px;">
                                                    No Approved Trips Found ....
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                {{-- </div> --}}
                 
                                    {{ $trips->links() }}
                            

                                <!-- /.col-md-12 -->
                            </div>
                        </div>
                    </div>


                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->
        </section>
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-gavel"></i> Authorize Trip<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Authorize</label>
                        <select class="form-control" wire:model.debounce.300ms="authorize" required>
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                            @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="reason">Comments</label>
                           <textarea wire:model.debounce.300="comments" class="form-control" cols="30" rows="5"></textarea>
                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>

        @livewire('trips.trip-status-manager')

    </div>
