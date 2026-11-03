<?php

namespace App\Http\Livewire\Trips;

use App\Models\Destination;
use App\Models\Trip;
use App\Models\TripDocumentSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

/**
 * System-generated trip Delivery Note (same layout as the Manifest). Each party
 * can e-sign on screen, or the printed copy is signed by hand on the blank lines.
 * Guests reach it through a signed link and carry an HMAC token so their
 * Livewire calls can't be pointed at another trip.
 */
class DeliveryNoteDocument extends Component
{
    public $trip;
    public $company;
    public $from;
    public $to;
    public $token;
    public $shareLink;

    public $signRole;
    public $signer_name;
    public $signer_designation;

    public static function tokenFor(int $tripId): string
    {
        return hash_hmac('sha256', 'delivery-note|'.$tripId, config('app.key'));
    }

    public function mount(Trip $trip, $token = null)
    {
        $this->trip = $trip;
        $this->token = $token;
        $this->company = $trip->company;
        $this->from = Destination::find($trip->from);
        $this->to = Destination::find($trip->to);
    }

    // Only the signer fields are editable from the browser.
    public function updating($name)
    {
        abort_unless(in_array($name, ['signer_name', 'signer_designation'], true), 403);
    }

    protected function canSign(): bool
    {
        return Auth::check()
            || ($this->token && hash_equals(static::tokenFor($this->trip->id), (string) $this->token));
    }

    protected function canManage(): bool
    {
        return Auth::check() && Auth::user()->category !== 'driver';
    }

    public function openSign($role)
    {
        abort_unless($this->canSign() && array_key_exists($role, TripDocumentSignature::DELIVERY_NOTE_ROLES), 403);

        $this->resetErrorBag();
        $this->signRole = $role;
        $this->signer_designation = null;
        $this->signer_name = $role === 'driver'
            ? trim(($this->trip->driver?->employee?->name ?? '').' '.($this->trip->driver?->employee?->surname ?? ''))
            : null;

        // the pad is rendered by this update; the event tells the JS to wire up its canvas
        $this->dispatchBrowserEvent('open-signature-pad');
    }

    public function saveSignature($signature)
    {
        abort_unless($this->canSign() && array_key_exists($this->signRole, TripDocumentSignature::DELIVERY_NOTE_ROLES), 403);

        $this->validate([
            'signer_name' => 'required|string|max:255',
            'signer_designation' => 'nullable|string|max:255',
        ], [
            'signer_name.required' => 'Enter the name of the person signing.',
        ]);

        // A PNG drawn on the pad; anything else (or an oversized payload) is refused.
        if (! is_string($signature) || ! str_starts_with($signature, 'data:image/png;base64,') || strlen($signature) > 700000) {
            $this->addError('signature', 'Please draw a signature before saving.');
            return;
        }

        $existing = $this->signatureQuery()->where('role', $this->signRole)->first();
        // Once signed, only office staff may replace a signature - not the link holder.
        if ($existing && ! $this->canManage()) {
            $this->addError('signature', 'This section has already been signed.');
            return;
        }

        TripDocumentSignature::updateOrCreate(
            ['trip_id' => $this->trip->id, 'document' => TripDocumentSignature::DELIVERY_NOTE, 'role' => $this->signRole],
            [
                'signer_name' => $this->signer_name,
                'signer_designation' => $this->signer_designation,
                'signature' => $signature,
                'signed_at' => now(),
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
            ]
        );

        $this->signRole = null;
    }

    public function cancelSign()
    {
        $this->signRole = null;
        $this->resetErrorBag();
    }

    public function clearSignature($role)
    {
        abort_unless($this->canManage(), 403);

        $this->signatureQuery()->where('role', $role)->delete();
    }

    public function generateShareLink()
    {
        abort_unless($this->canManage(), 403);

        $this->shareLink = URL::temporarySignedRoute(
            'trips.delivery_note.sign',
            now()->addDays(14),
            ['trip' => $this->trip->id]
        );
    }

    protected function signatureQuery()
    {
        return TripDocumentSignature::where('trip_id', $this->trip->id)
            ->where('document', TripDocumentSignature::DELIVERY_NOTE);
    }

    public function render()
    {
        $trip = $this->trip->fresh([
            'trip_transport_orders.transport_order.cargo',
            'trip_transport_orders.transport_order.consignee',
            'trip_transport_orders.transport_order.consignor',
            'trip_transport_orders.transport_order.units_of_measure',
            'trip_transport_orders.delivery_note.units_of_measure',
            'delivery_note.units_of_measure',
        ]);

        return view('livewire.trips.delivery-note-document', [
            'trip' => $trip,
            'signatures' => $this->signatureQuery()->get()->keyBy('role'),
            'roles' => TripDocumentSignature::DELIVERY_NOTE_ROLES,
            'canSign' => $this->canSign(),
            'canManage' => $this->canManage(),
        ]);
    }
}
