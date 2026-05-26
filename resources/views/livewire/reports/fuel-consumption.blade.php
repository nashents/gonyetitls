<div class="container-fluid">

    <div class="row mt-15">
        <div class="col-md-12">
            <div class="panel border-primary no-border border-3-top">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h5>
                            <i class="fas fa-gas-pump"></i>
                            Fuel Consumption Report
                        </h5>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="row">

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>From</label>
                                <input type="date" class="form-control" wire:model="from">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>To</label>
                                <input type="date" class="form-control" wire:model="to">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Asset Type</label>
                                <select class="form-control" wire:model="asset_type">
                                    <option value="all">All</option>
                                    <option value="horse">Horses</option>
                                    <option value="vehicle">Vehicles</option>
                                </select>
                            </div>
                        </div>

                        @if($asset_type === 'all' || $asset_type === 'horse')
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Horse</label>
                                    <select class="form-control" wire:model="selectedHorse">
                                        <option value="">All Horses</option>
                                        @foreach($horses as $horse)
                                            <option value="{{ $horse->id }}">
                                                {{ $horse->registration_number }} ({{ $horse->fleet_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if($asset_type === 'all' || $asset_type === 'vehicle')
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Vehicle</label>
                                    <select class="form-control" wire:model="selectedVehicle">
                                        <option value="">All Vehicles</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">
                                                {{ $vehicle->registration_number }} ({{ $vehicle->fleet_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12">
                            <button type="button" class="btn btn-default btn-sm" wire:click="clearFilters">
                                <i class="fa fa-refresh"></i> Reset Filters
                            </button>
                        </div>

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-3">
                            <div class="panel bg-primary text-white">
                                <div class="panel-body">
                                    <h6>Total Closed Cycles</h6>
                                    <h3>{{ number_format($summary['cycles']) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="panel bg-success text-white">
                                <div class="panel-body">
                                    <h6>Total Distance</h6>
                                    <h3>{{ number_format($summary['total_distance'], 2) }} KM</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="panel bg-warning text-white">
                                <div class="panel-body">
                                    <h6>Total Fuel Used</h6>
                                    <h3>{{ number_format($summary['total_fuel'], 2) }} L</h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="panel bg-info text-white">
                                <div class="panel-body">
                                    <h6>Average Consumption</h6>
                                    <h3>{{ number_format($summary['average_km_per_litre'], 2) }} KM/L</h3>
                                    <small>{{ number_format($summary['average_litres_per_100km'], 2) }} L/100KM</small>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive mt-20">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Asset Type</th>
                                    <th>Asset</th>
                                    <th>Cycle Period</th>
                                    <th>Start Odometer</th>
                                    <th>End Odometer</th>
                                    <th>Distance</th>
                                    <th>Fuel Used</th>
                                    <th>KM/L</th>
                                    <th>L/100KM</th>
                                    <th>Fuel Entries</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($fuelConsumptions as $row)
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $row['asset_type'] }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong>{{ $row['asset_name'] ?? 'N/A' }}</strong>
                                        </td>

                                        <td>
                                            <small>
                                                {{ \Carbon\Carbon::parse($row['start_date'])->format('d M Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($row['end_date'])->format('d M Y') }}
                                            </small>
                                        </td>

                                        <td>
                                            {{ is_numeric($row['start_odometer']) ? number_format($row['start_odometer'], 2) : 'N/A' }}
                                        </td>

                                        <td>
                                            {{ is_numeric($row['end_odometer']) ? number_format($row['end_odometer'], 2) : 'N/A' }}
                                        </td>

                                        <td>
                                            {{ is_numeric($row['distance']) ? number_format($row['distance'], 2).' KM' : 'N/A' }}
                                        </td>

                                        <td>
                                            <strong>{{ number_format($row['fuel_used'], 2) }} L</strong>
                                        </td>

                                        <td>
                                            @if(is_numeric($row['km_per_litre']))
                                                <span class="badge badge-success">
                                                    {{ number_format($row['km_per_litre'], 2) }} KM/L
                                                </span>
                                            @else
                                                <span class="badge badge-danger">N/A</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if(is_numeric($row['litres_per_100km']))
                                                {{ number_format($row['litres_per_100km'], 2) }} L/100KM
                                            @else
                                                N/A
                                            @endif
                                        </td>

                                        <td>
                                            {{ $row['entries_count'] }}
                                        </td>

                                        <td>
                                            @if($row['status'] === 'Closed')
                                                <span class="badge badge-success">Closed Cycle</span>
                                            @else
                                                <span class="badge badge-danger">{{ $row['status'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <strong>No closed full-tank fuel consumption cycles found.</strong>
                                            <br>
                                            <small>
                                                Consumption is only calculated from one full tank refill to the next full tank refill.
                                            </small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if($fuelConsumptions->count() > 0)
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Totals</th>
                                        <th>{{ number_format($summary['total_distance'], 2) }} KM</th>
                                        <th>{{ number_format($summary['total_fuel'], 2) }} L</th>
                                        <th>{{ number_format($summary['average_km_per_litre'], 2) }} KM/L</th>
                                        <th>{{ number_format($summary['average_litres_per_100km'], 2) }} L/100KM</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="alert alert-info mt-20">
                        <strong>Calculation Rule:</strong>
                        Fuel consumption is calculated only between two full tank refills.
                        Partial refills between those two full tank records are included in the fuel used total.
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>