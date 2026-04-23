<?php

namespace App\Http\Livewire\Trips;

use App\Mail\TripUpdatesMail;
use App\Models\DeliveryNote;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Trip;
use App\Models\TripStatus;
use App\Models\TripTransportOrder;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class TripStatusManager extends Component
{
    // ── Trip header scalars (NO Eloquent model stored as public property) ────
    public ?int    $trip_id                 = null;
    public ?string $trip_number             = null;
    public ?string $trip_status             = null;
    public ?string $selectedStatus          = null;
    public ?string $trip_status_date        = null;
    public ?string $trip_status_description = null;
    public bool    $customer_updates        = false;

    // ── Mileage / hours ──────────────────────────────────────────────────────
    public ?float $starting_mileage = null;
    public ?float $ending_mileage   = null;
    public ?float $starting_hours   = null;
    public ?float $ending_hours     = null;

    // ── Freight ──────────────────────────────────────────────────────────────
    public ?int   $currency_id             = null;
    public string $freight_calculation     = '';
    public string $calculation_measurement = '';

    // ── Trip scalar flags (safe to serialise) ────────────────────────────────
    public bool    $trip_transporter_agreement = false;
    public ?string $trip_cargo_type            = null;

    // ── Routing ──────────────────────────────────────────────────────────────
    public bool  $useTtoPath = false;
    public array $tto_ids    = [];

    // ── NEW path: per-TTO delivery note data ─────────────────────────────────
    public array $deliveryNotes = [];

    // ── LEGACY path: flat properties ─────────────────────────────────────────
    public ?string $cargo_type                    = null;
    public ?int    $units_of_measure_id           = null;
    public ?string $loaded_date                   = null;
    public ?float  $distance                      = null;
    public ?float  $loaded_quantity               = null;
    public ?float  $loaded_litreage               = null;
    public ?float  $loaded_litreage_at_20         = null;
    public ?float  $loaded_weight                 = null;
    public ?float  $loaded_rate                   = null;
    public ?float  $loaded_freight                = null;
    public ?float  $transporter_loaded_rate       = null;
    public ?float  $transporter_loaded_freight    = null;
    public ?string $offloaded_date                = null;
    public ?float  $offloaded_quantity            = null;
    public ?float  $offloaded_litreage            = null;
    public ?float  $offloaded_litreage_at_20      = null;
    public ?float  $offloaded_weight              = null;
    public ?float  $offloaded_distance            = null;
    public ?float  $offloaded_rate                = null;
    public ?float  $offloaded_freight             = null;
    public ?float  $transporter_offloaded_rate    = null;
    public ?float  $transporter_offloaded_freight = null;
    public ?string $comments                      = null;
    public ?int    $legacy_delivery_note_id       = null;

    protected $listeners = ['openTripStatusModal' => 'status'];

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        $trip_transport_orders = ! empty($this->tto_ids)
            ? TripTransportOrder::with('transport_order.cargo')
                ->whereIn('id', $this->tto_ids)
                ->get()
            : collect();

        $currencies = \App\Models\Currency::orderBy('name')->get();

        return view('livewire.trips.trip-status-manager', [
            'trip_transport_orders' => $trip_transport_orders,
            'currencies'            => $currencies,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OPEN MODAL
    // ─────────────────────────────────────────────────────────────────────────

    public function status(int $id): void
    {
       
        $trip = Trip::withTrashed()
            ->with([
                'trip_transport_orders.transport_order.cargo',
                'trip_transport_orders.transport_order',
                'trip_destinations',
                'delivery_note',
                'cargo',
            ])
            ->findOrFail($id);

        // ── Store only scalars, never the Eloquent model ──────────────────────
        $this->trip_id                    = $trip->id;
        $this->trip_number                = $trip->trip_number;
        $this->trip_status                = $trip->trip_status;
        $this->selectedStatus             = $trip->trip_status;
        $this->currency_id                = $trip->currency_id;
        $this->freight_calculation        = $trip->freight_calculation     ?? '';
        $this->calculation_measurement    = $trip->calculation_measurement ?? '';
        $this->trip_status_date           = $trip->trip_status_date;
        $this->trip_status_description    = $trip->trip_status_description;
        $this->customer_updates           = (bool) ($trip->customer_updates ?? false);
        $this->ending_mileage             = $trip->ending_mileage;
        $this->starting_mileage           = $trip->starting_mileage;
        $this->ending_hours               = $trip->ending_hours;
        $this->starting_hours             = $trip->starting_hours;
        $this->trip_transporter_agreement = (bool) $trip->transporter_agreement;
        $this->trip_cargo_type            = $trip->cargo?->type ?? '';

        // ── Decide path ───────────────────────────────────────────────────────
        $this->useTtoPath = $trip->trip_transport_orders->count() > 0;
        $this->tto_ids    = $trip->trip_transport_orders->pluck('id')->toArray();

        if ($this->useTtoPath) {
            $this->loadTtoPath($trip);
        } else {
            $this->loadLegacyPath($trip);
        }

        $this->dispatchBrowserEvent('show-statusModal');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PATH LOADERS
    // ─────────────────────────────────────────────────────────────────────────

    private function loadTtoPath(Trip $trip): void
    {
        $destinationTotals   = $this->aggregateDestinationTotals($trip);
        $this->deliveryNotes = [];

        foreach ($trip->trip_transport_orders as $tto) {
            $dn = $this->resolveDeliveryNote($trip, $tto);

            if (! $dn->exists) {
                $to = $tto->transport_order;

                $dn->user_id                    = Auth::id();
                $dn->trip_id                    = $trip->id;
                $dn->trip_transport_order_id    = $tto->id;
                $dn->transport_order_id         = $tto->transport_order_id;
                $dn->units_of_measure_id        = $to->units_of_measure_id ?? $trip->units_of_measure_id;
                $dn->distance                   = $trip->distance;
                $dn->loaded_quantity            = $to->quantity             ?? $trip->quantity;
                $dn->loaded_litreage            = $to->litreage             ?? $trip->litreage;
                $dn->loaded_litreage_at_20      = $to->litreage_at_20       ?? $trip->litreage_at_20;
                $dn->loaded_weight              = $to->weight               ?? $trip->weight;
                $dn->loaded_rate                = $to->rate                 ?? $trip->rate;
                $dn->loaded_freight             = $to->freight              ?? $trip->freight;
                $dn->transporter_loaded_rate    = $trip->transporter_rate;
                $dn->transporter_loaded_freight = $trip->transporter_freight;
                $dn->loaded_date                = $trip->start_date;
                $dn->save();

            } else {
                $needsSave = false;

                if (is_null($dn->trip_transport_order_id)) {
                    $dn->trip_transport_order_id = $tto->id;
                    $needsSave = true;
                }
                if (! $dn->transporter_loaded_rate && ! $dn->transporter_loaded_freight) {
                    $dn->transporter_loaded_rate    = $trip->transporter_rate;
                    $dn->transporter_loaded_freight = $trip->transporter_freight;
                    $needsSave = true;
                }
                if ($needsSave) $dn->save();
            }

            $this->deliveryNotes[$tto->id] = [
                'delivery_note_id'              => $dn->id,
                'units_of_measure_id'           => $dn->units_of_measure_id,
                'distance'                      => $dn->distance,
                'loaded_date'                   => $dn->loaded_date,
                'loaded_quantity'               => $dn->loaded_quantity,
                'loaded_litreage'               => $dn->loaded_litreage,
                'loaded_litreage_at_20'         => $dn->loaded_litreage_at_20,
                'loaded_weight'                 => $dn->loaded_weight,
                'loaded_rate'                   => $dn->loaded_rate,
                'loaded_freight'                => $dn->loaded_freight,
                'transporter_loaded_rate'       => $dn->transporter_loaded_rate,
                'transporter_loaded_freight'    => $dn->transporter_loaded_freight,
                'offloaded_date'                => $dn->offloaded_date,
                'offloaded_quantity'            => $dn->offloaded_quantity       ?? $destinationTotals['quantity'],
                'offloaded_litreage'            => $dn->offloaded_litreage       ?? $destinationTotals['litreage'],
                'offloaded_litreage_at_20'      => $dn->offloaded_litreage_at_20 ?? $destinationTotals['litreage_at_20'],
                'offloaded_weight'              => $dn->offloaded_weight         ?? $destinationTotals['weight'],
                'offloaded_distance'            => $dn->offloaded_distance,
                'offloaded_rate'                => $dn->offloaded_rate           ?? $dn->loaded_rate,
                'offloaded_freight'             => $dn->offloaded_freight        ?? $dn->loaded_freight,
                'transporter_offloaded_rate'    => $dn->transporter_offloaded_rate    ?? $dn->transporter_loaded_rate,
                'transporter_offloaded_freight' => $dn->transporter_offloaded_freight ?? $dn->transporter_loaded_freight,
                'comments'                      => $dn->comments,
            ];
        }
    }

    private function loadLegacyPath(Trip $trip): void
    {
        $this->cargo_type = $trip->cargo?->type ?? '';

        $dn = $trip->delivery_note;

        if (! $dn) {
            $dn                             = new DeliveryNote();
            $dn->user_id                    = Auth::id();
            $dn->trip_id                    = $trip->id;
            $dn->units_of_measure_id        = $trip->units_of_measure_id;
            $dn->distance                   = $trip->distance;
            $dn->loaded_quantity            = $trip->quantity;
            $dn->loaded_litreage            = $trip->litreage;
            $dn->loaded_litreage_at_20      = $trip->litreage_at_20;
            $dn->loaded_weight              = $trip->weight;
            $dn->loaded_rate                = $trip->rate;
            $dn->loaded_freight             = $trip->freight;
            $dn->transporter_loaded_rate    = $trip->transporter_rate;
            $dn->transporter_loaded_freight = $trip->transporter_freight;
            $dn->loaded_date                = $trip->start_date;
            $dn->save();
        } else {
            if (! $dn->transporter_loaded_rate && ! $dn->transporter_loaded_freight) {
                $dn->transporter_loaded_rate    = $trip->transporter_rate;
                $dn->transporter_loaded_freight = $trip->transporter_freight;
                $dn->save();
            }
        }

        $this->legacy_delivery_note_id      = $dn->id;
        $this->units_of_measure_id          = $dn->units_of_measure_id;
        $this->distance                     = $dn->distance;
        $this->loaded_date                  = $dn->loaded_date;
        $this->loaded_quantity              = $dn->loaded_quantity;
        $this->loaded_litreage              = $dn->loaded_litreage;
        $this->loaded_litreage_at_20        = $dn->loaded_litreage_at_20;
        $this->loaded_weight                = $dn->loaded_weight;
        $this->loaded_rate                  = $dn->loaded_rate;
        $this->loaded_freight               = $dn->loaded_freight;
        $this->transporter_loaded_rate      = $dn->transporter_loaded_rate;
        $this->transporter_loaded_freight   = $dn->transporter_loaded_freight;
        $this->offloaded_date               = $dn->offloaded_date;
        $this->offloaded_quantity           = $dn->offloaded_quantity;
        $this->offloaded_litreage           = $dn->offloaded_litreage;
        $this->offloaded_litreage_at_20     = $dn->offloaded_litreage_at_20;
        $this->offloaded_weight             = $dn->offloaded_weight;
        $this->offloaded_distance           = $dn->offloaded_distance;
        $this->comments                     = $dn->comments;

        if (! $dn->status) {
            $this->offloaded_rate                = $dn->loaded_rate;
            $this->offloaded_freight             = $dn->loaded_freight;
            $this->transporter_offloaded_rate    = $dn->transporter_loaded_rate;
            $this->transporter_offloaded_freight = $dn->transporter_loaded_freight;
        } else {
            $this->offloaded_rate                = $dn->offloaded_rate;
            $this->offloaded_freight             = $dn->offloaded_freight;
            $this->transporter_offloaded_rate    = $dn->transporter_offloaded_rate;
            $this->transporter_offloaded_freight = $dn->transporter_offloaded_freight;
        }

        $destinationTotals = $this->aggregateDestinationTotals($trip);

        if (is_null($this->offloaded_weight))         $this->offloaded_weight         = $destinationTotals['weight'];
        if (is_null($this->offloaded_quantity))        $this->offloaded_quantity       = $destinationTotals['quantity'];
        if (is_null($this->offloaded_litreage))        $this->offloaded_litreage       = $destinationTotals['litreage'];
        if (is_null($this->offloaded_litreage_at_20))  $this->offloaded_litreage_at_20 = $destinationTotals['litreage_at_20'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SUBMIT
    // ─────────────────────────────────────────────────────────────────────────

    public function update(): void
    {
        $this->validate($this->validationRules());

        DB::transaction(function () {
            $trip = Trip::withTrashed()
                ->with(['trip_transport_orders', 'trailers', 'breakdown_assignments.trailers', 'delivery_note'])
                ->findOrFail($this->trip_id);

            $trip->trip_status             = $this->selectedStatus;
            $trip->trip_status_date        = $this->trip_status_date;
            $trip->trip_status_description = $this->trip_status_description;
            $trip->ending_mileage          = $this->ending_mileage;
            $trip->starting_mileage        = $this->starting_mileage;
            $trip->ending_hours            = $this->ending_hours;
            $trip->starting_hours          = $this->starting_hours;
            $trip->freight_calculation     = $this->freight_calculation;
            $trip->calculation_measurement = $this->calculation_measurement;

            if ($this->selectedStatus === 'Offloaded') {
                $trip->end_date = $this->useTtoPath
                    ? (collect($this->deliveryNotes)->first()['offloaded_date'] ?? null)
                    : $this->offloaded_date;
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

            if (in_array($this->selectedStatus, ['Loaded', 'Offloaded'])) {
                $this->useTtoPath
                    ? $this->saveTtoDeliveryNotes($trip)
                    : $this->saveLegacyDeliveryNote($trip);
            }

            if (in_array($this->selectedStatus, ['Offloaded', 'Cancelled', 'Scheduled'])) {
                $this->releaseAssets($trip);
            }

            $this->sendCustomerNotification($trip);
        });

        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-statusModal');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Trip Status Updated Successfully!!']);
        $this->emit('tripStatusUpdated', $this->trip_id);
    }

    private function saveTtoDeliveryNotes(Trip $trip): void
    {
        foreach ($trip->trip_transport_orders as $tto) {
            $data = $this->deliveryNotes[$tto->id] ?? null;
            if (! $data) continue;

            $dn = isset($data['delivery_note_id'])
                ? DeliveryNote::find($data['delivery_note_id'])
                : null;

            if (! $dn) {
                $dn = $this->resolveDeliveryNote($trip, $tto);
            }

            $dn->trip_transport_order_id       = $tto->id;
            $dn->transport_order_id            = $tto->transport_order_id;
            $dn->user_id                       = $dn->user_id ?? Auth::id();
            $dn->trip_id                       = $trip->id;
            $dn->units_of_measure_id           = $data['units_of_measure_id'];
            $dn->distance                      = $data['distance'];
            $dn->loaded_date                   = $data['loaded_date'];
            $dn->loaded_quantity               = $data['loaded_quantity'];
            $dn->loaded_litreage               = $data['loaded_litreage'];
            $dn->loaded_litreage_at_20         = $data['loaded_litreage_at_20'];
            $dn->loaded_weight                 = $data['loaded_weight'];
            $dn->loaded_rate                   = $data['loaded_rate'];
            $dn->loaded_freight                = $data['loaded_freight'];
            $dn->transporter_loaded_rate       = $data['transporter_loaded_rate'];
            $dn->transporter_loaded_freight    = $data['transporter_loaded_freight'];
            $dn->offloaded_date                = $data['offloaded_date'];
            $dn->offloaded_quantity            = $data['offloaded_quantity'];
            $dn->offloaded_litreage            = $data['offloaded_litreage'];
            $dn->offloaded_litreage_at_20      = $data['offloaded_litreage_at_20'];
            $dn->offloaded_weight              = $data['offloaded_weight'];
            $dn->offloaded_distance            = $data['offloaded_distance'];
            $dn->offloaded_rate                = $data['offloaded_rate'];
            $dn->offloaded_freight             = $data['offloaded_freight'];
            $dn->transporter_offloaded_rate    = $data['transporter_offloaded_rate'];
            $dn->transporter_offloaded_freight = $data['transporter_offloaded_freight'];
            $dn->comments                      = $data['comments'];
            $dn->freight_calculation           = $this->freight_calculation;
            $dn->calculation_measurement       = $this->calculation_measurement;
            $dn->status                        = 1;
            $dn->save();

            $this->deliveryNotes[$tto->id]['delivery_note_id'] = $dn->id;
        }
    }

    private function saveLegacyDeliveryNote(Trip $trip): void
    {
        $dn = $this->legacy_delivery_note_id
            ? DeliveryNote::find($this->legacy_delivery_note_id)
            : $trip->delivery_note;

        if (! $dn) {
            $dn          = new DeliveryNote();
            $dn->user_id = Auth::id();
            $dn->trip_id = $trip->id;
        }

        $dn->units_of_measure_id           = $this->units_of_measure_id;
        $dn->distance                      = $this->distance;
        $dn->loaded_date                   = $this->loaded_date;
        $dn->loaded_quantity               = $this->loaded_quantity;
        $dn->loaded_litreage               = $this->loaded_litreage;
        $dn->loaded_litreage_at_20         = $this->loaded_litreage_at_20;
        $dn->loaded_weight                 = $this->loaded_weight;
        $dn->loaded_rate                   = $this->loaded_rate;
        $dn->loaded_freight                = $this->loaded_freight;
        $dn->transporter_loaded_rate       = $this->transporter_loaded_rate;
        $dn->transporter_loaded_freight    = $this->transporter_loaded_freight;
        $dn->offloaded_date                = $this->offloaded_date;
        $dn->offloaded_quantity            = $this->offloaded_quantity;
        $dn->offloaded_litreage            = $this->offloaded_litreage;
        $dn->offloaded_litreage_at_20      = $this->offloaded_litreage_at_20;
        $dn->offloaded_weight              = $this->offloaded_weight;
        $dn->offloaded_distance            = $this->offloaded_distance;
        $dn->offloaded_rate                = $this->offloaded_rate;
        $dn->offloaded_freight             = $this->offloaded_freight;
        $dn->transporter_offloaded_rate    = $this->transporter_offloaded_rate;
        $dn->transporter_offloaded_freight = $this->transporter_offloaded_freight;
        $dn->comments                      = $this->comments;
        $dn->freight_calculation           = $this->freight_calculation;
        $dn->calculation_measurement       = $this->calculation_measurement;
        $dn->status                        = 1;
        $dn->save();

        $this->legacy_delivery_note_id = $dn->id;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION
    // ─────────────────────────────────────────────────────────────────────────

    private function validationRules(): array
    {
        $base = [
            'selectedStatus'          => 'required',
            'trip_status_date'        => 'required|date',
            'trip_status_description' => 'nullable|string',
        ];

        if (! in_array($this->selectedStatus, ['Loaded', 'Offloaded'])) {
            return $base;
        }

        $base['freight_calculation'] = 'required|in:flat_rate,rate_weight,rate_weight_distance,rate_distance';

        if ($this->useTtoPath) {
            return array_merge($base, [
                'deliveryNotes.*.loaded_date'       => 'required|date',
                'deliveryNotes.*.loaded_rate'       => 'required|numeric',
                'deliveryNotes.*.loaded_freight'    => 'required|numeric',
                'deliveryNotes.*.offloaded_date'    => $this->selectedStatus === 'Offloaded' ? 'required|date'    : 'nullable|date',
                'deliveryNotes.*.offloaded_rate'    => $this->selectedStatus === 'Offloaded' ? 'required|numeric' : 'nullable|numeric',
                'deliveryNotes.*.offloaded_freight' => $this->selectedStatus === 'Offloaded' ? 'required|numeric' : 'nullable|numeric',
            ]);
        }

        return array_merge($base, [
            'loaded_date'       => 'required|date',
            'loaded_rate'       => 'required|numeric',
            'loaded_freight'    => 'required|numeric',
            'offloaded_date'    => $this->selectedStatus === 'Offloaded' ? 'required|date'    : 'nullable|date',
            'offloaded_rate'    => $this->selectedStatus === 'Offloaded' ? 'required|numeric' : 'nullable|numeric',
            'offloaded_freight' => $this->selectedStatus === 'Offloaded' ? 'required|numeric' : 'nullable|numeric',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FREIGHT RECALCULATION
    // ─────────────────────────────────────────────────────────────────────────

    public function updated(string $propertyName): void
    {
        if (in_array($propertyName, ['freight_calculation', 'calculation_measurement'])) {
            if ($this->useTtoPath) {
                foreach ($this->tto_ids as $ttoId) {
                    $this->recalculateDeliveryNoteFreight($ttoId);
                }
            } else {
                $this->recalculateLegacyFreight();
            }
            return;
        }

        if ($this->useTtoPath && str_starts_with($propertyName, 'deliveryNotes.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) < 3) return;
            $ttoId = (int) $parts[1];
            $field = $parts[2];
            if (in_array($field, $this->freightTriggerFields())) {
                $this->recalculateDeliveryNoteFreight($ttoId);
            }
            return;
        }

        if (! $this->useTtoPath && in_array($propertyName, $this->freightTriggerFields())) {
            $this->recalculateLegacyFreight();
        }
    }

    private function freightTriggerFields(): array
    {
        return [
            'loaded_rate', 'loaded_weight', 'loaded_litreage',
            'loaded_litreage_at_20', 'distance',
            'offloaded_rate', 'offloaded_weight', 'offloaded_litreage',
            'offloaded_litreage_at_20', 'offloaded_distance',
            'transporter_loaded_rate', 'transporter_offloaded_rate',
        ];
    }

    private function recalculateLegacyFreight(): void
    {
        $this->loaded_freight = $this->calculateFreight(
            $this->loaded_rate, $this->cargo_type, $this->freight_calculation,
            $this->calculation_measurement, $this->loaded_weight,
            $this->loaded_litreage_at_20, $this->loaded_litreage, $this->distance
        );
        $this->offloaded_freight = $this->calculateFreight(
            $this->offloaded_rate, $this->cargo_type, $this->freight_calculation,
            $this->calculation_measurement, $this->offloaded_weight,
            $this->offloaded_litreage_at_20, $this->offloaded_litreage, $this->offloaded_distance
        );
        $this->transporter_loaded_freight = $this->calculateFreight(
            $this->transporter_loaded_rate, $this->cargo_type, $this->freight_calculation,
            $this->calculation_measurement, $this->loaded_weight,
            $this->loaded_litreage_at_20, $this->loaded_litreage, $this->distance
        );
        $this->transporter_offloaded_freight = $this->calculateFreight(
            $this->transporter_offloaded_rate, $this->cargo_type, $this->freight_calculation,
            $this->calculation_measurement, $this->offloaded_weight,
            $this->offloaded_litreage_at_20, $this->offloaded_litreage, $this->offloaded_distance
        );
    }

    private function recalculateDeliveryNoteFreight(int $ttoId): void
    {
        if (! isset($this->deliveryNotes[$ttoId])) return;

        $tto       = TripTransportOrder::with('transport_order.cargo')->find($ttoId);
        $cargoType = $tto?->transport_order?->cargo?->type;
        $dn        = &$this->deliveryNotes[$ttoId];

        $dn['loaded_freight'] = $this->calculateFreight(
            $dn['loaded_rate'], $cargoType, $this->freight_calculation,
            $this->calculation_measurement, $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'], $dn['loaded_litreage'], $dn['distance']
        );
        $dn['offloaded_freight'] = $this->calculateFreight(
            $dn['offloaded_rate'], $cargoType, $this->freight_calculation,
            $this->calculation_measurement, $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'], $dn['offloaded_litreage'], $dn['offloaded_distance']
        );
        $dn['transporter_loaded_freight'] = $this->calculateFreight(
            $dn['transporter_loaded_rate'], $cargoType, $this->freight_calculation,
            $this->calculation_measurement, $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'], $dn['loaded_litreage'], $dn['distance']
        );
        $dn['transporter_offloaded_freight'] = $this->calculateFreight(
            $dn['transporter_offloaded_rate'], $cargoType, $this->freight_calculation,
            $this->calculation_measurement, $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'], $dn['offloaded_litreage'], $dn['offloaded_distance']
        );
    }

    private function calculateFreight(
        $rate, $cargoType, $freightCalculation,
        $measurement, $weight, $litreageAt20, $litreage, $distance
    ): ?float {
        if (! is_numeric($rate)) return null;

        switch ($freightCalculation) {
            case 'rate_weight':
                if ($cargoType === 'Solid' && is_numeric($weight))
                    return $rate * $weight;
                if ($cargoType === 'Liquid') {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20))
                        return $rate * $litreageAt20;
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage))
                        return $rate * $litreage;
                }
                break;
            case 'rate_distance':
                if (is_numeric($distance)) return $rate * $distance;
                break;
            case 'rate_weight_distance':
                if ($cargoType === 'Solid' && is_numeric($weight) && is_numeric($distance))
                    return $rate * $weight * $distance;
                if ($cargoType === 'Liquid' && is_numeric($distance)) {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20))
                        return $rate * $litreageAt20 * $distance;
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage))
                        return $rate * $litreage * $distance;
                }
                break;
            case 'flat_rate':
                return (float) $rate;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveDeliveryNote(Trip $trip, $tto): DeliveryNote
    {
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
        }

        $dn->trip_transport_order_id = $tto->id;
        $dn->transport_order_id      = $tto->transport_order_id;

        return $dn;
    }

    private function aggregateDestinationTotals(Trip $trip): array
    {
        $d = $trip->trip_destinations ?? collect();

        return [
            'weight'         => $d->whereNotNull('weight')->where('weight', '!=', '')->sum('weight')                        ?: null,
            'quantity'       => $d->whereNotNull('quantity')->where('quantity', '!=', '')->sum('quantity')                  ?: null,
            'litreage'       => $d->whereNotNull('litreage')->where('litreage', '!=', '')->sum('litreage')                  ?: null,
            'litreage_at_20' => $d->whereNotNull('litreage_at_20')->where('litreage_at_20', '!=', '')->sum('litreage_at_20') ?: null,
        ];
    }

    private function updateAssetMileage(Trip $trip): void
    {
        $asset = $trip->horse_id
            ? Horse::find($trip->horse_id)
            : ($trip->vehicle_id ? Vehicle::find($trip->vehicle_id) : null);

        if ($asset && $this->ending_mileage > $asset->mileage) {
            $asset->mileage = $this->ending_mileage;
            $asset->save();
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
                if ($asset->mileage > 0 && $trip->distance > 0)
                    $asset->mileage += $trip->distance;
                if ($asset->fuel_balance > 0 && $trip->trip_fuel > 0)
                    $asset->fuel_balance -= $trip->trip_fuel;
            }
            $asset->save();
        }

        if ($trip->driver_id)
            Driver::withTrashed()->find($trip->driver_id)?->update(['status' => 1]);

        foreach ($trip->trailers as $trailer)
            Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);

        foreach ($trip->breakdown_assignments as $ba) {
            Horse::withTrashed()->find($ba->horse_id)?->update(['status' => 1]);
            Driver::withTrashed()->find($ba->driver_id)?->update(['status' => 1]);
            foreach ($ba->trailers as $trailer)
                Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);
        }
    }


    private function sendCustomerNotification(Trip $trip): void
    {
        if (! $this->customer_updates) return;
        $company = Auth::user()->company ?? Auth::user()->employee?->company;
        $email   = Trip::find($this->trip_id)?->customer?->email;
        if ($email && $company)
            Mail::to($email)->send(new TripUpdatesMail(Trip::find($this->trip_id), $company));
    }

    public function resetInputFields(): void
    {
        $this->trip_id                       = null;
        $this->trip_number                   = null;
        $this->trip_status                   = null;
        $this->selectedStatus                = null;
        $this->trip_status_date              = null;
        $this->trip_status_description       = null;
        $this->customer_updates              = false;
        $this->starting_mileage              = null;
        $this->ending_mileage                = null;
        $this->starting_hours                = null;
        $this->ending_hours                  = null;
        $this->currency_id                   = null;
        $this->freight_calculation           = '';
        $this->calculation_measurement       = '';
        $this->trip_transporter_agreement    = false;
        $this->trip_cargo_type               = null;
        $this->useTtoPath                    = false;
        $this->tto_ids                       = [];
        $this->deliveryNotes                 = [];
        $this->cargo_type                    = null;
        $this->units_of_measure_id           = null;
        $this->loaded_date                   = null;
        $this->distance                      = null;
        $this->loaded_quantity               = null;
        $this->loaded_litreage               = null;
        $this->loaded_litreage_at_20         = null;
        $this->loaded_weight                 = null;
        $this->loaded_rate                   = null;
        $this->loaded_freight                = null;
        $this->transporter_loaded_rate       = null;
        $this->transporter_loaded_freight    = null;
        $this->offloaded_date                = null;
        $this->offloaded_quantity            = null;
        $this->offloaded_litreage            = null;
        $this->offloaded_litreage_at_20      = null;
        $this->offloaded_weight              = null;
        $this->offloaded_distance            = null;
        $this->offloaded_rate                = null;
        $this->offloaded_freight             = null;
        $this->transporter_offloaded_rate    = null;
        $this->transporter_offloaded_freight = null;
        $this->comments                      = null;
        $this->legacy_delivery_note_id       = null;
    }
}