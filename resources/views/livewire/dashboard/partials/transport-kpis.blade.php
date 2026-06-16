{{--
    Transport ERP KPI strip.
    Place at: resources/views/livewire/dashboard/partials/transport-kpis.blade.php
    Include near the top of index.blade.php (after the @php role/department block):
        @include('livewire.dashboard.partials.transport-kpis')

    Assumes $department_names / $role_names exist (built in index.blade.php) and
    all KPI properties come from the dashboard component.
--}}
@php
    $sym = $company_currency->symbol ?? '';
    $cur = $company_currency->name ?? '';
@endphp

@if (in_array('Transport & Logistics', $department_names) || in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
@if (!Auth::user()->driver)
<section class="section pt-10">
    <div class="container-fluid">

        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title">
                    <h5><i class="fa fa-chart-line text-primary"></i> Fleet & Operations KPIs ({{ date('Y') }})</h5>
                </div>
            </div>
            <div class="panel-body p-20">

                {{-- Operational --}}
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #2196F3;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Fleet Utilisation</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($fleet_utilization, 1) }}%</div>
                            <small class="text-muted">{{ $active_horses }} active / {{ $idle_horses }} idle</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #4CAF50;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Trips (YTD)</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($trips_ytd) }}</div>
                            <small class="text-muted">{{ $completed_trips_month }} completed this month</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #FF9800;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Distance (YTD)</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($total_distance_ytd) }} <small>km</small></div>
                            <small class="text-muted">across the fleet</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #9C27B0;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Tonnage Moved (YTD)</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($tonnage_ytd, 1) }} <small>t</small></div>
                            <small class="text-muted">Loss ratio: {{ number_format($tonnage_loss_ratio, 2) }}%</small>
                        </div>
                    </div>
                </div>

                {{-- Fuel & financial --}}
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #F44336;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Avg Consumption</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($avg_consumption, 2) }} <small>L/100km</small></div>
                            <small class="text-muted">{{ number_format($total_fuel_ytd) }} L used YTD</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #607D8B;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Fuel Cost / km</div>
                            <div style="font-size:24px;font-weight:600;">{{ $sym }}{{ number_format($fuel_cost_per_km, 2) }}</div>
                            <small class="text-muted">{{ $sym }}{{ number_format($fuel_cost_ytd, 2) }} fuel spend YTD</small>
                        </div>
                    </div>
                    @if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names))
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #009688;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Gross Margin (YTD)</div>
                            <div style="font-size:24px;font-weight:600;">{{ number_format($gross_margin, 1) }}%</div>
                            <small class="text-muted">{{ $sym }}{{ number_format($revenue_ytd, 2) }} rev / {{ $sym }}{{ number_format($expenses_ytd, 2) }} exp</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-15">
                        <div class="kpi-tile" style="border-left:4px solid #3F51B5;padding:14px 16px;background:#f8f9fa;border-radius:6px;">
                            <div class="text-muted" style="font-size:12px;text-transform:uppercase;">Revenue / Trip</div>
                            <div style="font-size:24px;font-weight:600;">{{ $sym }}{{ number_format($revenue_per_trip, 2) }}</div>
                            <small class="text-muted">{{ $cur }} avg per trip YTD</small>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</section>
@endif
@endif
