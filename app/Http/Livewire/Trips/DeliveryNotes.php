<?php

namespace App\Http\Livewire\Trips;

use App\Mail\TripUpdatesMail;
use App\Models\Currency;
use App\Models\DeliveryNote;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Trip;
use App\Models\TripStatus;
use App\Models\UnitsOfMeasure;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class DeliveryNotes extends Component
{
    public $trip;
    public $cargo_type;
    public $delivery_note;
    public $user;
    public $employee;
    public $company;
    public $weight_loss;
    public $quantity_loss;
    public $litreage_loss;
    public $litreage_at_20_loss;
    public $freight_loss;
    public $chargeable_weight_loss;
    public $chargeable_quantity_loss;
    public $chargeable_litreage_loss;
    public $pattern;

    public $trip_id;
    public array $deliveryNotes = [];

    public $trip_number;

    public $driver_id;
    public $horse_id;

    public $initial_fuel;
 
    public $trailer_regnumbers;
    public $trailer_reg_numbers;
    public $collection_point;
    public $deliver_point;
    public $weight;
    public $cargo;
    public $litreage;
    public $quantity;
    public $authorized_by;
    public $ending_mileage;
    public $starting_mileage;
    public $ending_hours;
    public $starting_hours;
    public $checked_by;
    public $start_date;
    public $transporter_id;
    public $subtotal;
    public $cpk;
    public $total = 0;

    public $clearing_agent;
    public $boarder;
    public $route;
    public $truck_stops;

    //fuel order variables
    public $fuels;
    public $fuel_id;
    public $order_number;
    public $date;
    public $fullname;
    public $station_name;
    public $station_email;
    public $email;
    public $regnumber;
    public $fuel_type;
    public $fuel_order_quantity;
    public $driver;
    public $horse;
    public $delivery_point;
    public $fuel;
    public $mileage;
    public $emptyrun_origin;
    public $emptyrun_destination;

    public $search;
    protected $queryString = ['search'];

  
    public $customer_updates;
   
    public $customer_id;
    public $trip_expenses;
    public $net_profit;
    public $net_profit_percentage;
    public $markup_percentage;
    public $gross_profit;
   
    public $currency_id;
    public $currency;
    public $trailers;
   
    public $fuel_order_date;
    public $from_destination;
    public $to_destination;
    public $from_destination_country;
    public $to_destination_country;

    public $to;
    public $from;
    public $trip_filter;

    public $offloading_point;
    public $loading_point;
    public $loading_point_email;
    public $customer_email;
    public $fuel_station_email;
   
    public $end_date;
 
    public $rate;
    public $freight;
    public $distance;
    public $trip_status;

    public $trips;
    public $authorize;
    public $comments;
    public $default_currency;

  
    public $status;
    public $actual_distance;
    public $actual_offloading_date;
    public $estimated_offloading_date;
    
    public $customer_total;
    public $transporter_total;

    public $currencies;
    public $loaded_quantity;
    public $loaded_litreage;
    public $loaded_litreage_at_20;
    public $loaded_weight;
    public $loaded_rate;
    public $loaded_freight;
    public $loaded_date;
    public $offloaded_quantity;
    public $offloaded_distance;
    public $offloaded_litreage;
    public $offloaded_litreage_at_20;
    public $offloaded_weight;
    public $offloaded_rate;
    public $offloaded_freight;
    public $transporter_offloaded_rate;
    public $transporter_offloaded_freight;
    public $transporter_loaded_rate;
    public $transporter_loaded_freight;
    public $offloaded_date;
    public $payment_status;
    public $selectedStatus;
    public $trip_status_date;
    public $trip_status_description;
    public $selectedDeliveryNote;
    public $freight_calculation;
    public $total_expenses = 0;
    public $total_customer_expenses = 0;
    public $total_transporter_expenses = 0;
    public $cost_of_sales = 0;
    public $grossprofit;
    public $turnover = 0;

    public $role_names;
    public $department_names;
    public $rank_names;
    public $calculation_measurement;
    public $units_of_measures;
    public $units_of_measure;
    public $units_of_measure_id;
    public $trip_transport_orders ;

    public $active_tab;

    protected $listeners = ['tripStatusUpdated' => '$refresh'];


    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->trip_transport_orders  = $trip->trip_transport_orders;
        $this->delivery_note = $this->trip->delivery_note;
        $this->cargo_type = $this->trip->cargo ? $this->trip->cargo->type : "";
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->company = $this->employee->company;

        $this->currency = Currency::with('trips')->find($this->trip->currency_id); 
        $this->currency_id = $this->trip->currency_id; 
        $this->currencies = Currency::with('trips')->orderBy('name','asc')->get(); 
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get(); 
        $this->units_of_measure = $this->trip->units_of_measure; 

        $departments = $this->employee->departments;
        foreach($departments as $department){
            $this->department_names[] = $department->name;
        }
        $roles = $this->user->roles;
        foreach($roles as $role){
            $this->role_names[] = $role->name;
        }
        $ranks = $this->employee->ranks;
        foreach($ranks as $rank){
            $this->rank_names[] = $rank->name;
        }
    }


    private function resetInputFields(){

        $this->trip_status = Null;
        $this->selectedStatus = Null;
        $this->currency_id = Null;
        $this->turnover = Null;
        $this->cost_of_sales = Null;
        $this->trip_status_date = Null;
        $this->selectedDeliveryNote = Null;
        $this->trip_status_description = Null;
        $this->customer_updates = Null;
        $this->freight_calculation = Null;
        $this->cargo_type = Null;
        $this->ending_mileage = Null;
        $this->starting_mileage = Null;
        $this->ending_hours = Null;
        $this->starting_hours = Null;
        $this->units_of_measure = Null;
        $this->distance = Null;
        $this->loaded_quantity = Null;
        $this->loaded_litreage = Null;
        $this->loaded_litreage_at_20 = Null;
        $this->loaded_weight = Null;
        $this->loaded_rate = Null;
        $this->loaded_freight = Null;
        $this->transporter_loaded_rate = Null;
        $this->transporter_loaded_freight = Null;
        $this->loaded_date = Null;
        $this->transporter_loaded_rate = Null;
        $this->transporter_loaded_freight = Null;
        $this->offloaded_quantity = Null;
        $this->offloaded_litreage = Null;
        $this->offloaded_litreage_at_20 = Null;
        $this->offloaded_weight = Null;
        $this->offloaded_distance = Null;
        $this->offloaded_rate = Null;
        $this->offloaded_freight = Null;
        $this->transporter_offloaded_rate = Null;
        $this->transporter_offloaded_freight = Null;
        $this->offloaded_rate = Null;
        $this->offloaded_freight = Null;
        $this->transporter_offloaded_rate = Null;
        $this->transporter_offloaded_freight = Null;
        $this->offloaded_date = Null;

    }


    
    public function updatedSelectedStatus($status)
    {
    
        if (!is_null($status) ) {
            if ($status != $this->trip_status) {    
                $this->trip_status_date = Null;
                $this->trip_status_description = Null;
            }

            if ($status == "Offloaded" || $status == "Loaded") {
                $this->selectedDeliveryNote = TRUE;
            }else {
                $this->selectedDeliveryNote = NULL;
            }
        }

    }

    
    private function updateAssetMileage(Trip $trip): void
    {
        $targetModel = $trip->horse_id
            ? Horse::find($trip->horse_id)
            : ($trip->vehicle_id ? Vehicle::find($trip->vehicle_id) : null);

        if (! $targetModel) return;

        if ($this->ending_mileage > $targetModel->mileage) {
            $targetModel->mileage = $this->ending_mileage;
            $targetModel->save();
        }
    }

    private function releaseAssets(Trip $trip): void
    {
        $isOffloaded = $this->selectedStatus === 'Offloaded';
        $noBreakdown = $trip->breakdown_assignments->isEmpty();

        foreach ([
            Horse::withTrashed()->find($trip->horse_id),
            Vehicle::withTrashed()->find($trip->vehicle_id),
        ] as $asset) {
            if (! $asset) continue;

            $asset->status = 1;

            if ($isOffloaded && $noBreakdown) {
                if ($asset->mileage > 0 && $trip->distance > 0) {
                    $asset->mileage += $trip->distance;
                }
                if ($asset->fuel_balance > 0 && $trip->trip_fuel > 0) {
                    $asset->fuel_balance -= $trip->trip_fuel;
                }
            }

            $asset->save();
        }

        if ($trip->driver_id) {
            Driver::withTrashed()->find($trip->driver_id)?->update(['status' => 1]);
        }

        foreach ($trip->trailers as $trailer) {
            Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);
        }

        foreach ($trip->breakdown_assignments as $ba) {
            Horse::withTrashed()->find($ba->horse_id)?->update(['status' => 1]);
            Driver::withTrashed()->find($ba->driver_id)?->update(['status' => 1]);
            foreach ($ba->trailers as $trailer) {
                Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);
            }
        }
    }

    private function sendCustomerNotification(Trip $trip): void
    {
        if (! $this->customer_updates) return;

        $company = Auth::user()->company ?? Auth::user()->employee?->company;
        $email   = $trip->customer?->email;

        if ($email && $company) {
            Mail::to($email)->send(new TripUpdatesMail($trip, $company));
        }
    }

    



    private function aggregateDestinationTotals(Trip $trip): array
    {
        $destinations = $trip->trip_destinations ?? collect();

        return [
            'weight'        => $destinations->whereNotNull('weight')->where('weight', '!=', '')->sum('weight') ?: null,
            'quantity'      => $destinations->whereNotNull('quantity')->where('quantity', '!=', '')->sum('quantity') ?: null,
            'litreage'      => $destinations->whereNotNull('litreage')->where('litreage', '!=', '')->sum('litreage') ?: null,
            'litreage_at_20'=> $destinations->whereNotNull('litreage_at_20')->where('litreage_at_20', '!=', '')->sum('litreage_at_20') ?: null,
        ];
    }

    private function resolveDeliveryNote(Trip $trip, $tto): DeliveryNote
    {
        // Use stored delivery_note_id first — fastest and most reliable
        $storedId = $this->deliveryNotes[$tto->id]['delivery_note_id'] ?? null;

        if ($storedId) {
            $dn = DeliveryNote::find($storedId);
            if ($dn) return $dn;
        }

        // Fall back to DB query with dual-FK fallback
        $dn = DeliveryNote::where('trip_id', $trip->id)
            ->where(function ($q) use ($tto) {
                $q->where('trip_transport_order_id', $tto->id)
                ->orWhere(function ($q2) use ($tto) {
                    $q2->whereNull('trip_transport_order_id')
                        ->where('transport_order_id', $tto->transport_order_id);
                });
            })
            ->latest()
            ->first();

        if (! $dn) {
            $dn = new DeliveryNote();
            $dn->user_id = Auth::id();
            $dn->trip_id = $trip->id;
        }

        // Always normalise both FKs
        $dn->trip_transport_order_id = $tto->id;
        $dn->transport_order_id      = $tto->transport_order_id;

        return $dn;
    }

    public function status(int $id): void
    {
        $trip = Trip::withTrashed()
            ->with([
                'trip_transport_orders.transport_order.cargo',
                'trip_transport_orders.transport_order',
                'trip_destinations',
            ])
            ->findOrFail($id);

        $this->trip                    = $trip;
        $this->trip_id                 = $trip->id;
        $this->trip_status             = $trip->trip_status;
        $this->selectedStatus          = $trip->trip_status;
        $this->currency_id             = $trip->currency_id;
        $this->freight_calculation     = $trip->freight_calculation;
        $this->trip_status_date        = $trip->trip_status_date;
        $this->trip_status_description = $trip->trip_status_description;
        $this->customer_updates        = $trip->customer_updates;
        $this->ending_mileage          = $trip->ending_mileage;
        $this->starting_mileage        = $trip->starting_mileage;
        $this->ending_hours            = $trip->ending_hours;
        $this->starting_hours          = $trip->starting_hours;
        $this->trip_transport_orders   = $trip->trip_transport_orders;

        $this->freight_calculation     = $trip->freight_calculation;
        $this->calculation_measurement = $trip->calculation_measurement ?? '';

        $destinationTotals   = $this->aggregateDestinationTotals($trip);
        $this->deliveryNotes = [];

        foreach ($trip->trip_transport_orders as $tto) {

            // Always query directly — don't rely on the eager-loaded relationship
            // since it only matches on trip_transport_order_id
            $dn = DeliveryNote::where('trip_id', $trip->id)
                ->where(function ($q) use ($tto) {
                    $q->where('trip_transport_order_id', $tto->id)
                    ->orWhere(function ($q2) use ($tto) {
                        $q2->whereNull('trip_transport_order_id')
                            ->where('transport_order_id', $tto->transport_order_id);
                    });
                })
                ->latest()
                ->first();

            if (! $dn) {
                // Create and immediately persist a seeded DN
                $to = $tto->transport_order;

                $dn = new DeliveryNote();
                $dn->user_id                    = Auth::id();
                $dn->trip_id                    = $trip->id;
                $dn->trip_transport_order_id    = $tto->id;
                $dn->transport_order_id         = $tto->transport_order_id;
                $dn->units_of_measure_id        = $to->units_of_measure_id  ?? $trip->units_of_measure_id;
                $dn->distance                   = $trip->distance;
                $dn->loaded_quantity            = $to->quantity              ?? $trip->quantity;
                $dn->loaded_litreage            = $to->litreage              ?? $trip->litreage;
                $dn->loaded_litreage_at_20      = $to->litreage_at_20        ?? $trip->litreage_at_20;
                $dn->loaded_weight              = $to->weight                ?? $trip->weight;
                $dn->loaded_rate                = $to->rate                  ?? $trip->rate;
                $dn->loaded_freight             = $to->freight               ?? $trip->freight;
                $dn->transporter_loaded_rate    = $trip->transporter_rate;
                $dn->transporter_loaded_freight = $trip->transporter_freight;
                $dn->loaded_date                = $trip->start_date;
                $dn->save();

            } else {
                // Normalise legacy rows missing trip_transport_order_id
                $needsSave = false;

                if (is_null($dn->trip_transport_order_id)) {
                    $dn->trip_transport_order_id = $tto->id;
                    $needsSave = true;
                }

                // Backfill transporter values if missing
                if (! $dn->transporter_loaded_rate && ! $dn->transporter_loaded_freight) {
                    $dn->transporter_loaded_rate    = $trip->transporter_rate;
                    $dn->transporter_loaded_freight = $trip->transporter_freight;
                    $needsSave = true;
                }

                if ($needsSave) $dn->save();
            }

            // Populate the Livewire array — stored DN values take priority,
            // destination totals only fill in when DN offloaded fields are genuinely null
            $this->deliveryNotes[$tto->id] = [
                'delivery_note_id'             => $dn->id,
                'units_of_measure_id'          => $dn->units_of_measure_id,
                'distance'                     => $dn->distance,
                'loaded_date'                  => $dn->loaded_date,
                'loaded_quantity'              => $dn->loaded_quantity,
                'loaded_litreage'              => $dn->loaded_litreage,
                'loaded_litreage_at_20'        => $dn->loaded_litreage_at_20,
                'loaded_weight'                => $dn->loaded_weight,
                'loaded_rate'                  => $dn->loaded_rate,
                'loaded_freight'               => $dn->loaded_freight,
                'transporter_loaded_rate'      => $dn->transporter_loaded_rate,
                'transporter_loaded_freight'   => $dn->transporter_loaded_freight,
                'offloaded_date'               => $dn->offloaded_date,
                // Only fall back to destination totals when the DN field is strictly null
                'offloaded_quantity'           => $dn->offloaded_quantity    ?? $destinationTotals['quantity'],
                'offloaded_litreage'           => $dn->offloaded_litreage    ?? $destinationTotals['litreage'],
                'offloaded_litreage_at_20'     => $dn->offloaded_litreage_at_20 ?? $destinationTotals['litreage_at_20'],
                'offloaded_weight'             => $dn->offloaded_weight      ?? $destinationTotals['weight'],
                'offloaded_distance'           => $dn->offloaded_distance,
                // If DN is already completed (status=1) use its own offload rates,
                // otherwise default to the loaded rates so the fields pre-populate
                'offloaded_rate'               => $dn->status
                                                    ? $dn->offloaded_rate
                                                    : ($dn->offloaded_rate ?? $dn->loaded_rate),
                'offloaded_freight'            => $dn->status
                                                    ? $dn->offloaded_freight
                                                    : ($dn->offloaded_freight ?? $dn->loaded_freight),
                'transporter_offloaded_rate'   => $dn->status
                                                    ? $dn->transporter_offloaded_rate
                                                    : ($dn->transporter_offloaded_rate ?? $dn->transporter_loaded_rate),
                'transporter_offloaded_freight'=> $dn->status
                                                    ? $dn->transporter_offloaded_freight
                                                    : ($dn->transporter_offloaded_freight ?? $dn->transporter_loaded_freight),
                'comments'                     => $dn->comments,
            ];
        }

        $this->dispatchBrowserEvent('show-statusModal');
    }

    public function update(): void
    {
        $this->validate([
            'selectedStatus'                    => 'required',
            'trip_status_date'                  => 'required|date',
            'trip_status_description'           => 'nullable|string',
            'deliveryNotes.*.loaded_date'       => 'required_if:selectedStatus,Loaded,Offloaded|nullable|date',
            'deliveryNotes.*.loaded_rate'       => 'required_if:selectedStatus,Loaded,Offloaded|nullable|numeric',
            'deliveryNotes.*.loaded_freight'    => 'required_if:selectedStatus,Loaded,Offloaded|nullable|numeric',
            'deliveryNotes.*.offloaded_date'    => 'required_if:selectedStatus,Offloaded|nullable|date',
            'deliveryNotes.*.offloaded_rate'    => 'required_if:selectedStatus,Offloaded|nullable|numeric',
            'deliveryNotes.*.offloaded_freight' => 'required_if:selectedStatus,Offloaded|nullable|numeric',
        ]);

        DB::transaction(function () {

            $trip = Trip::withTrashed()
                ->with(['trip_transport_orders', 'trailers', 'breakdown_assignments.trailers'])
                ->findOrFail($this->trip_id);

            // --- Trip header ---
            $trip->trip_status             = $this->selectedStatus;
            $trip->trip_status_date        = $this->trip_status_date;
            $trip->trip_status_description = $this->trip_status_description;
            $trip->ending_mileage          = $this->ending_mileage;
            $trip->starting_mileage        = $this->starting_mileage;
            $trip->ending_hours            = $this->ending_hours;
            $trip->starting_hours          = $this->starting_hours;
            $trip->freight_calculation     = $this->freight_calculation;      // ← add
            $trip->calculation_measurement = $this->calculation_measurement;  // ← add

            if ($this->selectedStatus === 'Offloaded') {
                $firstNote      = collect($this->deliveryNotes)->first();
                $trip->end_date = $firstNote['offloaded_date'] ?? null;
            }

            $trip->save();

            $this->updateAssetMileage($trip);

            TripStatus::create([
                'user_id'     => Auth::id(),
                'trip_id'     => $trip->id,
                'status'      => $this->selectedStatus,
                'date'        => $this->trip_status_date,
                'description' => $this->trip_status_description,
            ]);

            // --- Delivery notes ---
            if (in_array($this->selectedStatus, ['Loaded', 'Offloaded'])) {

                foreach ($trip->trip_transport_orders as $tto) {

                    $data = $this->deliveryNotes[$tto->id] ?? null;
                    if (! $data) continue;

                    // Resolve by stored ID first — avoids a second query in most cases
                    $dn = isset($data['delivery_note_id'])
                        ? DeliveryNote::find($data['delivery_note_id'])
                        : null;

                    if (! $dn) {
                        // Fallback dual-FK query
                        $dn = DeliveryNote::where('trip_id', $trip->id)
                            ->where(function ($q) use ($tto) {
                                $q->where('trip_transport_order_id', $tto->id)
                                ->orWhere(function ($q2) use ($tto) {
                                    $q2->whereNull('trip_transport_order_id')
                                        ->where('transport_order_id', $tto->transport_order_id);
                                });
                            })
                            ->latest()
                            ->first();
                    }

                    if (! $dn) {
                        $dn          = new DeliveryNote();
                        $dn->user_id = Auth::id();
                        $dn->trip_id = $trip->id;
                    }

                    // Always stamp both FKs
                    $dn->trip_transport_order_id = $tto->id;
                    $dn->transport_order_id      = $tto->transport_order_id;

                    $dn->units_of_measure_id          = $data['units_of_measure_id'];
                    $dn->distance                     = $data['distance'];
                    $dn->loaded_date                  = $data['loaded_date'];
                    $dn->loaded_quantity              = $data['loaded_quantity'];
                    $dn->loaded_litreage              = $data['loaded_litreage'];
                    $dn->loaded_litreage_at_20        = $data['loaded_litreage_at_20'];
                    $dn->loaded_weight                = $data['loaded_weight'];
                    $dn->loaded_rate                  = $data['loaded_rate'];
                    $dn->loaded_freight               = $data['loaded_freight'];
                    $dn->transporter_loaded_rate      = $data['transporter_loaded_rate'];
                    $dn->transporter_loaded_freight   = $data['transporter_loaded_freight'];
                    $dn->offloaded_date               = $data['offloaded_date'];
                    $dn->offloaded_quantity           = $data['offloaded_quantity'];
                    $dn->offloaded_litreage           = $data['offloaded_litreage'];
                    $dn->offloaded_litreage_at_20     = $data['offloaded_litreage_at_20'];
                    $dn->offloaded_weight             = $data['offloaded_weight'];
                    $dn->offloaded_distance           = $data['offloaded_distance'];
                    $dn->offloaded_rate               = $data['offloaded_rate'];
                    $dn->offloaded_freight            = $data['offloaded_freight'];
                    $dn->transporter_offloaded_rate   = $data['transporter_offloaded_rate'];
                    $dn->transporter_offloaded_freight= $data['transporter_offloaded_freight'];
                    $dn->comments                     = $data['comments'];
                    $dn->freight_calculation          = $this->freight_calculation;      // ← add
                    $dn->calculation_measurement      = $this->calculation_measurement;  // ← add
                    $dn->status                       = 1;
                    $dn->save();

                    // Reflect the saved ID back into the component array
                    // so a second save in the same session uses the fast path
                    $this->deliveryNotes[$tto->id]['delivery_note_id'] = $dn->id;
                }
            }

            if (in_array($this->selectedStatus, ['Offloaded', 'Cancelled', 'Scheduled'])) {
                $this->releaseAssets($trip);
            }

            $this->sendCustomerNotification($trip);
        });

        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-statusModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Trip Status Updated Successfully!!',
        ]);
    }


  

  
    private function recalculateDeliveryNoteFreight(int $ttoId): void
    {
        $dn = &$this->deliveryNotes[$ttoId];
        if (! $dn) return;

        // Resolve cargo type for this specific TTO
        $cargoType = null;
        foreach ($this->trip_transport_orders as $tto) {
            if ($tto->id === $ttoId) {
                $cargoType = $tto->transport_order?->cargo?->type;
                break;
            }
        }

        // Recalculate loaded freight
        $dn['loaded_freight'] = $this->calculateFreight(
            $dn['loaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'],
            $dn['loaded_litreage'],
            $dn['distance']
        );

        // Recalculate offloaded freight
        $dn['offloaded_freight'] = $this->calculateFreight(
            $dn['offloaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'],
            $dn['offloaded_litreage'],
            $dn['offloaded_distance']
        );

        // Recalculate transporter loaded freight
        $dn['transporter_loaded_freight'] = $this->calculateFreight(
            $dn['transporter_loaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'],
            $dn['loaded_litreage'],
            $dn['distance']
        );

        // Recalculate transporter offloaded freight
        $dn['transporter_offloaded_freight'] = $this->calculateFreight(
            $dn['transporter_offloaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'],
            $dn['offloaded_litreage'],
            $dn['offloaded_distance']
        );
    }

    public function updated(string $propertyName): void
    {
        // Trip-level freight_calculation change — recalc ALL TTOs
        if ($propertyName === 'freight_calculation') {
            foreach ($this->trip_transport_orders as $tto) {
                $this->recalculateDeliveryNoteFreight($tto->id);
            }
            return;
        }

        // deliveryNotes.{ttoId}.{field} changes
        if (str_starts_with($propertyName, 'deliveryNotes.')) {
            // Extract ttoId and field from property path
            // e.g. "deliveryNotes.42.loaded_weight" → ttoId=42, field=loaded_weight
            $parts = explode('.', $propertyName);
            // $parts[0] = 'deliveryNotes', $parts[1] = ttoId, $parts[2] = field
            if (count($parts) < 3) return;

            $ttoId = (int) $parts[1];
            $field = $parts[2];

            $freightTriggers = [
                'loaded_rate',
                'loaded_weight',
                'loaded_litreage',
                'loaded_litreage_at_20',
                'distance',
                'offloaded_rate',
                'offloaded_weight',
                'offloaded_litreage',
                'offloaded_litreage_at_20',
                'offloaded_distance',
                'transporter_loaded_rate',
                'transporter_offloaded_rate',
            ];

            if (in_array($field, $freightTriggers)) {
                $this->recalculateDeliveryNoteFreight($ttoId);
            }
        }
    }

    private function calculateFreight(
        $rate,
        $cargoType,
        $freightCalculation,
        $measurement,
        $weight,
        $litreageAt20,
        $litreage,
        $distance
    ): ?float {
        if (!is_numeric($rate)) return null;

        switch ($freightCalculation) {
            case 'rate_weight':
                if ($cargoType === 'Solid' && is_numeric($weight)) {
                    return $rate * $weight;
                }
                if ($cargoType === 'Liquid') {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20)) {
                        return $rate * $litreageAt20;
                    }
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage)) {
                        return $rate * $litreage;
                    }
                }
                break;

            case 'rate_distance':
                if (is_numeric($distance)) {
                    return $rate * $distance;
                }
                break;

            case 'rate_weight_distance':
                if ($cargoType === 'Solid' && is_numeric($weight) && is_numeric($distance)) {
                    return $rate * $weight * $distance;
                }
                if ($cargoType === 'Liquid' && is_numeric($distance)) {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20)) {
                        return $rate * $litreageAt20 * $distance;
                    }
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage)) {
                        return $rate * $litreage * $distance;
                    }
                }
                break;

            case 'flat_rate':
                return $rate;

            default:
                return null;
        }

        return null;
    }


    public function render()
    {

        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        
        $this->delivery_note = DeliveryNote::where('trip_id',$this->trip->id)->first();
        $this->cargo_type = $this->trip->cargo ? $this->trip->cargo->type : "";

        if ($this->delivery_note){

            if ($this->delivery_note->offloaded_date){

                if (preg_match($this->pattern, $this->delivery_note->offloaded_date)){
                    $this->actual_offloading_date = Carbon::parse($this->delivery_note->offloaded_date)->format('d M Y g:i A');
                }else{
                    $this->actual_offloading_date = $this->delivery_note->offloaded_date;
                }
               

            }

            if ((is_numeric($this->delivery_note->loaded_weight) && $this->delivery_note->loaded_weight > 0 ) && ( is_numeric($this->delivery_note->offloaded_weight) && $this->delivery_note->offloaded_weight > 0 )) {
                $this->weight_loss = $this->delivery_note->loaded_weight - $this->delivery_note->offloaded_weight;
                if ((is_numeric($this->weight_loss) && $this->weight_loss > 0) && (is_numeric($this->trip->allowable_loss_weight) && $this->trip->allowable_loss_weight > 0)) {
                    $this->chargeable_weight_loss =   $this->weight_loss - $this->trip->allowable_loss_weight;
                }
            }

            if ((is_numeric($this->delivery_note->loaded_quantity) && $this->delivery_note->loaded_quantity > 0 ) && (is_numeric($this->delivery_note->offloaded_quantity) && $this->delivery_note->offloaded_quantity > 0) ) {
                $this->quantity_loss = $this->delivery_note->loaded_quantity - $this->delivery_note->offloaded_quantity;
            }

             if ((is_numeric($this->delivery_note->loaded_litreage) && $this->delivery_note->loaded_litreage > 0) && (is_numeric($this->delivery_note->offloaded_litreage) && $this->delivery_note->offloaded_litreage > 0)) {
            $this->litreage_loss = $this->delivery_note->loaded_litreage - $this->delivery_note->offloaded_litreage;
            }

            if ((is_numeric($this->delivery_note->loaded_litreage_at_20) && $this->delivery_note->loaded_litreage_at_20 > 0 ) && (is_numeric($this->delivery_note->offloaded_litreage_at_20) && $this->delivery_note->offloaded_litreage_at_20 > 0)) {
                $this->litreage_at_20_loss = $this->delivery_note->loaded_litreage_at_20 - $this->delivery_note->offloaded_litreage_at_20;
            }

            if ((is_numeric($this->litreage_at_20_loss) && $this->litreage_at_20_loss > 0) && (is_numeric($this->trip->allowable_loss_litreage) && $this->trip->allowable_loss_litreage > 0)) {
                $this->chargeable_litreage_loss =   $this->litreage_at_20_loss - $this->trip->allowable_loss_litreage;
            }

            if ((is_numeric($this->delivery_note->loaded_freight) && $this->delivery_note->loaded_freight > 0) && (is_numeric($this->delivery_note->offloaded_freight) && $this->delivery_note->offloaded_freight > 0)) {
                $this->freight_loss = $this->delivery_note->loaded_freight - $this->delivery_note->offloaded_freight;
            }
        }
        
        

       

       

        if ((is_numeric($this->quantity_loss) && $this->quantity_loss > 0) && (is_numeric($this->trip->allowable_loss_quantity) && $this->trip->allowable_loss_quantity > 0)) {
            $this->chargeable_quantity_loss =   $this->quantity_loss - $this->trip->allowable_loss_quantity;
        }

       
     
        return view('livewire.trips.delivery-notes');
    }
}
