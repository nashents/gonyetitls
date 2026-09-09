<?php

namespace App\Http\Livewire\Trips;

use App\Models\Employee;
use App\Models\Trip;
use App\Models\TripNote;
use App\Models\TruckStop;
use App\Services\Fleet\FleetPositionResolver;
use App\Services\Fleet\ReverseGeocoder;
use App\Services\Integrations\IntegrationGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * One shared modal, mounted once per page (Asset Positions, Trips index),
 * listening for an `openTripNotes` event carrying a trip id — rather than
 * one modal instance per table row.
 *
 * Free-text ops comments/updates on a trip. Deliberately separate from
 * App\Models\TripStatus (which stays reserved for formal status changes) —
 * this is the running "SHUNT / to arrive by... / notify on arrival"
 * conversation, with quick "smart comment" templates for common task types.
 */
class TripNotesModal extends Component
{
    protected $listeners = ['openTripNotes' => 'open'];

    public $tripId;
    public $trip;

    /** Live position snapshot resolved once when the modal opens — reused for both the context display and the note being posted, rather than hitting the tracking/geocoding APIs again on every keystroke. */
    public $positionSnapshot = null;

    public $body = '';
    public $taskType = null;
    public $area;
    public $byTime;
    public $notify;

    public function open($tripId)
    {
        $this->tripId = $tripId;
        $this->trip = Trip::with([
            'horse:id,registration_number,fleet_number',
            'vehicle:id,registration_number,fleet_number',
            'customer:id,name',
            'cargo:id,name',
            'loading_point:id,name',
            'offloading_point:id,name',
            'transport_orders:id,transport_order_number',
        ])->find($tripId);

        $this->positionSnapshot = $this->resolvePositionSnapshot();

        $this->resetForm();
        $this->dispatchBrowserEvent('show-tripNotesModal');
    }

    protected function resetForm(): void
    {
        $this->body = '';
        $this->taskType = null;
        $this->area = null;
        $this->byTime = null;
        $this->notify = null;
    }

    /** Clicking a quick-comment button seeds the textarea from its template; clicking the active one again reverts to a plain comment. */
    public function selectTemplate(?string $type): void
    {
        if ($this->taskType === $type) {
            $this->taskType = null;
            $this->body = '';
            return;
        }

        $this->taskType = $type;
        $this->applyTemplate();
    }

    /** Keeps the textarea preview in sync as Area/By/Notify are filled in. */
    public function updated(string $name): void
    {
        if (in_array($name, ['area', 'byTime', 'notify']) && $this->taskType) {
            $this->applyTemplate();
        }
    }

    protected function applyTemplate(): void
    {
        $template = TripNote::TASK_TEMPLATES[$this->taskType] ?? null;
        if (! $template) {
            return;
        }

        $this->body = strtr($template, [
            '[area]'   => $this->area ?: '[area]',
            '[time]'   => $this->byTime ?: '[time]',
            '[notify]' => $this->notify ?: '[notify]',
        ]);
    }

    protected function currentCompanyId(): ?int
    {
        $user = Auth::user();

        return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
    }

    /** Best-effort live position snapshot for the modal's context bar/the note being posted — silently skipped if tracking isn't configured or nothing resolves. */
    protected function resolvePositionSnapshot(): ?array
    {
        if (! $this->trip || ! IntegrationGate::enabledForUserType('tracking')) {
            return null;
        }

        $assetKey = $this->trip->horse_id
            ? 'horse:' . $this->trip->horse_id
            : ($this->trip->vehicle_id ? 'vehicle:' . $this->trip->vehicle_id : null);

        if (! $assetKey) {
            return null;
        }

        try {
            $position = app(FleetPositionResolver::class)->resolve($this->currentCompanyId())->get($assetKey);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $position) {
            return null;
        }

        $address = app(ReverseGeocoder::class)->resolve($position['lat'], $position['lng']);

        return [
            'lat'         => $position['lat'],
            'lng'         => $position['lng'],
            'address'     => $address,
            'observed_at' => $position['observed_at'],
        ];
    }

    public function store(): void
    {
        $this->validate(['body' => 'required|string']);

        TripNote::create([
            'trip_id'               => $this->tripId,
            'user_id'               => Auth::id(),
            'task_type'             => $this->taskType,
            'body'                  => $this->body,
            'area'                  => $this->area,
            'by_time'               => $this->byTime,
            'notify'                => $this->notify,
            'latitude'              => $this->positionSnapshot['lat'] ?? null,
            'longitude'             => $this->positionSnapshot['lng'] ?? null,
            'location_description'  => $this->positionSnapshot['address'] ?? null,
        ]);

        $this->resetForm();
        $this->emit('tripNoteAdded');
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Note added.']);
    }

    public function getNotesProperty()
    {
        if (! $this->tripId) {
            return collect();
        }

        return TripNote::where('trip_id', $this->tripId)->with('user')->latest()->get();
    }

    public function render()
    {
        return view('livewire.trips.trip-notes-modal', [
            'areas'     => TruckStop::orderBy('name')->get(['id', 'name']),
            'employees' => Employee::where('archive', 0)->orderBy('name')->orderBy('surname')->get(['id', 'name', 'surname']),
            'notes'     => $this->notes,
        ]);
    }
}
