<?php

namespace App\Http\Livewire\Freight\Shipments;

use App\Models\Shipment;
use App\Models\ShippingLine;
use App\Models\TransportDocument;
use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Http\Livewire\Freight\Concerns\FreightEmbeddedPagination;

class TransportDocuments extends Component
{
    use FreightEmbeddedPagination;

    public $shipment;
    public $vendors;
    public $shippingLines;

    /** Set while editing an existing document (same modal as Add). */
    public $document_id;

    public $document_type;
    public $document_number;
    public $issue_date;
    public $carrier_vendor_id;
    public $carrier_shipping_line_id;
    public $carrier_name;
    public $place_of_issue;
    public $freight_payable_at;
    public $number_of_originals;
    public $status = 'draft';

    public function mount($shipmentId)
    {
        $this->shipment = Shipment::findOrFail($shipmentId);
        $this->vendors = Vendor::orderBy('name', 'asc')->get();
        $this->shippingLines = ShippingLine::where('is_active', true)->orderBy('name', 'asc')->get();
        $this->refreshShipment();
    }

    private function refreshShipment()
    {
        $this->shipment = Shipment::with(['transport_documents.carrier_vendor', 'transport_documents.shipping_line'])->findOrFail($this->shipment->id);
    }

    protected function rules()
    {
        return [
            'document_type' => ['required', 'string', Rule::in(array_keys(TransportDocument::DOCUMENT_TYPES))],
            'document_number' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'number_of_originals' => 'nullable|integer|min:0',
            'status' => ['required', Rule::in(array_keys(TransportDocument::STATUSES))],
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatchBrowserEvent('show-addTransportDocumentModal-' . $this->shipment->id);
    }

    public function edit($id)
    {
        $doc = $this->findDocument($id);

        $this->resetForm();
        $this->document_id = $doc->id;
        $this->document_type = $doc->document_type;
        $this->document_number = $doc->document_number;
        $this->issue_date = $doc->issue_date?->format('Y-m-d');
        $this->carrier_shipping_line_id = $doc->shipping_line_id;
        $this->carrier_vendor_id = $doc->shipping_line_id ? null : $doc->carrier_vendor_id;
        $this->carrier_name = $doc->carrier_name;
        $this->place_of_issue = $doc->place_of_issue;
        $this->freight_payable_at = $doc->freight_payable_at;
        $this->number_of_originals = $doc->number_of_originals;
        $this->status = $doc->status ?: 'draft';

        $this->dispatchBrowserEvent('show-addTransportDocumentModal-' . $this->shipment->id);
    }

    public function store()
    {
        $this->validate();

        // Sea documents: carrier is a shipping line (its vendor kept for billing); others: a vendor.
        $line = TransportDocument::carrierIsShippingLine($this->document_type) && $this->carrier_shipping_line_id
            ? ShippingLine::find($this->carrier_shipping_line_id)
            : null;

        $data = [
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'issue_date' => $this->issue_date ?: null,
            'shipping_line_id' => $line?->id,
            'carrier_vendor_id' => $line ? $line->vendor_id : ($this->carrier_vendor_id ?: null),
            'carrier_name' => $this->carrier_name,
            'place_of_issue' => $this->place_of_issue,
            'freight_payable_at' => $this->freight_payable_at,
            'number_of_originals' => $this->number_of_originals !== '' ? $this->number_of_originals : null,
            'status' => $this->status ?: 'draft',
        ];

        if ($this->document_id) {
            $this->findDocument($this->document_id)->update($data);
            $message = 'Transport document updated successfully!';
        } else {
            TransportDocument::create($data + ['shipment_id' => $this->shipment->id]);
            $message = 'Transport document added successfully!';
        }

        $this->resetForm();
        $this->refreshShipment();
        $this->dispatchBrowserEvent('hide-addTransportDocumentModal-' . $this->shipment->id);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $message]);
    }

    /** Quick status change from the row's actions (e.g. Draft -> Issued -> Telex Release). */
    public function setStatus($id, $status)
    {
        if (!array_key_exists($status, TransportDocument::STATUSES)) {
            return;
        }

        $doc = $this->findDocument($id);
        $doc->update(['status' => $status]);

        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $doc->document_number . ' marked as ' . TransportDocument::STATUSES[$status] . '.']);
    }

    public function delete($id)
    {
        $doc = $this->findDocument($id);

        if ($reason = $doc->deleteBlockedReason()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Cannot delete ' . $doc->document_number . ': ' . $reason]);
            return;
        }

        $doc->delete();

        if ((int) $this->document_id === (int) $id) {
            $this->resetForm();
        }

        $this->refreshShipment();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Transport document ' . $doc->document_number . ' deleted.']);
    }

    public function resetForm()
    {
        $this->reset(['document_id', 'document_type', 'document_number', 'issue_date', 'carrier_vendor_id', 'carrier_shipping_line_id', 'carrier_name', 'place_of_issue', 'freight_payable_at', 'number_of_originals']);
        $this->status = 'draft';
        $this->resetValidation();
    }

    /** Only documents of this shipment can be touched from this tab. */
    private function findDocument($id): TransportDocument
    {
        return TransportDocument::where('shipment_id', $this->shipment->id)->findOrFail($id);
    }

    public function refresh($category)
    {
        if ($category === 'vendors') {
            $this->vendors = Vendor::orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Vendors Refreshed Successfully!!.']);
        } elseif ($category === 'shipping_lines') {
            $this->shippingLines = ShippingLine::where('is_active', true)->orderBy('name', 'asc')->get();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Shipping Lines Refreshed Successfully!!.']);
        }
    }

    public function render()
    {
        return view('livewire.freight.shipments.transport-documents', [
            'documentRows' => $this->paginateRows($this->shipment->transport_documents->sortBy('id')),
            'documentTypes' => TransportDocument::DOCUMENT_TYPES,
            'statuses' => TransportDocument::STATUSES,
        ]);
    }
}
