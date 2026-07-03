<div>
    <section class="section gonyeti-dashboard">
        <div class="container-fluid dashboard-shell">
            @php
                $authUser         = Auth::user();
                $employee         = optional($authUser)->employee;
                $department_names = $employee && $employee->departments ? $employee->departments->pluck('name')->toArray() : [];
                $role_names       = $authUser && $authUser->roles ? $authUser->roles->pluck('name')->toArray() : [];

                $isSuper          = in_array('Super Admin', $role_names);
                $isAdmin          = in_array('Admin', $role_names);
                $isDriver         = (bool) ($authUser->driver ?? false) || (bool) ($driver ?? false);
                $driverRecord     = $driver ?? ($employee->driver ?? null);

                $isWorkshopUser   = in_array('Workshop', $department_names);
                $isWorkshopAdmin  = $isSuper || ($isWorkshopUser && $isAdmin);
                $isWorkshopWorker = $isWorkshopUser && !$isWorkshopAdmin;

                // Department KPI blocks are for admin/managerial users only.
                // Drivers and non-admin workshop employees get their own restricted dashboard below.
                $canSee = function(array $departments) use ($department_names, $isSuper, $isDriver, $isWorkshopWorker) {
                    if ($isDriver || $isWorkshopWorker) {
                        return false;
                    }

                    return $isSuper || count(array_intersect($departments, $department_names)) > 0;
                };
                $fmt = fn($value, $decimals = 0) => is_numeric($value) ? number_format((float) $value, $decimals) : $value;
                $money = fn($value) => ($currency_name ?? 'USD').' '.$this->formatCurrency((float) $value);
                $alertTotal = ($overdue_invoices_count ?? 0) + ($expired_reminders_count ?? 0) + ($expired_documents_count ?? 0) + ($docs_expiring_7d ?? 0) + ($vehicles_on_breakdown_count ?? 0) + ($trips_overdue_count ?? 0) + ($pending_authorizations_count ?? 0);
            @endphp

            <style>
                .gonyeti-dashboard{--gd-blue:#123bdc;--gd-blue-dark:#071b3a;--gd-orange:#ff7a00;--gd-orange-soft:#fff1e4;--gd-cyan:#0d8ecf;background:#f4f7fb;min-height:calc(100vh - 70px);padding:8px 0 18px 0;}
                .dashboard-shell{max-width:100%;}
                .gd-hero{background:linear-gradient(135deg,#061632 0%,#082963 46%,#123bdc 100%);border-radius:16px;padding:14px 18px;color:#fff;margin-bottom:12px;box-shadow:0 10px 26px rgba(6,22,50,.24);position:relative;overflow:hidden;border:1px solid rgba(255,255,255,.16);}
                .gd-hero:before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.52),rgba(0,0,0,.28),rgba(255,122,0,.10));z-index:0;}
                .gd-hero:after{content:"";position:absolute;right:-80px;top:-80px;width:260px;height:260px;border-radius:50%;background:rgba(255,122,0,.18);z-index:0;}
                .gd-title{font-size:21px;font-weight:900;margin:0 0 3px 0;letter-spacing:.25px;color:#fff;text-shadow:0 3px 10px rgba(0,0,0,.65);line-height:1.15;}
                .gd-subtitle{font-size:12px;color:#f5f8ff;margin:0;text-shadow:0 2px 6px rgba(0,0,0,.55);}
                .gd-toolbar{text-align:right;position:relative;z-index:2;}
                .gd-pill{display:inline-block;border:1px solid rgba(255,255,255,.28);background:rgba(0,0,0,.34);backdrop-filter:blur(8px);border-radius:20px;padding:6px 10px;font-size:11px;margin-left:6px;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.45);}
                .gd-alert-strip{display:flex;gap:8px;overflow-x:auto;margin-bottom:12px;padding-bottom:2px;}
                .gd-alert{min-width:165px;background:#fff;border-radius:10px;border-left:4px solid #d9534f;padding:10px 12px;box-shadow:0 4px 14px rgba(15,39,83,.08);color:#26384f;text-decoration:none!important;}
                .gd-alert.warning{border-left-color:#f0ad4e}.gd-alert.info{border-left-color:#337ab7}.gd-alert.success{border-left-color:#5cb85c}
                .gd-alert .num{font-size:20px;font-weight:800;line-height:1}.gd-alert .label{font-size:11px;text-transform:uppercase;color:#6b7788;margin-top:4px;display:block;}
                .gd-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px;align-items:start;}
                .gd-panel{grid-column:span 6;background:#fff;border:1px solid #e4ebf5;border-radius:14px;box-shadow:0 5px 18px rgba(15,39,83,.07);overflow:hidden;}
                .gd-panel.wide{grid-column:span 12}.gd-panel.third{grid-column:span 4}.gd-panel.small{grid-column:span 3}
                .gd-panel-head{padding:12px 14px;border-bottom:1px solid #edf2f8;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(180deg,#fff,#fbfdff);}
                .gd-panel-title{margin:0;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:#102a56;}
                .gd-panel-note{font-size:11px;color:#7c8798;}
                .gd-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:0;}
                .gd-kpi{padding:12px 12px;border-right:1px solid #edf2f8;border-bottom:1px solid #edf2f8;min-height:82px;position:relative;color:#26384f;text-decoration:none!important;background:#fff;}
                .gd-kpi:nth-child(4n){border-right:0;}
                .gd-kpi:hover{background:#f7fbff;}
                .gd-kpi .icon{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:#eaf2ff;color:var(--gd-blue);margin-bottom:7px;font-size:14px;}
                .gd-kpi:nth-child(2n) .icon{background:var(--gd-orange-soft);color:var(--gd-orange);}
                .gd-kpi .value{font-size:20px;font-weight:850;color:#0d2240;line-height:1.05;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
                .gd-kpi .name{font-size:11px;color:#637083;text-transform:uppercase;letter-spacing:.35px;margin-top:5px;display:block;}
                .gd-kpi.good .icon{background:#e9f8f0;color:#1f9b59}.gd-kpi.warn .icon{background:#fff6e5;color:#c77a00}.gd-kpi.danger .icon{background:#fdecea;color:#c0392b}.gd-kpi.dark .icon{background:#eef1f6;color:#34495e}

                .gd-module-count-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(138px,1fr));gap:10px;padding:12px 14px 14px 14px;}
                .gd-module-count-card{background:#fff;border:1px solid rgba(18,59,220,.13);border-left:4px solid var(--gd-orange);border-radius:12px;padding:10px 11px;color:#0d2240;text-decoration:none!important;box-shadow:0 4px 13px rgba(15,39,83,.055);transition:all .18s ease;min-height:78px;display:flex;flex-direction:column;justify-content:space-between;}
                .gd-module-count-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,39,83,.12);color:var(--gd-blue);}
                .gd-module-top{display:flex;align-items:center;justify-content:space-between;gap:8px;}
                .gd-module-icon{width:28px;height:28px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;background:#eaf2ff;color:var(--gd-blue);font-size:13px;flex:0 0 28px;}
                .gd-module-count-card:nth-child(2n) .gd-module-icon{background:var(--gd-orange-soft);color:var(--gd-orange);}
                .gd-module-value{font-size:22px;font-weight:900;line-height:1;color:#0d2240;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
                .gd-module-label{display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.45px;color:#637083;margin-top:8px;line-height:1.2;}
                .gd-module-badge{display:inline-block;font-size:9px;font-weight:800;color:#fff;background:var(--gd-blue);border-radius:20px;padding:2px 6px;margin-left:4px;vertical-align:middle;}
                .gd-mini-list{padding:10px 14px;}
                .gd-mini-row{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf2f8;padding:8px 0;font-size:12px;}
                .gd-mini-row:last-child{border-bottom:0;}.gd-mini-row strong{color:#172b4d}.gd-mini-row span{color:#6b7788;}
                .gd-progress{height:7px;background:#e9eef6;border-radius:10px;overflow:hidden;margin-top:7px;}.gd-progress b{display:block;height:100%;background:linear-gradient(90deg,var(--gd-blue),var(--gd-orange));border-radius:10px;}
                .gd-chart-wrap{padding:12px 14px 16px 14px;}
                .gd-chart{width:100%;height:260px;}
                .gd-chart.tall{height:320px;}
                .gd-chart.short{height:220px;}
                .gd-chart-toolbar{padding:8px 14px;border-bottom:1px solid #edf2f8;background:#fbfdff;display:flex;gap:8px;align-items:center;justify-content:flex-end;}
                .gd-chart-toolbar select{height:30px;border:1px solid #dbe5f1;border-radius:7px;padding:4px 8px;font-size:12px;background:#fff;}
                .gd-logo-box{height:46px;min-width:142px;background:#fff;border-radius:12px;padding:6px 10px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 18px rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.65);}
                .gd-logo-mark{height:34px;max-width:150px;object-fit:contain;filter:none;}
                .gd-title-row{display:flex;align-items:center;gap:12px;position:relative;z-index:2;}
                .gd-title-text{background:rgba(0,0,0,.42);border-left:4px solid var(--gd-orange);border-radius:12px;padding:9px 14px;box-shadow:0 6px 16px rgba(0,0,0,.16);}
                @media (max-width:1199px){.gd-panel,.gd-panel.third,.gd-panel.small{grid-column:span 12}.gd-kpi-grid{grid-template-columns:repeat(3,1fr)}.gd-kpi:nth-child(4n){border-right:1px solid #edf2f8}.gd-kpi:nth-child(3n){border-right:0}}
                @media (max-width:767px){.gd-toolbar{text-align:left;margin-top:10px}.gd-kpi-grid{grid-template-columns:repeat(2,1fr)}.gd-kpi:nth-child(3n){border-right:1px solid #edf2f8}.gd-kpi:nth-child(2n){border-right:0}.gd-hero{padding:14px}.gd-panel{border-radius:10px}}
            </style>

            <div class="gd-hero">
                <div class="row">
                    <div class="col-md-7">
                        <div class="gd-title-row">
                            <div class="gd-logo-box"><img class="gd-logo-mark" src="{{ asset('images/uploads/logo.png') }}" alt="Gonyeti ERP"></div>
                            <div class="gd-title-text">
                                <h4 class="gd-title">Gonyeti ERP Command Dashboard</h4>
                                <p class="gd-subtitle">Grouped operational, financial and compliance KPIs for {{ now()->format('d M Y') }}.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 gd-toolbar">
                        <span class="gd-pill"><i class="fa fa-building"></i> {{ $company->name ?? 'Company' }}</span>
                        <span class="gd-pill"><i class="fa fa-money"></i> {{ $currency_name ?? 'USD' }}</span>
                        <span class="gd-pill"><i class="fa fa-refresh"></i> {{ $last_refreshed_at ?? now()->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            @if ($isDriver)
                {{-- DRIVER DASHBOARD: restricted to driver-owned records only. Restores legacy driver widgets in the modern design. --}}
                <div class="gd-panel wide" style="margin-bottom:12px;">
                    <div class="gd-panel-head">
                        <h5 class="gd-panel-title"><i class="fa fa-id-card"></i> My Driver Dashboard</h5>
                        <span class="gd-panel-note">{{ now()->year }} personal activity only</span>
                    </div>
                    <div class="gd-kpi-grid">
                        <a class="gd-kpi" href="{{ route('trips.index') }}">
                            <span class="icon"><i class="fa fa-road"></i></span>
                            <div class="value">{{ $fmt($driver_trips ?? 0) }}</div>
                            <span class="name">My Trips YTD</span>
                        </a>
                        <a class="gd-kpi" href="{{ $driverRecord ? route('checklists.index') : '#' }}">
                            <span class="icon"><i class="fa fa-search"></i></span>
                            <div class="value">{{ $fmt($driver_inspections ?? 0) }}</div>
                            <span class="name">My Inspections YTD</span>
                        </a>
                        <a class="gd-kpi danger" href="{{ $driverRecord ? route('driver.breakdowns',$driver->id) : '#' }}">
                            <span class="icon"><i class="fa fa-wrench"></i></span>
                            <div class="value">{{ $fmt($driver_breakdowns ?? 0) }}</div>
                            <span class="name">My Breakdown Reports</span>
                        </a>
                        <a class="gd-kpi warn" href="{{ $driverRecord ? route('recoveries.index') : '#' }}">
                            <span class="icon"><i class="fa fa-list"></i></span>
                            <div class="value">{{ $fmt($driver_recoveries ?? 0) }}</div>
                            <span class="name">My Recoveries YTD</span>
                        </a>
                    </div>
                </div>

                <div class="gd-panel wide">
                    <div class="gd-panel-head">
                        <h5 class="gd-panel-title"><i class="fa fa-truck"></i> My Trips</h5>
                        <span class="gd-panel-note">Latest driver trip records</span>
                    </div>
                    <div class="gd-mini-list" style="overflow-x:auto; max-height:550px;">
                        @livewire('dashboard.driver-trips')
                    </div>
                </div>

            @elseif ($isWorkshopWorker)
                {{-- WORKSHOP NON-ADMIN DASHBOARD: mechanics/technicians see only their assigned work. --}}
                <div class="gd-panel wide" style="margin-bottom:12px;">
                    <div class="gd-panel-head">
                        <h5 class="gd-panel-title"><i class="fa fa-wrench"></i> My Workshop Workbench</h5>
                        <span class="gd-panel-note">Assigned tickets and inspections only</span>
                    </div>
                    <div class="gd-kpi-grid">
                        <a class="gd-kpi" href="{{ route('tickets.cards', $employee->id) }}">
                            <span class="icon"><i class="fa fa-ticket"></i></span>
                            <div class="value">{{ $fmt($my_tickets_count ?? 0) }}</div>
                            <span class="name">My Tickets YTD</span>
                        </a>
                        <a class="gd-kpi" href="{{ route('inspections.my-inspections', $employee->id) }}">
                            <span class="icon"><i class="fa fa-search"></i></span>
                            <div class="value">{{ $fmt($my_inspections_count ?? 0) }}</div>
                            <span class="name">My Inspections YTD</span>
                        </a>
                    </div>
                </div>

                <div class="gd-panel wide">
                    <div class="gd-panel-head">
                        <h5 class="gd-panel-title"><i class="fa fa-lock"></i> Restricted Access</h5>
                        <span class="gd-panel-note">Admin workshop KPIs are hidden for non-admin users</span>
                    </div>
                    <div class="gd-mini-list">
                        <div class="gd-mini-row"><strong>Tickets</strong><span><a href="{{ route('tickets.cards', $employee->id) }}">Open my ticket cards</a></span></div>
                        <div class="gd-mini-row"><strong>Inspections</strong><span><a href="{{ route('inspections.my-inspections', $employee->id) }}">Open my inspections</a></span></div>
                    </div>
                </div>

            @else

            @if ($alertTotal > 0)
                <div class="gd-alert-strip">
                    @if (($expired_reminders_count ?? 0) > 0)<a class="gd-alert" href="{{ route('fitnesses.index',['expired_reminders' => 1]) }}"><span class="num">{{ $expired_reminders_count }}</span><span class="label"><i class="fa fa-bell"></i> Expired reminders</span></a>@endif
                    @if (($reminders_expiring_7d ?? 0) > 0)<a class="gd-alert warning" href="{{ route('fitnesses.index') }}"><span class="num">{{ $reminders_expiring_7d }}</span><span class="label"><i class="fa fa-clock-o"></i> Expiring in 7 days</span></a>@endif
                    @if (($expired_documents_count ?? 0) > 0)<a class="gd-alert" href="{{route('documents.index',['category' => 'all', 'expired_documents' => 1])}}"><span class="num">{{ $expired_documents_count }}</span><span class="label"><i class="fa fa-file"></i> Expired documents</span></a>@endif
                    @if (($overdue_invoices_count ?? 0) > 0)<a class="gd-alert" href="{{ route('invoices.index') }}"><span class="num">{{ $overdue_invoices_count }}</span><span class="label"><i class="fa fa-exclamation-circle"></i> Overdue invoices</span></a>@endif
                    @if (($fleet_in_workshop ?? 0) > 0)<a class="gd-alert" href="{{ route('bookings.index') }}"><span class="num">{{ $fleet_in_workshop }}</span><span class="label"><i class="fa fa-wrench"></i> Equipment(s) in workshop</span></a>@endif
                    @if (($trips_overdue_count ?? 0) > 0)<a class="gd-alert warning" href="#"><span class="num">{{ $trips_overdue_count }}</span><span class="label"><i class="fa fa-road"></i> Overdue trips</span></a>@endif
                    @if (($pending_authorizations_count ?? 0) > 0)<a class="gd-alert info" href="#"><span class="num">{{ $pending_authorizations_count }}</span><span class="label"><i class="fa fa-hourglass-half"></i> Pending approvals</span></a>@endif
                </div>
            @endif


            {{-- Compact legacy-style module counts. These use the existing loadCounts() properties, where YTD counts are already year-filtered in the Livewire component. --}}
            <div class="gd-panel wide" style="margin-bottom:12px;">
                <div class="gd-panel-head">
                    <h5 class="gd-panel-title"><i class="fa fa-th-large"></i> Module Quick Counts</h5>
                    <span class="gd-panel-note">Clickable year-to-date and master record totals</span>
                </div>
                <div class="gd-module-count-grid">
                    @if ($canSee(['Transport & Logistics']))
                        <a href="{{ route('transport_orders.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-file-text-o"></i></span><strong class="gd-module-value">{{ $fmt($transport_order_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">TOs <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('trips.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-road"></i></span><strong class="gd-module-value">{{ $fmt($trip_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Trips <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('shifts.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-clock"></i></span><strong class="gd-module-value">{{ $fmt($shift_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Shifts <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('horses.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fas fa-truck"></i></span><strong class="gd-module-value">{{ $fmt($horse_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Horses Active</span>
                        </a>
                        <a href="{{ route('trailers.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fas fa-trailer"></i></span><strong class="gd-module-value">{{ $fmt($trailer_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Trailers Active</span>
                        </a>
                        <a href="{{ route('drivers.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-users"></i></span><strong class="gd-module-value">{{ $fmt($driver_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Drivers Active</span>
                        </a>
                    @endif

                    @if ($canSee(['Finance']))
                        <a href="{{ route('customers.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-users"></i></span><strong class="gd-module-value">{{ $fmt($customer_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Customers Active</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-file-text"></i></span><strong class="gd-module-value">{{ $fmt($invoice_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Invoices <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('bills.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-file"></i></span><strong class="gd-module-value">{{ $fmt($bill_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Bills <span class="gd-module-badge">YTD</span></span>
                        </a>
                    @endif

                    @if ($canSee(['Transport & Logistics']))
                        <a href="{{ route('fuels.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-tint"></i></span><strong class="gd-module-value">{{ $fmt($fuel_order_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Fuel Issues <span class="gd-module-badge">YTD</span></span>
                        </a>
                    @endif

                    @if ($canSee(['Workshop']))
                        <a href="{{ route('bookings.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-wrench"></i></span><strong class="gd-module-value">{{ $fmt($booking_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Job Cards <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('tickets.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-ticket"></i></span><strong class="gd-module-value">{{ $fmt($ticket_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Tickets <span class="gd-module-badge">YTD</span></span>
                        </a>
                    @endif

                    @if ($canSee(['Stores']))
                        <a href="{{ route('inventories.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-cubes"></i></span><strong class="gd-module-value">{{ $fmt($inventory_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Inventory</span>
                        </a>
                        <a href="{{ route('inventory_purchases.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-shopping-cart"></i></span><strong class="gd-module-value">{{ $fmt($inventory_purchases_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">POs <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('inventory_dispatches.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-send"></i></span><strong class="gd-module-value">{{ $fmt($inventory_dispatches_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Dispatches <span class="gd-module-badge">YTD</span></span>
                        </a>
                        <a href="{{ route('tyres.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-circle-o"></i></span><strong class="gd-module-value">{{ $fmt($tyre_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Active Tyres</span>
                        </a>
                    @endif

                    @if ($canSee(['Human Resources']))
                        <a href="{{ route('employees.index') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-users"></i></span><strong class="gd-module-value">{{ $fmt($employee_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Employees Active</span>
                        </a>
                        <a href="{{ route('leaves.manage') }}" class="gd-module-count-card">
                            <div class="gd-module-top"><span class="gd-module-icon"><i class="fa fa-plane"></i></span><strong class="gd-module-value">{{ $fmt($leave_count ?? 0) }}</strong></div>
                            <span class="gd-module-label">Leave Applied <span class="gd-module-badge">YTD</span></span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="gd-grid">
                @if ($canSee(['Finance']))
                    <div class="gd-panel wide">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-line-chart"></i> Executive Finance</h5><span class="gd-panel-note">Revenue, cash, receivables and payables</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi" href="{{ route('invoices.index',['range' => 'td', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-dollar"></i></span><div class="value">{{ $money($revenue_today ?? 0) }}</div><span class="name">Revenue Today</span></a>
                            <a class="gd-kpi {{ ($revenue_mtd_change_pct ?? 0) >= 0 ? 'good' : 'danger' }}" href="{{ route('invoices.index',['range' => 'mtd', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-dollar"></i></span><div class="value">{{ $money($revenue_mtd ?? 0) }}</div><span class="name">Revenue MTD · {{ ($revenue_mtd_change_pct ?? 0) > 0 ? '+' : '' }}{{ $revenue_mtd_change_pct ?? 0 }}%</span></a>
                            <a class="gd-kpi" href="{{ route('invoices.index',['range' => 'ytd', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-dollar"></i></span><div class="value">{{ $money($revenue_ytd ?? 0) }}</div><span class="name">Revenue YTD</span></a>
                            <a class="gd-kpi {{ ($gross_margin_pct ?? 0) >= 20 ? 'good' : (($gross_margin_pct ?? 0) >= 10 ? 'warn' : 'danger') }}" href="#"><span class="icon"><i class="fa fa-percent"></i></span><div class="value">{{ $gross_margin_pct ?? 0 }}%</div><span class="name">Gross Margin MTD</span></a>
                            <a class="gd-kpi {{ ($outstanding_invoices_value ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('invoices.index',['status'=>'unpaid', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-inbox"></i></span><div class="value">{{ $money($outstanding_invoices_value ?? 0) }}</div><span class="name">Receivables · {{ $outstanding_invoices_count ?? 0 }}</span></a>
                            <a class="gd-kpi {{ ($overdue_invoices_value ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('invoices.index',['status'=>'overdue', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-exclamation-circle"></i></span><div class="value">{{ $money($overdue_invoices_value ?? 0) }}</div><span class="name">Overdue Receivables</span></a>
                            <a class="gd-kpi {{ ($outstanding_bills_value ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('bills.index',['status'=>'unpaid', 'authorization'=>'approved']) }}"><span class="icon"><i class="fa fa-send"></i></span><div class="value">{{ $money($outstanding_bills_value ?? 0) }}</div><span class="name">Payables</span></a>
                            <a class="gd-kpi {{ ($cash_position ?? 0) >= 0 ? 'good' : 'danger' }}" href="#"><span class="icon"><i class="fa fa-bank"></i></span><div class="value">{{ $money($cash_position ?? 0) }}</div><span class="name">Net Cash MTD</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Transport & Logistics']))
                    <div class="gd-panel">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-road"></i> Transport Operations</h5><span class="gd-panel-note">Today & MTD</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-calendar-check-o"></i></span><div class="value">{{ $trips_planned_today ?? 0 }}</div><span class="name">Planned Today</span></a>
                            <a class="gd-kpi" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-location-arrow"></i></span><div class="value">{{ $trips_in_progress ?? 0 }}</div><span class="name">In Progress</span></a>
                            <a class="gd-kpi good" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-check-circle"></i></span><div class="value">{{ $trips_completed_today ?? 0 }}</div><span class="name">Completed Today</span></a>
                            <a class="gd-kpi {{ ($trips_overdue_count ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-clock-o"></i></span><div class="value">{{ $trips_overdue_count ?? 0 }}</div><span class="name">Overdue</span></a>
                            <a class="gd-kpi" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-balance-scale"></i></span><div class="value">{{ $fmt($tonnes_today ?? 0,1) }}t</div><span class="name">Tonnes Today</span></a>
                            <a class="gd-kpi" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-balance-scale"></i></span><div class="value">{{ $this->formatCurrency($tonnes_mtd ?? 0,0) }}t</div><span class="name">Tonnes MTD</span></a>
                            <a class="gd-kpi" href="{{ route('trips.index') }}"><span class="icon"><i class="fa fa-tachometer"></i></span><div class="value">{{ $this->formatCurrency($km_mtd ?? 0,0) }}</div><span class="name">KM MTD</span></a>
                            <a class="gd-kpi {{ ($transport_orders_pending ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('transport_orders.index') }}"><span class="icon"><i class="fa fa-clipboard"></i></span><div class="value">{{ $transport_orders_pending ?? 0 }}</div><span class="name">Pending TOs</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Transport & Logistics']))
                    <div class="gd-panel">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-truck"></i> Fleet Status</h5><span class="gd-panel-note">Availability and compliance</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi good" href="{{ route('horses.index') }}"><span class="icon"><i class="fa fa-truck"></i></span><div class="value">{{ $fleet_active ?? 0 }}</div><span class="name">Active / On Road</span></a>
                            <a class="gd-kpi" href="{{ route('horses.index') }}"><span class="icon"><i class="fa fa-pause-circle"></i></span><div class="value">{{ $fleet_idle ?? 0 }}</div><span class="name">Idle / Available</span></a>
                            <a class="gd-kpi {{ ($fleet_in_workshop ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('bookings.index') }}"><span class="icon"><i class="fa fa-wrench"></i></span><div class="value">{{ $fleet_in_workshop ?? 0 }}</div><span class="name">In Workshop</span></a>
                            <a class="gd-kpi {{ ($fleet_on_breakdown ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('breakdowns.index') }}"><span class="icon"><i class="fa fa-warning"></i></span><div class="value">{{ $fleet_on_breakdown ?? 0 }}</div><span class="name">Breakdowns</span></a>
                            <a class="gd-kpi" href="{{ route('horses.index') }}"><span class="icon"><i class="fa fa-pie-chart"></i></span><div class="value">{{ $fleet_utilization_pct ?? 0 }}%</div><span class="name">Horse Utilization</span></a>
                            <a class="gd-kpi" href="{{ route('trailers.index') }}"><span class="icon"><i class="fa fa-columns"></i></span><div class="value">{{ $trailers_active ?? 0 }}</div><span class="name">Trailers Active</span></a>
                            <a class="gd-kpi {{ ($docs_expiring_30d ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('fitnesses.index') }}"><span class="icon"><i class="fa fa-id-card"></i></span><div class="value">{{ $docs_expiring_30d ?? 0 }}</div><span class="name">Docs Expiring 30d</span></a>
                            <a class="gd-kpi {{ ($vehicles_overdue_service ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('services.index') }}"><span class="icon"><i class="fa fa-cogs"></i></span><div class="value">{{ $vehicles_overdue_service ?? 0 }}</div><span class="name">Service Overdue</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Transport & Logistics']))
                    <div class="gd-panel third">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-tint"></i> Fuel</h5><span class="gd-panel-note">Issue, cost and stock</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi" href="{{ route('fuels.index') }}"><span class="icon"><i class="fa fa-tint"></i></span><div class="value">{{ $fmt($fuel_issued_today ?? 0) }} L</div><span class="name">Issued Today</span></a>
                            <a class="gd-kpi" href="{{ route('fuels.index') }}"><span class="icon"><i class="fa fa-bar-chart"></i></span><div class="value">{{ $this->formatCurrency($fuel_issued_mtd ?? 0,0) }} L</div><span class="name">Issued MTD</span></a>
                            <a class="gd-kpi warn" href="{{ route('fuels.index') }}"><span class="icon"><i class="fa fa-money"></i></span><div class="value">{{ $money($fuel_cost_mtd ?? 0) }}</div><span class="name">Cost MTD</span></a>
                            <a class="gd-kpi {{ ($fuel_exceptions_count ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('fuels.index') }}"><span class="icon"><i class="fa fa-warning"></i></span><div class="value">{{ $fuel_exceptions_count ?? 0 }}</div><span class="name">Exceptions</span></a>
                            <a class="gd-kpi" href="{{ route('containers.index') }}"><span class="icon"><i class="fa fa-database"></i></span><div class="value">{{ $fmt($diesel_balance_litres ?? 0) }} L</div><span class="name">Diesel Stock</span></a>
                            <a class="gd-kpi" href="{{ route('containers.index') }}"><span class="icon"><i class="fa fa-database"></i></span><div class="value">{{ $fmt($petrol_balance_litres ?? 0) }} L</div><span class="name">Petrol Stock</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Workshop']))
                    <div class="gd-panel third">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-wrench"></i> Workshop</h5><span class="gd-panel-note">Jobs and repair risk</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi {{ ($open_job_cards ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('bookings.index') }}"><span class="icon"><i class="fa fa-wrench"></i></span><div class="value">{{ $open_job_cards ?? 0 }}</div><span class="name">Open Jobs</span></a>
                            <a class="gd-kpi {{ ($overdue_repairs_count ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('bookings.index') }}"><span class="icon"><i class="fa fa-warning"></i></span><div class="value">{{ $overdue_repairs_count ?? 0 }}</div><span class="name">Overdue Repairs</span></a>
                            <a class="gd-kpi good" href="{{ route('bookings.index') }}"><span class="icon"><i class="fa fa-check"></i></span><div class="value">{{ $completed_job_cards_mtd ?? 0 }}</div><span class="name">Completed MTD</span></a>
                            <a class="gd-kpi warn" href="{{ route('bills.index') }}"><span class="icon"><i class="fa fa-money"></i></span><div class="value">{{ $money($workshop_spend_mtd ?? 0) }}</div><span class="name">Spend MTD</span></a>
                            <a class="gd-kpi {{ ($breakdowns_mtd ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('breakdowns.index') }}"><span class="icon"><i class="fa fa-exclamation-circle"></i></span><div class="value">{{ $breakdowns_mtd ?? 0 }}</div><span class="name">Breakdowns MTD</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Stores']))
                    <div class="gd-panel third">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-cubes"></i> Inventory & Stores</h5><span class="gd-panel-note">Stock and procurement</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi" href="{{ route('inventories.index') }}"><span class="icon"><i class="fa fa-cubes"></i></span><div class="value">{{ $money($inventory_total_value ?? 0) }}</div><span class="name">Stock Value</span></a>
                            <a class="gd-kpi {{ ($low_stock_count ?? 0) > 0 ? 'danger' : 'good' }}" href="{{ route('inventories.index') }}"><span class="icon"><i class="fa fa-warning"></i></span><div class="value">{{ $low_stock_count ?? 0 }}</div><span class="name">Low Stock</span></a>
                            <a class="gd-kpi {{ ($pending_pos_count ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('inventory_purchases.index') }}"><span class="icon"><i class="fa fa-shopping-cart"></i></span><div class="value">{{ $pending_pos_count ?? 0 }}</div><span class="name">Pending POs</span></a>
                            <a class="gd-kpi {{ ($pending_requisitions ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('requisitions.index') }}"><span class="icon"><i class="fa fa-list-alt"></i></span><div class="value">{{ $pending_requisitions ?? 0 }}</div><span class="name">Requisitions</span></a>
                            <a class="gd-kpi" href="{{ route('tyres.index') }}"><span class="icon"><i class="fa fa-circle-o"></i></span><div class="value">{{ $active_tyres ?? 0 }}</div><span class="name">Active Tyres</span></a>
                        </div>
                    </div>
                @endif

                @if ($canSee(['Human Resources']))
                    <div class="gd-panel">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-users"></i> Human Resources</h5><span class="gd-panel-note">People, drivers and payroll</span></div>
                        <div class="gd-kpi-grid">
                            <a class="gd-kpi" href="{{ route('employees.index') }}"><span class="icon"><i class="fa fa-users"></i></span><div class="value">{{ $active_employees ?? 0 }}</div><span class="name">Active Staff</span></a>
                            <a class="gd-kpi" href="{{ route('drivers.index') }}"><span class="icon"><i class="fa fa-id-badge"></i></span><div class="value">{{ $active_drivers ?? 0 }}</div><span class="name">Active Drivers</span></a>
                            <a class="gd-kpi" href="{{ route('leaves.manage') }}"><span class="icon"><i class="fa fa-plane"></i></span><div class="value">{{ $employees_on_leave ?? 0 }}</div><span class="name">On Leave Today</span></a>
                            <a class="gd-kpi {{ ($pending_leave_requests ?? 0) > 0 ? 'warn' : 'good' }}" href="{{ route('leaves.pending') }}"><span class="icon"><i class="fa fa-hourglass-half"></i></span><div class="value">{{ $pending_leave_requests ?? 0 }}</div><span class="name">Pending Leave</span></a>
                            <a class="gd-kpi warn" href="#"><span class="icon"><i class="fa fa-money"></i></span><div class="value">{{ $money($payroll_cost_mtd ?? 0) }}</div><span class="name">Payroll Cost MTD</span></a>
                            <a class="gd-kpi {{ ($outstanding_loans ?? 0) > 0 ? 'warn' : 'good' }}" href="#"><span class="icon"><i class="fa fa-credit-card"></i></span><div class="value">{{ $money($outstanding_loans ?? 0) }}</div><span class="name">Outstanding Loans</span></a>
                        </div>
                    </div>
                @endif

                @if (($pending_authorizations_count ?? 0) > 0)
                    <div id="pending-auth" class="gd-panel">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-check-square-o"></i> Pending Authorizations</h5><span class="gd-panel-note">Grouped approvals queue</span></div>
                        <div class="gd-kpi-grid">
                            @if (($pending_trips_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('trips.pending') }}"><span class="icon"><i class="fa fa-road"></i></span><div class="value">{{ $pending_trips_auth }}</div><span class="name">Trips</span></a>@endif
                            @if (($pending_invoices_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('invoices.pending') }}"><span class="icon"><i class="fa fa-file-text"></i></span><div class="value">{{ $pending_invoices_auth }}</div><span class="name">Invoices</span></a>@endif
                            @if (($pending_bills_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('bills.pending') }}"><span class="icon"><i class="fa fa-file"></i></span><div class="value">{{ $pending_bills_auth }}</div><span class="name">Bills</span></a>@endif
                            @if (($pending_fuel_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('fuels.pending') }}"><span class="icon"><i class="fa fa-tint"></i></span><div class="value">{{ $pending_fuel_auth }}</div><span class="name">Fuel</span></a>@endif
                            @if (($pending_requisitions_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('requisitions.pending') }}"><span class="icon"><i class="fa fa-list-alt"></i></span><div class="value">{{ $pending_requisitions_auth }}</div><span class="name">Requisitions</span></a>@endif
                            @if (($pending_leave_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('leaves.pending') }}"><span class="icon"><i class="fa fa-plane"></i></span><div class="value">{{ $pending_leave_auth }}</div><span class="name">Leave</span></a>@endif
                            @if (($pending_purchase_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('inventory_purchases.pending') }}"><span class="icon"><i class="fa fa-shopping-cart"></i></span><div class="value">{{ $pending_purchase_auth }}</div><span class="name">Purchases</span></a>@endif
                            @if (($pending_bookings_auth ?? 0) > 0)<a class="gd-kpi warn" href="{{ route('bookings.pending') }}"><span class="icon"><i class="fa fa-wrench"></i></span><div class="value">{{ $pending_bookings_auth }}</div><span class="name">Workshop</span></a>@endif
                        </div>
                    </div>
                @endif

                @if (($top_drivers ?? collect())->count() || ($top_horses ?? collect())->count())
                    <div class="gd-panel wide">
                        <div class="gd-panel-head"><h5 class="gd-panel-title"><i class="fa fa-trophy"></i> Performance Snapshot</h5><span class="gd-panel-note">Current month leaders</span></div>
                        <div class="row" style="margin:0;">
                            <div class="col-md-6 gd-mini-list">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Top Drivers</h6>
                                @forelse (($top_drivers ?? collect()) as $driverRow)
                                    <div class="gd-mini-row"><strong>{{ trim(($driverRow->employee->name ?? '').' '.($driverRow->employee->surname ?? '')) ?: 'Driver' }}</strong><span>{{ $driverRow->trips_count ?? 0 }} trips · {{ $money($driverRow->total_revenue ?? 0) }}</span></div>
                                @empty
                                    <div class="gd-mini-row"><span>No driver performance data yet.</span></div>
                                @endforelse
                            </div>
                            <div class="col-md-6 gd-mini-list">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Top Horses</h6>
                                @forelse (($top_horses ?? collect()) as $horseRow)
                                    <div class="gd-mini-row"><strong>{{ $horseRow->registration_number ?? $horseRow->horse_number ?? 'Horse' }}</strong><span>{{ $horseRow->trips_count ?? 0 }} trips · {{ $fmt($horseRow->fuel_usage ?? 0) }} L</span></div>
                                @empty
                                    <div class="gd-mini-row"><span>No horse performance data yet.</span></div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Charts retained from the previous dashboard and restyled using Gonyeti blue/orange palette --}}
                <div class="gd-panel wide">
                    <div class="gd-panel-head">
                        <h5 class="gd-panel-title"><i class="fa fa-area-chart"></i> Analytics & Graphs</h5>
                        <span class="gd-panel-note">Financial, trips, fuel, workshop and performance trends</span>
                    </div>
                    <div class="gd-chart-toolbar">
                        <label style="font-size:12px;color:#637083;margin:0;">Reporting Year</label>
                        <select wire:model="year">
                            @for ($yr = now()->year; $yr >= 2021; $yr--)
                                <option value="{{ $yr }}">{{ $yr }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="row" style="margin:0;">
                        @if ($canSee(['Finance','Management','Operations']))
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Income vs Expenses</h6>
                                <div id="gd_finance_chart" class="gd-chart"></div>
                            </div>
                        @endif
                        @if ($canSee(['Transport & Logistics']))
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Monthly Trips</h6>
                                <div id="gd_trips_chart" class="gd-chart"></div>
                            </div>
                        @endif
                        @if ($canSee(['Fuel','Transport & Logistics']))
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Fuel Stock Split</h6>
                                <div id="gd_fuel_stock_chart" class="gd-chart short"></div>
                            </div>
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Fuel Initial vs Top-up</h6>
                                <div id="gd_fuel_movement_chart" class="gd-chart short"></div>
                            </div>
                        @endif
                        @if ($canSee(['Workshop']))
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Workshop Bookings</h6>
                                <div id="gd_bookings_chart" class="gd-chart short"></div>
                            </div>
                        @endif
                        @if ($canSee(['Human Resources']))
                            <div class="col-md-6 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Employee Gender Mix</h6>
                                <div id="gd_gender_chart" class="gd-chart short"></div>
                            </div>
                        @endif
                        @if ($canSee(['Transport & Logistics']))
                            <div class="col-md-12 gd-chart-wrap">
                                <h6 class="gd-panel-title" style="margin-bottom:8px;">Driver Weight Performance</h6>
                                <div id="gd_driver_weight_chart" class="gd-chart tall"></div>
                            </div>
                        @endif
                    </div>
                </div>

            @endif
            </div>
        </div>
    </section>
</div>


@section('extra-js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        renderGonyetiDashboardCharts();
    });

    document.addEventListener('livewire:load', function () {
        Livewire.on('drivers-weight-updated', function () {
            setTimeout(renderGonyetiDashboardCharts, 150);
        });
    });

    function gdValue(value) {
        var n = parseFloat(value || 0);
        return isNaN(n) ? 0 : n;
    }

    function renderGonyetiDashboardCharts() {
        if (typeof Highcharts === 'undefined') return;

        var blue = '#123bdc';
        var blueDark = '#071b3a';
        var orange = '#ff7a00';
        var cyan = '#0d8ecf';
        var green = '#1f9b59';
        var red = '#c0392b';
        var grid = '#e8eef7';
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        Highcharts.setOptions({
            chart: { style: { fontFamily: 'Poppins, Arial, sans-serif' }, backgroundColor: 'transparent' },
            title: { text: null },
            credits: { enabled: false },
            exporting: { enabled: true },
            colors: [blue, orange, cyan, green, blueDark, red],
            xAxis: { lineColor: grid, tickColor: grid, labels: { style: { color: '#637083', fontSize: '10px' } } },
            yAxis: { gridLineColor: grid, title: { style: { color: '#637083' } }, labels: { style: { color: '#637083', fontSize: '10px' } } },
            legend: { itemStyle: { color: '#26384f', fontSize: '11px' } },
            tooltip: { borderColor: blue, borderRadius: 8, shadow: true }
        });

        if (document.getElementById('gd_finance_chart')) {
            Highcharts.chart('gd_finance_chart', {
                chart: { type: 'column' },
                xAxis: { categories: months },
                yAxis: { title: { text: '{{ $currency_name ?? "USD" }}' } },
                plotOptions: { column: { borderRadius: 4, groupPadding: 0.12 } },
                series: [
                    { name: 'Income', data: [gdValue({{ $jan ?? 0 }}),gdValue({{ $feb ?? 0 }}),gdValue({{ $mar ?? 0 }}),gdValue({{ $apr ?? 0 }}),gdValue({{ $may ?? 0 }}),gdValue({{ $jun ?? 0 }}),gdValue({{ $jul ?? 0 }}),gdValue({{ $aug ?? 0 }}),gdValue({{ $sep ?? 0 }}),gdValue({{ $oct ?? 0 }}),gdValue({{ $nov ?? 0 }}),gdValue({{ $dec ?? 0 }})] },
                    { name: 'Expenses', data: [gdValue({{ $jan_expense ?? 0 }}),gdValue({{ $feb_expense ?? 0 }}),gdValue({{ $mar_expense ?? 0 }}),gdValue({{ $apr_expense ?? 0 }}),gdValue({{ $may_expense ?? 0 }}),gdValue({{ $jun_expense ?? 0 }}),gdValue({{ $jul_expense ?? 0 }}),gdValue({{ $aug_expense ?? 0 }}),gdValue({{ $sep_expense ?? 0 }}),gdValue({{ $oct_expense ?? 0 }}),gdValue({{ $nov_expense ?? 0 }}),gdValue({{ $dec_expense ?? 0 }})] }
                ]
            });
        }

        if (document.getElementById('gd_trips_chart')) {
            Highcharts.chart('gd_trips_chart', {
                chart: { type: 'areaspline' },
                xAxis: { categories: months },
                yAxis: { title: { text: 'Trips' } },
                plotOptions: { areaspline: { fillOpacity: 0.18, marker: { radius: 3 } } },
                series: [{ name: 'Trips', data: [gdValue({{ $jan_trips ?? 0 }}),gdValue({{ $feb_trips ?? 0 }}),gdValue({{ $mar_trips ?? 0 }}),gdValue({{ $apr_trips ?? 0 }}),gdValue({{ $may_trips ?? 0 }}),gdValue({{ $jun_trips ?? 0 }}),gdValue({{ $jul_trips ?? 0 }}),gdValue({{ $aug_trips ?? 0 }}),gdValue({{ $sep_trips ?? 0 }}),gdValue({{ $oct_trips ?? 0 }}),gdValue({{ $nov_trips ?? 0 }}),gdValue({{ $dec_trips ?? 0 }})] }]
            });
        }

        if (document.getElementById('gd_fuel_stock_chart')) {
            Highcharts.chart('gd_fuel_stock_chart', {
                chart: { type: 'pie' },
                tooltip: { pointFormat: '<b>{point.y:,.0f} L</b> ({point.percentage:.1f}%)' },
                plotOptions: { pie: { innerSize: '58%', dataLabels: { enabled: true, format: '{point.name}: {point.y:,.0f}L', style: { fontSize: '10px' } } } },
                series: [{ name: 'Fuel', data: [{ name: 'Diesel', y: gdValue({{ $diesel_quantity ?? $diesel_balance_litres ?? 0 }}), color: blue }, { name: 'Petrol', y: gdValue({{ $petrol_quantity ?? $petrol_balance_litres ?? 0 }}), color: orange }] }]
            });
        }

        if (document.getElementById('gd_fuel_movement_chart')) {
            Highcharts.chart('gd_fuel_movement_chart', {
                chart: { type: 'column' },
                xAxis: { categories: months },
                yAxis: { title: { text: 'Litres' } },
                plotOptions: { column: { stacking: 'normal', borderRadius: 3 } },
                series: [
                    { name: 'Initial', data: [gdValue({{ $jan_initial_fuel ?? 0 }}),gdValue({{ $feb_initial_fuel ?? 0 }}),gdValue({{ $mar_initial_fuel ?? 0 }}),gdValue({{ $apr_initial_fuel ?? 0 }}),gdValue({{ $may_initial_fuel ?? 0 }}),gdValue({{ $jun_initial_fuel ?? 0 }}),gdValue({{ $jul_initial_fuel ?? 0 }}),gdValue({{ $aug_initial_fuel ?? 0 }}),gdValue({{ $sep_initial_fuel ?? 0 }}),gdValue({{ $oct_initial_fuel ?? 0 }}),gdValue({{ $nov_initial_fuel ?? 0 }}),gdValue({{ $dec_initial_fuel ?? 0 }})] },
                    { name: 'Top-up', data: [gdValue({{ $jan_topup_fuel ?? 0 }}),gdValue({{ $feb_topup_fuel ?? 0 }}),gdValue({{ $mar_topup_fuel ?? 0 }}),gdValue({{ $apr_topup_fuel ?? 0 }}),gdValue({{ $may_topup_fuel ?? 0 }}),gdValue({{ $jun_topup_fuel ?? 0 }}),gdValue({{ $jul_topup_fuel ?? 0 }}),gdValue({{ $aug_topup_fuel ?? 0 }}),gdValue({{ $sep_topup_fuel ?? 0 }}),gdValue({{ $oct_topup_fuel ?? 0 }}),gdValue({{ $nov_topup_fuel ?? 0 }}),gdValue({{ $dec_topup_fuel ?? 0 }})] }
                ]
            });
        }

        if (document.getElementById('gd_bookings_chart')) {
            Highcharts.chart('gd_bookings_chart', {
                chart: { type: 'column' },
                xAxis: { categories: months },
                yAxis: { min: 0, title: { text: 'Bookings' } },
                plotOptions: { column: { stacking: 'normal', borderRadius: 3 } },
                series: [
                    { name: 'Closed', data: [gdValue({{ $jan_closed_bookings ?? 0 }}),gdValue({{ $feb_closed_bookings ?? 0 }}),gdValue({{ $mar_closed_bookings ?? 0 }}),gdValue({{ $apr_closed_bookings ?? 0 }}),gdValue({{ $may_closed_bookings ?? 0 }}),gdValue({{ $jun_closed_bookings ?? 0 }}),gdValue({{ $jul_closed_bookings ?? 0 }}),gdValue({{ $aug_closed_bookings ?? 0 }}),gdValue({{ $sep_closed_bookings ?? 0 }}),gdValue({{ $oct_closed_bookings ?? 0 }}),gdValue({{ $nov_closed_bookings ?? 0 }}),gdValue({{ $dec_closed_bookings ?? 0 }})] },
                    { name: 'Open', data: [gdValue({{ $jan_open_bookings ?? 0 }}),gdValue({{ $feb_open_bookings ?? 0 }}),gdValue({{ $mar_open_bookings ?? 0 }}),gdValue({{ $apr_open_bookings ?? 0 }}),gdValue({{ $may_open_bookings ?? 0 }}),gdValue({{ $jun_open_bookings ?? 0 }}),gdValue({{ $jul_open_bookings ?? 0 }}),gdValue({{ $aug_open_bookings ?? 0 }}),gdValue({{ $sep_open_bookings ?? 0 }}),gdValue({{ $oct_open_bookings ?? 0 }}),gdValue({{ $nov_open_bookings ?? 0 }}),gdValue({{ $dec_open_bookings ?? 0 }})] }
                ]
            });
        }

        if (document.getElementById('gd_gender_chart')) {
            Highcharts.chart('gd_gender_chart', {
                chart: { type: 'pie' },
                tooltip: { pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)' },
                plotOptions: { pie: { innerSize: '58%', dataLabels: { enabled: true, format: '{point.name}: {point.y}', style: { fontSize: '10px' } } } },
                series: [{ name: 'Employees', data: [{ name: 'Male', y: gdValue({{ $males ?? 0 }}), color: blue }, { name: 'Female', y: gdValue({{ $females ?? 0 }}), color: orange }] }]
            });
        }

        if (document.getElementById('gd_driver_weight_chart')) {
            Highcharts.chart('gd_driver_weight_chart', {
                chart: { type: 'bar' },
                xAxis: { type: 'category' },
                yAxis: { title: { text: 'Weight' } },
                plotOptions: { bar: { borderRadius: 4, colorByPoint: false } },
                series: [{ name: 'Total Weight', data: @json($chartData ?? []) }]
            });
        }
    }
</script>
@endsection
